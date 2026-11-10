<?php

declare(strict_types=1);

/**
 * Сортировка рецептов по выбранному полю.
 *
 * Класс не изменяет исходный массив: usort() работает с копией,
 * которую возвращает хранилище.
 */
final class RecipeSorter
{
    /**
     * @var string[] Поля, по которым разрешена сортировка.
     */
    public const FIELDS = [
        'id',
        'title',
        'author',
        'category',
        'difficulty',
        'prep_time',
        'created_at',
        'updated_at',
    ];

    /**
     * Сортирует рецепты.
     *
     * @param Recipe[] $recipes Список рецептов.
     * @param string $field Поле сортировки.
     * @param string $direction Направление: asc или desc.
     * @return Recipe[] Отсортированный список.
     */
    public function sort(array $recipes, string $field, string $direction): array
    {
        $field = in_array($field, self::FIELDS, true) ? $field : 'created_at';
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        usort(
            $recipes,
            function (Recipe $a, Recipe $b) use ($field, $direction): int {
                $comparison = $this->compare($a, $b, $field);

                if ($comparison === 0) {
                    return ($a->getId() ?? 0) <=> ($b->getId() ?? 0);
                }

                return $direction === 'asc' ? $comparison : -$comparison;
            }
        );

        return $recipes;
    }

    /**
     * Сравнивает два рецепта по указанному полю.
     *
     * @param Recipe $a Первый рецепт.
     * @param Recipe $b Второй рецепт.
     * @param string $field Поле сравнения.
     * @return int Результат сравнения.
     */
    private function compare(Recipe $a, Recipe $b, string $field): int
    {
        return match ($field) {
            'title' => strcmp(
                mb_strtolower($a->getTitle(), 'UTF-8'),
                mb_strtolower($b->getTitle(), 'UTF-8')
            ),
            'author' => strcmp(
                mb_strtolower($a->getAuthor(), 'UTF-8'),
                mb_strtolower($b->getAuthor(), 'UTF-8')
            ),
            'category' => $this->compareEnum($a->getCategory(), $b->getCategory(), RecipeOptions::CATEGORIES),
            'difficulty' => $this->compareEnum($a->getDifficulty(), $b->getDifficulty(), RecipeOptions::DIFFICULTIES),
            'prep_time' => $a->getPrepTime() <=> $b->getPrepTime(),
            'id' => ($a->getId() ?? 0) <=> ($b->getId() ?? 0),
            'created_at' => strcmp($a->getCreatedAt(), $b->getCreatedAt()),
            'updated_at' => strcmp($a->getUpdatedAt(), $b->getUpdatedAt()),
            default => 0,
        };
    }

    /**
     * Сравнивает enum-значения в порядке, заданном справочником.
     *
     * @param string $a Первое значение.
     * @param string $b Второе значение.
     * @param array<string, string> $order Справочник значений.
     * @return int Результат сравнения.
     */
    private function compareEnum(string $a, string $b, array $order): int
    {
        $keys = array_keys($order);
        $positionA = array_search($a, $keys, true);
        $positionB = array_search($b, $keys, true);

        return ($positionA === false ? PHP_INT_MAX : $positionA)
            <=> ($positionB === false ? PHP_INT_MAX : $positionB);
    }
}
