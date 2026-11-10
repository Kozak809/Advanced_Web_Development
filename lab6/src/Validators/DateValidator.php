<?php

declare(strict_types=1);

/**
 * Проверяет дату на соответствие формату и при необходимости запрещает будущее.
 */
final class DateValidator implements ValidatorInterface
{
    /**
     * @param string $format Формат даты (например, Y-m-d).
     * @param bool $notInFuture Запрещать даты позже сегодняшнего дня.
     * @param string $message Сообщение об ошибке.
     */
    public function __construct(
        private string $format = 'Y-m-d',
        private bool $notInFuture = true,
        private string $message = 'Укажите корректную дату.'
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return $this->message;
        }

        $date = DateTime::createFromFormat('!' . $this->format, $value);
        $errors = DateTime::getLastErrors();

        if (
            $date === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        ) {
            return $this->message;
        }

        if ($this->notInFuture && $date > new DateTime('today')) {
            return 'Дата не может быть в будущем.';
        }

        return null;
    }
}
