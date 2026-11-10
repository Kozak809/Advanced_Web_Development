<?php

declare(strict_types=1);

/**
 * Модель рецепта.
 *
 * Объект хранит данные одной записи. Свойства приватные, доступ к ним
 * осуществляется через getter-методы; идентификатор присваивается
 * хранилищем после сохранения.
 */
final class Recipe
{
    private ?int $id;
    private string $title;
    private string $author;
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
     * Создаёт объект рецепта.
     *
     * @param int|null $id Идентификатор или null для новой записи.
     * @param string $title Название рецепта.
     * @param string $author Автор рецепта.
     * @param string $category Код категории.
     * @param string $difficulty Код сложности.
     * @param int $prepTime Время приготовления в минутах.
     * @param string $ingredients Список ингредиентов.
     * @param string $instructions Пошаговое описание приготовления.
     * @param string[] $tags Теги рецепта.
     * @param string $createdAt Дата создания в формате YYYY-MM-DD.
     * @param string $updatedAt Дата обновления в формате YYYY-MM-DD.
     */
    public function __construct(
        ?int $id,
        string $title,
        string $author,
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
        $this->title = $title;
        $this->author = $author;
        $this->category = $category;
        $this->difficulty = $difficulty;
        $this->prepTime = $prepTime;
        $this->ingredients = $ingredients;
        $this->instructions = $instructions;
        $this->tags = array_values($tags);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Возвращает идентификатор рецепта.
     *
     * @return int|null Идентификатор или null, если запись ещё не сохранена.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает название рецепта.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Возвращает автора рецепта.
     *
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Возвращает код категории.
     *
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Возвращает код сложности.
     *
     * @return string
     */
    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    /**
     * Возвращает время приготовления в минутах.
     *
     * @return int
     */
    public function getPrepTime(): int
    {
        return $this->prepTime;
    }

    /**
     * Возвращает список ингредиентов.
     *
     * @return string
     */
    public function getIngredients(): string
    {
        return $this->ingredients;
    }

    /**
     * Возвращает инструкцию по приготовлению.
     *
     * @return string
     */
    public function getInstructions(): string
    {
        return $this->instructions;
    }

    /**
     * Возвращает теги рецепта.
     *
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Возвращает дату создания.
     *
     * @return string Дата в формате YYYY-MM-DD.
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * Возвращает дату последнего обновления.
     *
     * @return string Дата в формате YYYY-MM-DD.
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * Возвращает копию рецепта с присвоенным идентификатором.
     *
     * @param int $id Новый идентификатор.
     * @return self
     */
    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    /**
     * Преобразует рецепт в массив для сохранения в JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'category' => $this->category,
            'difficulty' => $this->difficulty,
            'prep_time' => $this->prepTime,
            'ingredients' => $this->ingredients,
            'instructions' => $this->instructions,
            'tags' => $this->tags,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Создаёт рецепт из массива, прочитанного из JSON.
     *
     * @param array<string, mixed> $data Данные записи.
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $tags = $data['tags'] ?? [];
        if (!is_array($tags)) {
            $tags = [];
        }

        $tags = array_values(array_filter($tags, 'is_string'));

        return new self(
            isset($data['id']) ? (int) $data['id'] : null,
            (string) ($data['title'] ?? ''),
            (string) ($data['author'] ?? ''),
            (string) ($data['category'] ?? ''),
            (string) ($data['difficulty'] ?? ''),
            (int) ($data['prep_time'] ?? 0),
            (string) ($data['ingredients'] ?? ''),
            (string) ($data['instructions'] ?? ''),
            $tags,
            (string) ($data['created_at'] ?? ''),
            (string) ($data['updated_at'] ?? '')
        );
    }
}
