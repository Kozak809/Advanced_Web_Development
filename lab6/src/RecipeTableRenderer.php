<?php

declare(strict_types=1);

/**
 * Формирует HTML-таблицу рецептов.
 *
 * Класс отвечает только за представление: данные получает готовыми,
 * ничего не сохраняет и не изменяет.
 */
final class RecipeTableRenderer
{
    /**
     * @var array<string, string> Сортируемые столбцы: поле => подпись.
     */
    private const COLUMNS = [
        'id' => 'ID',
        'title' => 'Название',
        'author' => 'Автор',
        'category' => 'Категория',
        'difficulty' => 'Сложность',
        'prep_time' => 'Время, мин',
        'created_at' => 'Создано',
    ];

    /**
     * Формирует HTML-таблицу со всеми рецептами.
     *
     * @param Recipe[] $recipes Список рецептов.
     * @param string $sort Текущее поле сортировки.
     * @param string $direction Текущее направление сортировки.
     * @return string HTML-код таблицы.
     *
     * @throws InvalidArgumentException Если массив содержит не Recipe.
     */
    public function render(array $recipes, string $sort, string $direction): string
    {
        $headers = '';

        foreach (self::COLUMNS as $field => $label) {
            $isActive = $field === $sort;
            $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';
            $query = http_build_query(['sort' => $field, 'dir' => $nextDirection]);
            $arrow = $isActive ? ($direction === 'asc' ? ' ↑' : ' ↓') : '';
            $class = $isActive ? ' class="sorted"' : '';

            $headers .= '<th><a href="?' . $this->escape($query) . '"' . $class . '>'
                . $this->escape($label) . $arrow
                . '</a></th>';
        }

        $headers .= '<th>Теги</th><th>Подробнее</th>';

        $rows = '';

        foreach ($recipes as $recipe) {
            if (!$recipe instanceof Recipe) {
                throw new InvalidArgumentException('Массив должен содержать только объекты Recipe.');
            }

            $categoryLabel = RecipeOptions::CATEGORIES[$recipe->getCategory()] ?? $recipe->getCategory();
            $difficultyLabel = RecipeOptions::DIFFICULTIES[$recipe->getDifficulty()] ?? $recipe->getDifficulty();

            $rows .= '<tr>'
                . '<td>' . $this->escape((string) ($recipe->getId() ?? '—')) . '</td>'
                . '<td><strong>' . $this->escape($recipe->getTitle()) . '</strong></td>'
                . '<td>' . $this->escape($recipe->getAuthor()) . '</td>'
                . '<td><span class="badge badge-category">' . $this->escape($categoryLabel) . '</span></td>'
                . '<td><span class="badge badge-' . $this->escape($recipe->getDifficulty()) . '">'
                . $this->escape($difficultyLabel) . '</span></td>'
                . '<td>' . $recipe->getPrepTime() . '</td>'
                . '<td>' . $this->escape($this->formatDate($recipe->getCreatedAt())) . '</td>'
                . '<td>' . $this->renderTags($recipe->getTags()) . '</td>'
                . '<td>' . $this->renderDetails($recipe) . '</td>'
                . '</tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="9" class="empty">Пока нет рецептов. Добавьте первый!</td></tr>';
        }

        return '<table class="recipes-table">'
            . '<thead><tr>' . $headers . '</tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>';
    }

    /**
     * Формирует список тегов.
     *
     * @param string[] $tags Теги рецепта.
     * @return string HTML-код.
     */
    private function renderTags(array $tags): string
    {
        if ($tags === []) {
            return '<span class="muted">—</span>';
        }

        $html = '';

        foreach ($tags as $tag) {
            $label = RecipeOptions::TAGS[$tag] ?? $tag;
            $html .= '<span class="tag">' . $this->escape($label) . '</span>';
        }

        return $html;
    }

    /**
     * Формирует раскрывающийся блок с ингредиентами и инструкцией.
     *
     * @param Recipe $recipe Рецепт.
     * @return string HTML-код.
     */
    private function renderDetails(Recipe $recipe): string
    {
        return '<details>'
            . '<summary>Открыть</summary>'
            . '<div class="details">'
            . '<p><strong>Ингредиенты:</strong><br>'
            . nl2br($this->escape($recipe->getIngredients()))
            . '</p>'
            . '<p><strong>Приготовление:</strong><br>'
            . nl2br($this->escape($recipe->getInstructions()))
            . '</p>'
            . '<p class="meta">Обновлено: ' . $this->escape($this->formatDate($recipe->getUpdatedAt())) . '</p>'
            . '</div>'
            . '</details>';
    }

    /**
     * Форматирует дату из YYYY-MM-DD в DD.MM.YYYY.
     *
     * @param string $date Дата в формате YYYY-MM-DD.
     * @return string Отформатированная дата или исходная строка.
     */
    private function formatDate(string $date): string
    {
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);

        return $parsed !== false ? $parsed->format('d.m.Y') : $date;
    }

    /**
     * Экранирует значение для безопасного вывода в HTML.
     *
     * @param string $value Исходная строка.
     * @return string Экранированная строка.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
