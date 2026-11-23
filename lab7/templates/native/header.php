<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Рецепты') ?></title>
    <link rel="stylesheet" href="/templates/style.css">
</head>
<body>

<header>
    <div class="header-inner">
        <p class="eyebrow">Advanced Web Development · Лабораторная работа №7</p>
        <h1><?= e($title ?? 'Управление рецептами') ?></h1>
        <p class="subtitle">PDO, SQLite, шаблоны и CRUD</p>
    </div>
    <nav>
        <a href="index.php?engine=<?= e($engine ?? 'native') ?>">Список рецептов</a>
        <a href="index.php?page=form&engine=<?= e($engine ?? 'native') ?>">Добавить рецепт</a>
        <span class="engine-switch">
            Шаблон:
            <a href="?engine=native<?= isset($_GET['page']) ? '&page=' . e($_GET['page']) : '' ?>" class="<?= ($engine ?? 'native') === 'native' ? 'active' : '' ?>">PHP</a>
            <a href="?engine=twig<?= isset($_GET['page']) ? '&page=' . e($_GET['page']) : '' ?>" class="<?= ($engine ?? 'native') === 'twig' ? 'active' : '' ?>">Twig</a>
        </span>
    </nav>
</header>
