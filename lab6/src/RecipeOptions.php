<?php

declare(strict_types=1);

/**
 * Справочники допустимых значений для рецептов.
 *
 * Класс централизует enum-значения, чтобы форма, валидация,
 * сортировка и вывод использовали одни и те же наборы.
 */
final class RecipeOptions
{
    /**
     * @var array<string, string> Категории блюд: код => подпись.
     */
    public const CATEGORIES = [
        'breakfast' => 'Завтрак',
        'lunch' => 'Обед',
        'dinner' => 'Ужин',
        'dessert' => 'Десерт',
        'drink' => 'Напиток',
    ];

    /**
     * @var array<string, string> Уровни сложности: код => подпись.
     */
    public const DIFFICULTIES = [
        'easy' => 'Лёгкая',
        'medium' => 'Средняя',
        'hard' => 'Сложная',
    ];

    /**
     * @var array<string, string> Дополнительные теги: код => подпись.
     */
    public const TAGS = [
        'vegetarian' => 'Вегетарианское',
        'vegan' => 'Веганское',
        'gluten_free' => 'Без глютена',
        'lactose_free' => 'Без лактозы',
    ];

    /**
     * Класс содержит только константы и не предназначен для создания объектов.
     */
    private function __construct()
    {
    }
}
