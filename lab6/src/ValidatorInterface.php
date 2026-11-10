<?php

declare(strict_types=1);

/**
 * Общий контракт валидатора.
 *
 * Позволяет хранить разные правила проверки в одном массиве
 * и добавлять новые валидаторы без изменения формы.
 */
interface ValidatorInterface
{
    /**
     * Проверяет значение.
     *
     * @param mixed $value Проверяемое значение.
     * @return string|null Текст ошибки или null, если значение корректно.
     */
    public function validate(mixed $value): ?string;
}
