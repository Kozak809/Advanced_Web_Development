<?php

declare(strict_types=1);

/**
 * Бизнес-логика над коллекцией транзакций.
 *
 * Не хранит транзакции самостоятельно и работает с хранилищем
 * через интерфейс TransactionStorageInterface.
 */
final class TransactionManager
{
    /**
     * @param TransactionStorageInterface $repository Хранилище транзакций.
     */
    public function __construct(
        private TransactionStorageInterface $repository
    ) {
    }

    /**
     * Вычисляет общую сумму всех транзакций.
     *
     * @return float Общая сумма.
     */
    public function calculateTotalAmount(): float
    {
        $total = 0.0;

        foreach ($this->repository->getAllTransactions() as $transaction) {
            $total += $transaction->getAmount();
        }

        return $total;
    }

    /**
     * Вычисляет сумму транзакций за период включительно.
     *
     * @param string $startDate Начало периода в формате YYYY-MM-DD.
     * @param string $endDate Конец периода в формате YYYY-MM-DD.
     * @return float Сумма транзакций за период.
     *
     * @throws InvalidArgumentException Если начало позже конца.
     * @throws Exception Если дата некорректна.
     */
    public function calculateTotalAmountByDateRange(string $startDate, string $endDate): float
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end->setTime(23, 59, 59);

        if ($start > $end) {
            throw new InvalidArgumentException('Начальная дата не может быть позже конечной.');
        }

        $total = 0.0;

        foreach ($this->repository->getAllTransactions() as $transaction) {
            $transactionDate = $transaction->getDate();

            if ($transactionDate >= $start && $transactionDate <= $end) {
                $total += $transaction->getAmount();
            }
        }

        return $total;
    }

    /**
     * Считает количество транзакций по получателю (без учёта регистра).
     *
     * @param string $merchant Название получателя.
     * @return int Количество транзакций.
     */
    public function countTransactionsByMerchant(string $merchant): int
    {
        $count = 0;
        $normalizedMerchant = mb_strtolower($merchant, 'UTF-8');

        foreach ($this->repository->getAllTransactions() as $transaction) {
            if (mb_strtolower($transaction->getMerchant(), 'UTF-8') === $normalizedMerchant) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Возвращает транзакции, отсортированные по дате (от старых к новым).
     *
     * @return Transaction[] Отсортированный список транзакций.
     */
    public function sortTransactionsByDate(): array
    {
        $transactions = $this->repository->getAllTransactions();

        usort(
            $transactions,
            static fn (Transaction $a, Transaction $b): int => $a->getDate()->getTimestamp() <=> $b->getDate()->getTimestamp()
        );

        return $transactions;
    }

    /**
     * Возвращает транзакции, отсортированные по сумме по убыванию.
     *
     * @return Transaction[] Отсортированный список транзакций.
     */
    public function sortTransactionsByAmountDesc(): array
    {
        $transactions = $this->repository->getAllTransactions();

        usort(
            $transactions,
            static fn (Transaction $a, Transaction $b): int => $b->getAmount() <=> $a->getAmount()
        );

        return $transactions;
    }
}
