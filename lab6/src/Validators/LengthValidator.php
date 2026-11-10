<?php

declare(strict_types=1);

/**
 * Проверяет длину строки в символах UTF-8.
 */
final class LengthValidator implements ValidatorInterface
{
    /**
     * @param int $min Минимальная длина.
     * @param int $max Максимальная длина.
     * @param string $message Сообщение об ошибке.
     */
    public function __construct(
        private int $min,
        private int $max,
        private string $message = ''
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): ?string
    {
        if (!is_string($value)) {
            return 'Значение должно быть строкой.';
        }

        $length = mb_strlen(trim($value), 'UTF-8');

        if ($length < $this->min || $length > $this->max) {
            return $this->message !== ''
                ? $this->message
                : sprintf('Длина должна быть от %d до %d символов.', $this->min, $this->max);
        }

        return null;
    }
}
