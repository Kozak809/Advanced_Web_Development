<?php

declare(strict_types=1);

/**
 * Проверяет массив тегов на допустимые значения.
 */
final class TagsValidator implements ValidatorInterface
{
    /**
     * @param string[] $allowed Допустимые теги.
     * @param string $message Сообщение об ошибке.
     */
    public function __construct(
        private array $allowed,
        private string $message = 'Выбран недопустимый тег.'
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            return $this->message;
        }

        foreach ($value as $tag) {
            if (!is_string($tag) || !in_array($tag, $this->allowed, true)) {
                return $this->message;
            }
        }

        return null;
    }
}
