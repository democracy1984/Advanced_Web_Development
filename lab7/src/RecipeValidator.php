<?php

declare(strict_types=1);

/**
 * Набор правил валидации формы рецепта.
 */
final class RecipeValidator
{
    /** @var array<string, ValidatorInterface[]> */
    private array $rules;

    /**
     * @param string[] $allowedCategoryIds Допустимые ID категорий.
     */
    public function __construct(
        array $allowedCategoryIds
    ) {
        $this->rules = [
            'title' => [
                new RequiredValidator('Укажите название рецепта.'),
                new LengthValidator(3, 100, 'Название должно содержать от 3 до 100 символов.'),
            ],
            'author' => [
                new RequiredValidator('Укажите автора рецепта.'),
                new LengthValidator(2, 80, 'Имя автора должно содержать от 2 до 80 символов.'),
            ],
            'category' => [
                new RequiredValidator('Выберите категорию.'),
                new EnumValidator($allowedCategoryIds, 'Выберите категорию из списка.'),
            ],
            'difficulty' => [
                new RequiredValidator('Выберите сложность.'),
                new EnumValidator(array_keys(RecipeOptions::DIFFICULTIES), 'Выберите сложность из списка.'),
            ],
            'prep_time' => [
                new RequiredValidator('Укажите время приготовления.'),
                new IntegerRangeValidator(1, 1440, 'Время должно быть целым числом от 1 до 1440 минут.'),
            ],
            'ingredients' => [
                new RequiredValidator('Перечислите ингредиенты.'),
                new LengthValidator(10, 2000, 'Ингредиенты должны содержать от 10 до 2000 символов.'),
            ],
            'instructions' => [
                new RequiredValidator('Опишите процесс приготовления.'),
                new LengthValidator(20, 5000, 'Описание должно содержать от 20 до 5000 символов.'),
            ],
            'created_at' => [
                new RequiredValidator('Укажите дату создания.'),
                new DateValidator('Y-m-d', true, 'Укажите корректную дату не в будущем.'),
            ],
            'tags' => [
                new TagsValidator(array_keys(RecipeOptions::TAGS), 'Выбран недопустимый тег.'),
            ],
        ];
    }

    /** @return array{errors: array<string, string>, data: array<string, mixed>} */
    public function validate(array $input): array
    {
        $data = [
            'title' => trim((string) ($input['title'] ?? '')),
            'author' => trim((string) ($input['author'] ?? '')),
            'category' => trim((string) ($input['category'] ?? '')),
            'difficulty' => trim((string) ($input['difficulty'] ?? '')),
            'prep_time' => trim((string) ($input['prep_time'] ?? '')),
            'ingredients' => trim((string) ($input['ingredients'] ?? '')),
            'instructions' => trim((string) ($input['instructions'] ?? '')),
            'created_at' => trim((string) ($input['created_at'] ?? '')),
            'tags' => [],
        ];

        if (isset($input['tags']) && is_array($input['tags'])) {
            $data['tags'] = array_values(array_unique(array_filter($input['tags'], 'is_string')));
        }

        $errors = [];

        foreach ($this->rules as $field => $validators) {
            foreach ($validators as $validator) {
                $error = $validator->validate($data[$field] ?? null);

                if ($error !== null) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }

        return ['errors' => $errors, 'data' => $data];
    }
}
