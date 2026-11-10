<?php

declare(strict_types=1);

/**
 * Банковская транзакция.
 *
 * Класс инкапсулирует данные об одной операции: идентификатор, дату,
 * сумму, описание, получателя и категорию получателя. Все свойства
 * приватные, изменение состояния после создания объекта невозможно.
 */
final class Transaction
{
    private int $id;
    private DateTime $date;
    private float $amount;
    private string $description;
    private string $merchant;
    private string $category;

    /**
     * Создаёт новую транзакцию.
     *
     * @param int $id Уникальный идентификатор транзакции.
     * @param string $date Дата операции в формате YYYY-MM-DD.
     * @param float $amount Сумма операции (больше нуля).
     * @param string $description Описание платежа.
     * @param string $merchant Получатель платежа.
     * @param string $category Категория получателя.
     *
     * @throws InvalidArgumentException Если входные данные некорректны.
     */
    public function __construct(
        int $id,
        string $date,
        float $amount,
        string $description,
        string $merchant,
        string $category
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID транзакции должен быть положительным.');
        }

        $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
        $dateErrors = DateTime::getLastErrors();

        if (
            $parsedDate === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
        ) {
            throw new InvalidArgumentException('Дата транзакции должна быть в формате YYYY-MM-DD.');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Сумма транзакции должна быть больше нуля.');
        }

        $description = trim($description);
        $merchant = trim($merchant);
        $category = trim($category);

        if ($description === '' || $merchant === '' || $category === '') {
            throw new InvalidArgumentException('Описание, получатель и категория не могут быть пустыми.');
        }

        $this->id = $id;
        $this->date = $parsedDate;
        $this->amount = $amount;
        $this->description = $description;
        $this->merchant = $merchant;
        $this->category = $category;
    }

    /**
     * Возвращает идентификатор транзакции.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Возвращает дату транзакции.
     *
     * @return DateTime Копия объекта даты, чтобы защитить внутреннее состояние.
     */
    public function getDate(): DateTime
    {
        return clone $this->date;
    }

    /**
     * Возвращает сумму транзакции.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Возвращает описание платежа.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Возвращает название получателя.
     *
     * @return string
     */
    public function getMerchant(): string
    {
        return $this->merchant;
    }

    /**
     * Возвращает категорию получателя.
     *
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Вычисляет количество дней с даты транзакции до текущего дня.
     *
     * Для будущих дат возвращается отрицательное значение.
     *
     * @return int Количество дней.
     */
    public function getDaysSinceTransaction(): int
    {
        $interval = $this->date->diff(new DateTime('today'));

        return (int) $interval->format('%r%a');
    }

    /**
     * Преобразует транзакцию в массив для сохранения в JSON.
     *
     * @return array{id: int, date: string, amount: float, description: string, merchant: string, category: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'amount' => $this->amount,
            'description' => $this->description,
            'merchant' => $this->merchant,
            'category' => $this->category,
        ];
    }

    /**
     * Создаёт транзакцию из массива, прочитанного из JSON.
     *
     * @param array<string, mixed> $data Данные записи.
     * @return self
     *
     * @throws InvalidArgumentException Если данные некорректны.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['id'] ?? 0),
            (string) ($data['date'] ?? ''),
            (float) ($data['amount'] ?? 0),
            (string) ($data['description'] ?? ''),
            (string) ($data['merchant'] ?? ''),
            (string) ($data['category'] ?? '')
        );
    }
}
