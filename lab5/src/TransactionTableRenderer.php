<?php

declare(strict_types=1);

/**
 * Формирует HTML-представление коллекции транзакций.
 *
 * Класс отвечает только за вывод и не содержит бизнес-логики.
 */
final class TransactionTableRenderer
{
    /**
     * Формирует HTML-таблицу транзакций.
     *
     * @param Transaction[] $transactions Список транзакций для вывода.
     * @return string HTML-код таблицы.
     *
     * @throws InvalidArgumentException Если массив содержит элементы другого типа.
     */
    public function render(array $transactions): string
    {
        $rows = '';

        foreach ($transactions as $transaction) {
            if (!$transaction instanceof Transaction) {
                throw new InvalidArgumentException('Массив должен содержать только объекты Transaction.');
            }

            $rows .= sprintf(
                '<tr>'
                . '<td>%d</td>'
                . '<td>%s</td>'
                . '<td class="amount">%s</td>'
                . '<td>%s</td>'
                . '<td>%s</td>'
                . '<td><span class="category">%s</span></td>'
                . '<td>%d</td>'
                . '</tr>',
                $transaction->getId(),
                $this->escape($transaction->getDate()->format('d.m.Y')),
                number_format($transaction->getAmount(), 2, '.', ' '),
                $this->escape($transaction->getDescription()),
                $this->escape($transaction->getMerchant()),
                $this->escape($transaction->getCategory()),
                $transaction->getDaysSinceTransaction()
            );
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="7" class="empty">Транзакции отсутствуют.</td></tr>';
        }

        return '<table class="transactions-table">'
            . '<thead>'
            . '<tr>'
            . '<th>ID транзакции</th>'
            . '<th>Дата</th>'
            . '<th>Сумма ($)</th>'
            . '<th>Описание</th>'
            . '<th>Название получателя</th>'
            . '<th>Категория получателя</th>'
            . '<th>Дней с момента транзакции</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>'
            . $rows
            . '</tbody>'
            . '</table>';
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
