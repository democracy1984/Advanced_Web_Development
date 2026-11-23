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
$page = isset($_GET['page']) && is_string($_GET['page']) && $_GET['page'] === 'form' ? 'form' : 'list';
$formData = $service->emptyData();
$defaultCategoryId = (string) array_key_first($categories);
$formData['category'] = $defaultCategoryId;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $postedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

    if (!hash_equals($csrfToken, $postedToken)) {
        $errors['form'] = 'Сессия истекла. Обновите страницу и попробуйте снова.';
    } elseif ($action === 'create') {
        $result = $service->create($_POST);

        if ($result['success']) {
            $recipe = $result['recipe'];
            $_SESSION['flash_success'] = $recipe !== null
                ? 'Рецепт «' . $recipe->getTitle() . '» добавлен.'
                : 'Рецепт добавлен.';

            header('Location: index.php?engine=' . urlencode($engine));
            exit;
        }

        $errors = $result['errors'];
        $formData = $result['data'];
        $page = 'form';

        if (($formData['category'] ?? '') === '') {
            $formData['category'] = $defaultCategoryId;
        }
    } elseif ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!is_int($id) || $id <= 0) {
            $errors['form'] = 'Некорректный идентификатор рецепта.';
        } else {
            $service->delete($id);
            $_SESSION['flash_success'] = 'Рецепт удалён.';

            header('Location: index.php?engine=' . urlencode($engine));
            exit;
        }
    }
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$recipes = $repository->all();

if ($search !== '') {
    $recipes = array_values(array_filter(
        $recipes,
        static function (Recipe $recipe) use ($search): bool {
            return mb_stripos($recipe->getTitle(), $search) !== false
                || mb_stripos($recipe->getAuthor(), $search) !== false
                || mb_stripos($recipe->getCategory(), $search) !== false;
        }
    ));
}

$totalRecipes = count($repository->all());
$averagePrepTime = $totalRecipes > 0
    ? (int) round(array_sum(array_map(
        static fn (Recipe $recipe): int => $recipe->getPrepTime(),
        $repository->all()
    )) / $totalRecipes)
    : 0;

$stats = [
    'total' => $totalRecipes,
    'average' => $averagePrepTime,
];

if ($page === 'form') {
    echo $view->render('form', [
        'title' => 'Новый рецепт',
        'engine' => $engine,
        'page' => 'form',
        'isEdit' => false,
        'formData' => $formData,
        'errors' => $errors,
        'categories' => $categories,
        'difficulties' => RecipeOptions::DIFFICULTIES,
        'tags' => RecipeOptions::TAGS,
        'csrfToken' => $csrfToken,
        'flashSuccess' => $flashSuccess,
        'flashError' => $flashError,
        'formAction' => 'index.php?engine=' . urlencode($engine),
    ]);
    exit;
}

echo $view->render('list', [
    'title' => 'Список рецептов',
    'engine' => $engine,
    'page' => 'list',
    'recipes' => $recipes,
    'categories' => $categories,
    'difficulties' => RecipeOptions::DIFFICULTIES,
    'tags' => RecipeOptions::TAGS,
    'stats' => $stats,
    'csrfToken' => $csrfToken,
    'flashSuccess' => $flashSuccess,
    'flashError' => $flashError,
    'errors' => $errors,
]);
