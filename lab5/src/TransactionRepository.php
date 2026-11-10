<?php

declare(strict_types=1);

/**
 * Хранилище транзакций в JSON-файле.
 *
 * Класс отвечает только за загрузку, сохранение и базовый доступ
 * к коллекции. Бизнес-логика находится в TransactionManager.
 */
final class TransactionRepository implements TransactionStorageInterface
{
    /**
     * @var Transaction[] Коллекция транзакций.
     */
    private array $transactions = [];

    /**
     * @param string $filePath Путь к JSON-файлу с транзакциями.
     */
    public function __construct(
        private string $filePath
    ) {
        if (is_file($this->filePath)) {
            $rows = json_decode((string) file_get_contents($this->filePath), true);

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (is_array($row)) {
                        $this->transactions[] = Transaction::fromArray($row);
                    }
                }
            }
        }
    }

    /**
     * Добавляет транзакцию и сохраняет файл.
     *
     * @param Transaction $transaction Транзакция для добавления.
     * @return void
     *
     * @throws InvalidArgumentException Если транзакция с таким ID уже есть.
     */
    public function addTransaction(Transaction $transaction): void
    {
        if ($this->findById($transaction->getId()) !== null) {
            throw new InvalidArgumentException('Транзакция с таким ID уже существует.');
        }

        $this->transactions[] = $transaction;
        $this->save();
    }

    /**
     * Удаляет транзакцию по идентификатору и сохраняет файл.
     *
     * @param int $id Идентификатор транзакции.
     * @return void
     */
    public function removeTransactionById(int $id): void
    {
        foreach ($this->transactions as $index => $transaction) {
            if ($transaction->getId() === $id) {
                unset($this->transactions[$index]);
                $this->transactions = array_values($this->transactions);
                $this->save();

                return;
            }
        }
    }

    /**
     * Возвращает все транзакции.
     *
     * @return Transaction[]
     */
    public function getAllTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * Находит транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции.
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction
    {
        foreach ($this->transactions as $transaction) {
            if ($transaction->getId() === $id) {
                return $transaction;
            }
        }

        return null;
    }

    /**
     * Возвращает следующий свободный идентификатор.
     *
     * @return int
     */
    public function getNextId(): int
    {
        $maxId = 0;

        foreach ($this->transactions as $transaction) {
            $maxId = max($maxId, $transaction->getId());
        }

        return $maxId + 1;
    }

    /**
     * Сохраняет коллекцию в JSON-файл.
     *
     * @return void
     *
     * @throws RuntimeException Если запись не удалась.
     */
    private function save(): void
    {
        $rows = array_map(
            static fn (Transaction $transaction): array => $transaction->toArray(),
            $this->transactions
        );

        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false || file_put_contents($this->filePath, $json . PHP_EOL) === false) {
            throw new RuntimeException('Не удалось сохранить транзакции в файл.');
        }
    }
}
