<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= e($flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-error"><?= e($flashError) ?></div>
<?php endif; ?>

<?php if (!empty($errors['form'] ?? '')): ?>
    <div class="alert alert-error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<section class="panel">
    <h2>Список рецептов</h2>
    <p>Всего рецептов: <?= (int) ($stats['total'] ?? 0) ?>. Среднее время приготовления: <?= e(formatDuration((int) ($stats['average'] ?? 0))) ?>.</p>

    <div class="table-wrap">
        <table class="recipes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Автор</th>
                    <th>Категория</th>
                    <th>Сложность</th>
                    <th>Время</th>
                    <th>Создано</th>
                    <th>Теги</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($recipes ?? []) as $recipe): ?>
                    <tr>
                        <td><?= (int) $recipe->getId() ?></td>
                        <td><strong><?= e($recipe->getTitle()) ?></strong></td>
                        <td><?= e($recipe->getAuthor()) ?></td>
                        <td><span class="badge badge-category"><?= e($recipe->getCategory()) ?></span></td>
                        <td>
                            <span class="badge badge-<?= e($recipe->getDifficulty()) ?>">
                                <?= e(RecipeOptions::DIFFICULTIES[$recipe->getDifficulty()] ?? $recipe->getDifficulty()) ?>
                            </span>
                        </td>
                        <td><?= e(formatDuration($recipe->getPrepTime())) ?></td>
                        <td><?= e((new DateTime($recipe->getCreatedAt()))->format('d.m.Y')) ?></td>
                        <td>
                            <?php foreach ($recipe->getTags() as $tag): ?>
                                <span class="tag"><?= e(RecipeOptions::TAGS[$tag] ?? $tag) ?></span>
                            <?php endforeach; ?>
                            <?php if ($recipe->getTags() === []): ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn btn-secondary" href="edit.php?id=<?= (int) $recipe->getId() ?>&engine=<?= e($engine ?? 'native') ?>">Редактировать</a>
                            <form method="post" action="index.php?engine=<?= e($engine ?? 'native') ?>" style="display:inline-block; margin-top:6px;">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $recipe->getId() ?>">
                                <button type="submit" class="btn btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9">
                            <details>
                                <summary>Ингредиенты и приготовление</summary>
                                <div class="details">
                                    <p><strong>Ингредиенты:</strong><br><?= nl2br(e($recipe->getIngredients())) ?></p>
                                    <p><strong>Приготовление:</strong><br><?= nl2br(e($recipe->getInstructions())) ?></p>
                                    <p class="meta">Обновлено: <?= e((new DateTime($recipe->getUpdatedAt()))->format('d.m.Y')) ?></p>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (($recipes ?? []) === []): ?>
                    <tr>
                        <td colspan="9" class="empty">Рецептов пока нет. Добавьте первый!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
