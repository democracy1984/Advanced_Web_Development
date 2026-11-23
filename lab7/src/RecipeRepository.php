<?php

declare(strict_types=1);

/**
 * Работа с таблицами recipes и categories через PDO.
 */
final class RecipeRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /** @return array<int, string> */
    public function allCategories(): array
    {
        $rows = $this->pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
        $result = [];

        foreach ($rows as $row) {
            $result[(int) $row['id']] = (string) $row['name'];
        }

        return $result;
    }

    /** @return Recipe[] */
    public function all(): array
    {
        $sql = 'SELECT r.*, c.name AS category_name
                FROM recipes r
                JOIN categories c ON c.id = r.category_id
                ORDER BY r.id DESC';

        $statement = $this->pdo->query($sql);
        $recipes = [];

        foreach ($statement->fetchAll() as $row) {
            $recipes[] = Recipe::fromRow($row);
        }

        return $recipes;
    }

    public function findById(int $id): ?Recipe
    {
        $statement = $this->pdo->prepare(
            'SELECT r.*, c.name AS category_name
             FROM recipes r
             JOIN categories c ON c.id = r.category_id
             WHERE r.id = :id'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch();

        return $row ? Recipe::fromRow($row) : null;
    }

    public function create(Recipe $recipe): Recipe
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO recipes
                (title, author, category_id, difficulty, prep_time, ingredients, instructions, tags, created_at, updated_at)
             VALUES
                (:title, :author, :category_id, :difficulty, :prep_time, :ingredients, :instructions, :tags, :created_at, :updated_at)'
        );

        $statement->execute([
            ':title' => $recipe->getTitle(),
            ':author' => $recipe->getAuthor(),
            ':category_id' => $recipe->getCategoryId(),
            ':difficulty' => $recipe->getDifficulty(),
            ':prep_time' => $recipe->getPrepTime(),
            ':ingredients' => $recipe->getIngredients(),
            ':instructions' => $recipe->getInstructions(),
            ':tags' => implode(',', $recipe->getTags()),
            ':created_at' => $recipe->getCreatedAt(),
            ':updated_at' => $recipe->getUpdatedAt(),
        ]);

        $savedId = (int) $this->pdo->lastInsertId();

        return $this->findById($savedId) ?? $recipe->withId($savedId);
    }

    public function update(int $id, Recipe $recipe): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE recipes
             SET title = :title,
                 author = :author,
                 category_id = :category_id,
                 difficulty = :difficulty,
                 prep_time = :prep_time,
                 ingredients = :ingredients,
                 instructions = :instructions,
                 tags = :tags,
                 created_at = :created_at,
                 updated_at = :updated_at
             WHERE id = :id'
        );

        $statement->execute([
            ':id' => $id,
            ':title' => $recipe->getTitle(),
            ':author' => $recipe->getAuthor(),
            ':category_id' => $recipe->getCategoryId(),
            ':difficulty' => $recipe->getDifficulty(),
            ':prep_time' => $recipe->getPrepTime(),
            ':ingredients' => $recipe->getIngredients(),
            ':instructions' => $recipe->getInstructions(),
            ':tags' => implode(',', $recipe->getTags()),
            ':created_at' => $recipe->getCreatedAt(),
            ':updated_at' => $recipe->getUpdatedAt(),
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM recipes WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    /** @return Recipe[] */
    public function search(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return $this->all();
        }

        $statement = $this->pdo->prepare(
            "SELECT r.*, c.name AS category_name
             FROM recipes r
             JOIN categories c ON c.id = r.category_id
             WHERE r.title LIKE :query
                OR r.author LIKE :query
                OR c.name LIKE :query
             ORDER BY r.id DESC"
        );
        $statement->execute([':query' => '%' . $query . '%']);

        $recipes = [];

        foreach ($statement->fetchAll() as $row) {
            $recipes[] = Recipe::fromRow($row);
        }

        return $recipes;
    }
}
