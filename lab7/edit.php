<?php

declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

$flashSuccess = '';
$flashError = '';

if (isset($_SESSION['flash_success'])) {
    $flashSuccess = (string) $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

if (isset($_SESSION['flash_error'])) {
    $flashError = (string) $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

$errors = [];

$id = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
$recipe = $id > 0 ? $repository->findById($id) : null;

if ($recipe === null) {
    http_response_code(404);
    echo 'Рецепт не найден.';
    exit;
}

$formData = [
    'id' => $recipe->getId(),
    'title' => $recipe->getTitle(),
    'author' => $recipe->getAuthor(),
    'category' => (string) $recipe->getCategoryId(),
    'difficulty' => $recipe->getDifficulty(),
    'prep_time' => (string) $recipe->getPrepTime(),
    'ingredients' => $recipe->getIngredients(),
    'instructions' => $recipe->getInstructions(),
    'created_at' => $recipe->getCreatedAt(),
    'tags' => $recipe->getTags(),
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $postedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

    if (!hash_equals($csrfToken, $postedToken)) {
        $errors['form'] = 'Сессия истекла. Обновите страницу и попробуйте снова.';
    } elseif ($action === 'update') {
        $result = $service->update($id, $_POST);

        if ($result['success']) {
            $_SESSION['flash_success'] = 'Рецепт обновлён.';

            header('Location: index.php?engine=' . urlencode($engine));
            exit;
        }

        $errors = $result['errors'];
        $formData = $result['data'];

        if (($formData['category'] ?? '') === '') {
            $formData['category'] = (string) $recipe->getCategoryId();
        }
    } elseif ($action === 'delete') {
        $service->delete($id);
        $_SESSION['flash_success'] = 'Рецепт удалён.';

        header('Location: index.php?engine=' . urlencode($engine));
        exit;
    }
}

$title = 'Редактирование рецепта';

echo $view->render('form', [
    'title' => $title,
    'engine' => $engine,
    'page' => 'form',
    'isEdit' => true,
    'formData' => $formData,
    'errors' => $errors,
    'categories' => $categories,
    'difficulties' => RecipeOptions::DIFFICULTIES,
    'tags' => RecipeOptions::TAGS,
    'csrfToken' => $csrfToken,
    'flashSuccess' => $flashSuccess,
    'flashError' => $flashError,
    'formAction' => 'edit.php?id=' . $id . '&engine=' . urlencode($engine),
]);
