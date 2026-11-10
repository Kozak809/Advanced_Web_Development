<?php

declare(strict_types=1);

/**
 * Проверяет, что значение входит в ограниченный набор (enum).
 */
final class EnumValidator implements ValidatorInterface
{
    /**
     * @param string[] $allowed Допустимые значения.
     * @param string $message Сообщение об ошибке.
     */
    public function __construct(
        private array $allowed,
        private string $message = 'Выберите значение из списка.'
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): ?string
    {
        if (!is_string($value) || !in_array($value, $this->allowed, true)) {
            return $this->message;
        }

        return null;
    }
}
