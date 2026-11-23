<?php

declare(strict_types=1);

/**
 * Модель рецепта.
 */
final class Recipe
{
    private ?int $id;
    private string $title;
    private string $author;
    private int $categoryId;
    private string $category;
    private string $difficulty;
    private int $prepTime;
    private string $ingredients;
    private string $instructions;
    /** @var string[] */
    private array $tags;
    private string $createdAt;
    private string $updatedAt;

    /**
     * @param string[] $tags
     */
    public function __construct(
        ?int $id,
        string $title,
        string $author,
        int $categoryId,
        string $category,
        string $difficulty,
        int $prepTime,
        string $ingredients,
        string $instructions,
        array $tags,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->title = trim($title);
        $this->author = trim($author);
        $this->categoryId = $categoryId;
        $this->category = trim($category);
        $this->difficulty = $difficulty;
        $this->prepTime = $prepTime;
        $this->ingredients = trim($ingredients);
        $this->instructions = trim($instructions);
        $this->tags = array_values($tags);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function getPrepTime(): int
    {
        return $this->prepTime;
    }

    public function getIngredients(): string
    {
        return $this->ingredients;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }

    /** @return string[] */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'category_id' => $this->categoryId,
            'category' => $this->category,
            'difficulty' => $this->difficulty,
            'prep_time' => $this->prepTime,
            'ingredients' => $this->ingredients,
            'instructions' => $this->instructions,
            'tags' => implode(',', $this->tags),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        $tags = array_filter(array_map('trim', explode(',', (string) ($row['tags'] ?? ''))));

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['title'] ?? ''),
            (string) ($row['author'] ?? ''),
            (int) ($row['category_id'] ?? 0),
            (string) ($row['category'] ?? $row['category_name'] ?? ''),
            (string) ($row['difficulty'] ?? 'easy'),
            (int) ($row['prep_time'] ?? 0),
            (string) ($row['ingredients'] ?? ''),
            (string) ($row['instructions'] ?? ''),
            array_values($tags),
            (string) ($row['created_at'] ?? ''),
            (string) ($row['updated_at'] ?? '')
        );
    }
}
