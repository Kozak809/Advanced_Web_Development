<?php

declare(strict_types=1);

/**
 * JSON-хранилище рецептов.
 *
 * Отвечает только за чтение и запись файла data.json.
 * При добавлении записи используется блокировка файла,
 * чтобы не потерять данные при одновременных запросах.
 */
final class RecipeRepository
{
    /**
     * @param string $filePath Путь к JSON-файлу с рецептами.
     */
    public function __construct(
        private string $filePath
    ) {
    }

    /**
     * Возвращает все рецепты из хранилища.
     *
     * @return Recipe[]
     *
     * @throws RuntimeException Если файл не удаётся прочитать или JSON повреждён.
     */
    public function all(): array
    {
        $recipes = [];

        foreach ($this->readRows() as $row) {
            $recipes[] = Recipe::fromArray($row);
        }

        return $recipes;
    }

    /**
     * Добавляет рецепт в хранилище и присваивает ему идентификатор.
     *
     * @param Recipe $recipe Рецепт без идентификатора.
     * @return Recipe Сохранённый рецепт с присвоенным ID.
     *
     * @throws RuntimeException Если файл недоступен или запись не удалась.
     */
    public function add(Recipe $recipe): Recipe
    {
        $handle = fopen($this->filePath, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Не удалось открыть файл данных для записи.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Не удалось заблокировать файл данных.');
            }

            $contents = stream_get_contents($handle);

            if ($contents === false) {
                throw new RuntimeException('Не удалось прочитать файл данных.');
            }

            $rows = $this->decodeRows($contents);
            $storedRecipe = $recipe->withId($this->nextId($rows));
            $rows[] = $storedRecipe->toArray();

            try {
                $json = json_encode(
                    $rows,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                );
            } catch (JsonException $exception) {
                throw new RuntimeException('Не удалось преобразовать данные в JSON.', 0, $exception);
            }

            rewind($handle);

            if (!ftruncate($handle, 0)) {
                throw new RuntimeException('Не удалось очистить файл данных перед записью.');
            }

            if (fwrite($handle, $json . PHP_EOL) === false) {
                throw new RuntimeException('Не удалось записать данные в файл.');
            }

            fflush($handle);

            return $storedRecipe;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * Читает и декодирует строки из файла.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws RuntimeException Если файл недоступен или JSON повреждён.
     */
    private function readRows(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $contents = file_get_contents($this->filePath);

        if ($contents === false) {
            throw new RuntimeException('Не удалось прочитать файл данных.');
        }

        return $this->decodeRows($contents);
    }

    /**
     * Декодирует JSON и проверяет, что это список объектов.
     *
     * @param string $contents Содержимое файла.
     * @return array<int, array<string, mixed>>
     *
     * @throws RuntimeException Если JSON некорректен.
     */
    private function decodeRows(string $contents): array
    {
        $contents = trim($contents);

        if ($contents === '') {
            return [];
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Файл данных содержит некорректный JSON.', 0, $exception);
        }

        if (!is_array($data) || !array_is_list($data)) {
            throw new RuntimeException('Файл данных должен содержать список записей.');
        }

        $rows = [];

        foreach ($data as $row) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Вычисляет следующий свободный идентификатор.
     *
     * @param array<int, array<string, mixed>> $rows Сохранённые записи.
     * @return int Новый идентификатор.
     */
    private function nextId(array $rows): int
    {
        $maxId = 0;

        foreach ($rows as $row) {
            $id = isset($row['id']) ? (int) $row['id'] : 0;

            if ($id > $maxId) {
                $maxId = $id;
            }
        }

        return $maxId + 1;
    }
}
