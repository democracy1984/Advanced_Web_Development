<?php

declare(strict_types=1);

/**
 * Сценарии создания и обновления рецептов.
 */
final class RecipeService
{
    public function __construct(
        private RecipeRepository $repository,
        private RecipeValidator $validator,
        private int $defaultCategoryId
    ) {
    }

    /** @return array{success: bool, errors: array<string, string>, data: array<string, mixed>, recipe: ?Recipe} */
    public function create(array $input): array
    {
        $validation = $this->validator->validate($input);
        $data = $validation['data'];
        $errors = $validation['errors'];

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors, 'data' => $data, 'recipe' => null];
        }

        $recipe = new Recipe(
            null,
            (string) $data['title'],
            (string) $data['author'],
            (int) $data['category'],
            '',
            (string) $data['difficulty'],
            (int) $data['prep_time'],
            (string) $data['ingredients'],
            (string) $data['instructions'],
            (array) $data['tags'],
            (string) $data['created_at'],
            date('Y-m-d')
        );

        return [
            'success' => true,
            'errors' => [],
            'data' => $data,
            'recipe' => $this->repository->create($recipe),
        ];
    }

    /** @return array{success: bool, errors: array<string, string>, data: array<string, mixed>, recipe: ?Recipe} */
    public function update(int $id, array $input): array
    {
        $validation = $this->validator->validate($input);
        $data = $validation['data'];
        $errors = $validation['errors'];

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors, 'data' => $data, 'recipe' => null];
        }

        $recipe = new Recipe(
            $id,
            (string) $data['title'],
            (string) $data['author'],
            (int) $data['category'],
            '',
            (string) $data['difficulty'],
            (int) $data['prep_time'],
            (string) $data['ingredients'],
            (string) $data['instructions'],
            (array) $data['tags'],
            (string) $data['created_at'],
            date('Y-m-d')
        );

        $this->repository->update($id, $recipe);

        return [
            'success' => true,
            'errors' => [],
            'data' => $data,
            'recipe' => $recipe,
        ];
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    /** @return array<string, mixed> */
    public function emptyData(): array
    {
        return [
            'title' => '',
            'author' => '',
            'category' => (string) $this->defaultCategoryId,
            'difficulty' => 'easy',
            'prep_time' => '30',
            'ingredients' => '',
            'instructions' => '',
            'created_at' => date('Y-m-d'),
            'tags' => [],
        ];
    }
}
