<?php

declare(strict_types=1);

/**
 * Проверяет целое число и его диапазон.
 */
final class IntegerRangeValidator implements ValidatorInterface
{
    /**
     * @param int $min Минимальное значение.
     * @param int $max Максимальное значение.
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
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        if ($integer === false) {
            return $this->message !== '' ? $this->message : 'Введите целое число.';
        }

        if ($integer < $this->min || $integer > $this->max) {
            return $this->message !== ''
                ? $this->message
                : sprintf('Значение должно быть от %d до %d.', $this->min, $this->max);
        }

        return null;
    }
}
