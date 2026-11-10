<?php

declare(strict_types=1);

/**
 * Сервис создания рецепта.
 *
 * Связывает валидацию, модель и хранилище, оставляя index.php
 * роль контроллера: принять запрос, вызвать сервис и показать результат.
 */
final class RecipeService
{
    /**
     * @param RecipeRepository $repository Хранилище рецептов.
     * @param RecipeValidator $validator Валидатор формы.
     */
    public function __construct(
        private RecipeRepository $repository,
        private RecipeValidator $validator
    ) {
    }

    /**
     * Проверяет данные формы и сохраняет новый рецепт.
     *
     * @param array<string, mixed> $input Данные из $_POST.
     * @return array{success: bool, errors: array<string, string>, data: array<string, mixed>, recipe: Recipe|null}
     *         Результат операции, ошибки, очищенные данные и сохранённый рецепт.
     *
     * @throws RuntimeException Если не удалось записать данные в хранилище.
     */
    public function create(array $input): array
    {
        $validation = $this->validator->validate($input);
        $data = $validation['data'];
        $errors = $validation['errors'];

        if ($errors !== []) {
            return [
                'success' => false,
                'errors' => $errors,
                'data' => $data,
                'recipe' => null,
            ];
        }

        $recipe = new Recipe(
            null,
            (string) $data['title'],
            (string) $data['author'],
            (string) $data['category'],
            (string) $data['difficulty'],
            (int) $data['prep_time'],
            (string) $data['ingredients'],
            (string) $data['instructions'],
            (array) $data['tags'],
            (string) $data['created_at'],
            date('Y-m-d')
        );

        $savedRecipe = $this->repository->add($recipe);

        return [
            'success' => true,
            'errors' => [],
            'data' => $data,
            'recipe' => $savedRecipe,
        ];
    }

    /**
     * Возвращает значения формы по умолчанию.
     *
     * @return array<string, mixed>
     */
    public function emptyData(): array
    {
        return [
            'title' => '',
            'author' => '',
            'category' => '',
            'difficulty' => '',
            'prep_time' => '30',
            'ingredients' => '',
            'instructions' => '',
            'created_at' => date('Y-m-d'),
            'tags' => [],
        ];
    }
}
