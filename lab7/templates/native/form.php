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
    <h2><?= !empty($isEdit) ? 'Редактирование рецепта' : 'Новый рецепт' ?></h2>
    <p><?= !empty($isEdit) ? 'Измените данные и сохраните.' : 'Заполните все обязательные поля.' ?></p>

    <form method="post" action="<?= e($formAction ?? 'index.php') ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="action" value="<?= !empty($isEdit) ? 'update' : 'create' ?>">

        <?php if (!empty($isEdit)): ?>
            <input type="hidden" name="id" value="<?= (int) ($formData['id'] ?? 0) ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="field field-wide">
                <label for="title">Название рецепта</label>
                <input type="text" id="title" name="title" value="<?= e($formData['title'] ?? '') ?>" required minlength="3" maxlength="100">
                <?php if (isset($errors['title'])): ?>
                    <p class="field-error"><?= e($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="author">Автор</label>
                <input type="text" id="author" name="author" value="<?= e($formData['author'] ?? '') ?>" required minlength="2" maxlength="80">
                <?php if (isset($errors['author'])): ?>
                    <p class="field-error"><?= e($errors['author']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="category">Категория</label>
                <select id="category" name="category" required>
                    <option value="">Выберите категорию</option>
                    <?php foreach (($categories ?? []) as $id => $name): ?>
                        <option value="<?= (int) $id ?>" <?= (string) ($formData['category'] ?? '') === (string) $id ? 'selected' : '' ?>>
                            <?= e($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <p class="field-error"><?= e($errors['category']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="difficulty">Сложность</label>
                <select id="difficulty" name="difficulty" required>
                    <option value="">Выберите сложность</option>
                    <?php foreach (RecipeOptions::DIFFICULTIES as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($formData['difficulty'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['difficulty'])): ?>
                    <p class="field-error"><?= e($errors['difficulty']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="prep_time">Время приготовления, мин</label>
                <input type="number" id="prep_time" name="prep_time" value="<?= e($formData['prep_time'] ?? '30') ?>" required min="1" max="1440">
                <?php if (isset($errors['prep_time'])): ?>
                    <p class="field-error"><?= e($errors['prep_time']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="created_at">Дата создания</label>
                <input type="date" id="created_at" name="created_at" value="<?= e($formData['created_at'] ?? date('Y-m-d')) ?>" required>
                <?php if (isset($errors['created_at'])): ?>
                    <p class="field-error"><?= e($errors['created_at']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field field-wide">
                <label for="ingredients">Ингредиенты</label>
                <textarea id="ingredients" name="ingredients" rows="4" required minlength="10" maxlength="2000"><?= e($formData['ingredients'] ?? '') ?></textarea>
                <?php if (isset($errors['ingredients'])): ?>
                    <p class="field-error"><?= e($errors['ingredients']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field field-wide">
                <label for="instructions">Приготовление</label>
                <textarea id="instructions" name="instructions" rows="5" required minlength="20" maxlength="5000"><?= e($formData['instructions'] ?? '') ?></textarea>
                <?php if (isset($errors['instructions'])): ?>
                    <p class="field-error"><?= e($errors['instructions']) ?></p>
                <?php endif; ?>
            </div>

            <fieldset class="field field-wide tags-field">
                <legend>Теги</legend>
                <div class="checkbox-grid">
                    <?php foreach (RecipeOptions::TAGS as $value => $label): ?>
                        <label class="checkbox">
                            <input type="checkbox" name="tags[]" value="<?= e($value) ?>" <?= in_array($value, (array) ($formData['tags'] ?? []), true) ? 'checked' : '' ?>>
                            <span><?= e($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['tags'])): ?>
                    <p class="field-error"><?= e($errors['tags']) ?></p>
                <?php endif; ?>
            </fieldset>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn"><?= !empty($isEdit) ? 'Сохранить изменения' : 'Сохранить рецепт' ?></button>
            <a href="index.php?engine=<?= e($engine ?? 'native') ?>" class="btn btn-secondary">Отмена</a>
            <?php if (!empty($isEdit)): ?>
                <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Удалить рецепт?')">Удалить</button>
            <?php endif; ?>
        </div>
    </form>
</section>
