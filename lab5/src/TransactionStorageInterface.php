<?php

declare(strict_types=1);

/**
 * Контракт хранилища транзакций.
 *
 * Абстрагирует TransactionManager от конкретного способа хранения:
 * память, база данных или удалённый API.
 */
interface TransactionStorageInterface
{
    /**
     * Добавляет транзакцию в хранилище.
     *
     * @param Transaction $transaction Транзакция для добавления.
     * @return void
     *
     * @throws InvalidArgumentException Если транзакция с таким ID уже существует.
     */
    public function addTransaction(Transaction $transaction): void;

    /**
     * Удаляет транзакцию по идентификатору.
     *
     * Если транзакция с указанным ID не найдена, метод ничего не меняет.
     *
     * @param int $id Идентификатор транзакции.
     * @return void
     */
    public function removeTransactionById(int $id): void;

    /**
     * Возвращает все транзакции.
     *
     * @return Transaction[] Список транзакций.
     */
    public function getAllTransactions(): array;

    /**
     * Находит транзакцию по идентификатору.
     *
     * @param int $id Идентификатор транзакции.
     * @return Transaction|null Найденная транзакция или null.
     */
    public function findById(int $id): ?Transaction;
}
