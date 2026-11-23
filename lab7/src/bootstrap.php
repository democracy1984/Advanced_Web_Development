<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Recipe.php';
require_once __DIR__ . '/RecipeOptions.php';
require_once __DIR__ . '/RecipeRepository.php';
require_once __DIR__ . '/RecipeService.php';
require_once __DIR__ . '/RecipeValidator.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ValidatorInterface.php';

foreach (glob(__DIR__ . '/Validators/*.php') as $validatorFile) {
    require_once $validatorFile;
}

$config = require __DIR__ . '/../config.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = (string) $_SESSION['csrf_token'];

$database = new Database($config['db']);
$pdo = $database->getConnection();

// Создание схемы, если базы ещё нет.
$pdo->exec(file_get_contents(__DIR__ . '/../schema.sql'));

$categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();

if ($categoryCount === 0) {
    $categoriesData = [
        ['slug' => 'breakfast', 'name' => 'Завтрак'],
        ['slug' => 'lunch', 'name' => 'Обед'],
        ['slug' => 'dinner', 'name' => 'Ужин'],
        ['slug' => 'dessert', 'name' => 'Десерт'],
        ['slug' => 'drink', 'name' => 'Напиток'],
    ];

    $categoryStatement = $pdo->prepare(
        'INSERT INTO categories (slug, name) VALUES (:slug, :name)'
    );

    foreach ($categoriesData as $category) {
        $categoryStatement->execute($category);
    }

    $recipeStatement = $pdo->prepare(
        'INSERT INTO recipes
            (title, author, category_id, difficulty, prep_time, ingredients, instructions, tags, created_at, updated_at)
         VALUES
            (:title, :author, :category_id, :difficulty, :prep_time, :ingredients, :instructions, :tags, :created_at, :updated_at)'
    );

    $recipes = [
        [
            'title' => 'Овсянка с ягодами',
            'author' => 'Анна Петрова',
            'category' => 'breakfast',
            'difficulty' => 'easy',
            'prep_time' => 15,
            'ingredients' => "Овсяные хлопья — 60 г\nМолоко — 200 мл\nЯгоды — 50 г\nМёд — 1 ч. л.",
            'instructions' => "1. Вскипятите молоко.\n2. Добавьте овсянку и варите 5 минут.\n3. Добавьте ягоды и мёд.",
            'tags' => 'vegetarian',
            'created_at' => '2026-10-20',
        ],
        [
            'title' => 'Паста с томатами и базиликом',
            'author' => 'Иван Смирнов',
            'category' => 'dinner',
            'difficulty' => 'medium',
            'prep_time' => 25,
            'ingredients' => "Спагетти — 200 г\nТоматы — 3 шт.\nЧеснок — 2 зубчика\nБазилик — пучок\nОливковое масло — 2 ст. л.",
            'instructions' => "1. Отварите пасту до готовности.\n2. Обжарьте чеснок на оливковом масле.\n3. Добавьте томаты и тушите 10 минут.\n4. Смешайте с пастой и базиликом.",
            'tags' => 'vegetarian',
            'created_at' => '2026-10-25',
        ],
        [
            'title' => 'Шоколадный брауни',
            'author' => 'Мария Козлова',
            'category' => 'dessert',
            'difficulty' => 'easy',
            'prep_time' => 40,
            'ingredients' => "Шоколад — 100 г\nМасло — 100 г\nЯйца — 2 шт.\nСахар — 150 г\nМука — 70 г",
            'instructions' => "1. Растопите шоколад с маслом.\n2. Взбейте яйца с сахаром.\n3. Смешайте все ингредиенты.\n4. Выпекайте 25 минут при 180 °C.",
            'tags' => 'vegetarian',
            'created_at' => '2026-10-30',
        ],
        [
            'title' => 'Смузи из шпината и яблока',
            'author' => 'Олег Иванов',
            'category' => 'drink',
            'difficulty' => 'easy',
            'prep_time' => 10,
            'ingredients' => "Шпинат — 50 г\nЯблоко — 1 шт.\nБанан — 1 шт.\nВода — 150 мл",
            'instructions' => "1. Вымойте шпинат и фрукты.\n2. Нарежьте яблоко и банан.\n3. Измельчите всё в блендере с водой.",
            'tags' => 'vegan,gluten_free,lactose_free',
            'created_at' => '2026-11-01',
        ],
    ];

    $categoryIds = [];

    foreach ($pdo->query('SELECT id, slug FROM categories') as $row) {
        $categoryIds[$row['slug']] = (int) $row['id'];
    }

    foreach ($recipes as $recipe) {
        $recipeStatement->execute([
            ':title' => $recipe['title'],
            ':author' => $recipe['author'],
            ':category_id' => $categoryIds[$recipe['category']],
            ':difficulty' => $recipe['difficulty'],
            ':prep_time' => $recipe['prep_time'],
            ':ingredients' => $recipe['ingredients'],
            ':instructions' => $recipe['instructions'],
            ':tags' => $recipe['tags'],
            ':created_at' => $recipe['created_at'],
            ':updated_at' => $recipe['created_at'],
        ]);
    }
}

$repository = new RecipeRepository($pdo);
$categories = $repository->allCategories();
$validator = new RecipeValidator(array_map('strval', array_keys($categories)));
$service = new RecipeService($repository, $validator, (int) array_key_first($categories));

$engine = $config['template_engine'];

if (isset($_GET['engine']) && is_string($_GET['engine']) && in_array($_GET['engine'], ['native', 'twig'], true)) {
    $engine = $_GET['engine'];
}

$twig = null;

if ($engine === 'twig') {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates/twig');
    $twig = new \Twig\Environment($loader, [
        'cache' => false,
        'autoescape' => 'html',
    ]);
    $twig->addFilter(new \Twig\TwigFilter('format_duration', function (int $minutes): string {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours === 0) {
            return $mins . ' мин';
        }

        if ($mins === 0) {
            return $hours . ' ч';
        }

        return $hours . ' ч ' . $mins . ' мин';
    }));
}

$view = new View($engine, __DIR__ . '/../templates', $twig);
