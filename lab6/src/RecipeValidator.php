<?php

declare(strict_types=1);

/**
 * Набор правил валидации формы рецепта.
 *
 * Каждому полю соответствует цепочка валидаторов, реализующих
 * общий интерфейс ValidatorInterface. Проверка останавливается
 * на первой ошибке поля.
 */
final class RecipeValidator
{
    /**
     * @var array<string, ValidatorInterface[]> Правила по полям.
     */
    private array $rules;

    /**
     * Создаёт набор правил валидации.
     */
    public function __construct()
    {
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
                new EnumValidator(array_keys(RecipeOptions::CATEGORIES), 'Выберите категорию из списка.'),
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

    /**
     * Нормализует и проверяет данные формы.
     *
     * @param array<string, mixed> $input Данные из $_POST.
     * @return array{errors: array<string, string>, data: array<string, mixed>}
     *         Массив ошибок по полям и очищенные данные для повторного вывода формы.
     */
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

        // Приводим переносы строк textarea к единому виду \n.
        $data['ingredients'] = str_replace(["\r\n", "\r"], "\n", $data['ingredients']);
        $data['instructions'] = str_replace(["\r\n", "\r"], "\n", $data['instructions']);

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
