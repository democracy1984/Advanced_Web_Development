<?php

declare(strict_types=1);

/**
 * Форматирует минуты в строку вида "1 ч 30 мин".
 */
function formatDuration(int $minutes): string
{
    $hours = intdiv($minutes, 60);
    $mins = $minutes % 60;

    if ($hours === 0) {
        return $mins . ' мин';
    }

    if ($mins === 0) {
        return $hours . ' ч';
    }

    return $hours . ' ч ' . $mins . ' мин';
}

/**
 * Экранирует значение для HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
