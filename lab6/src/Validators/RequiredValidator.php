<?php

declare(strict_types=1);

/**
 * Проверяет, что значение заполнено.
 */
final class RequiredValidator implements ValidatorInterface
{
    /**
     * @param string $message Сообщение об ошибке.
     */
    public function __construct(
        private string $message = 'Поле обязательно для заполнения.'
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): ?string
    {
        if ($value === null) {
            return $this->message;
        }

        if (is_string($value) && trim($value) === '') {
            return $this->message;
        }

        if (is_array($value) && $value === []) {
            return $this->message;
        }

        return null;
    }
}
