<?php

declare(strict_types=1);

/**
 * Подключение к базе данных через PDO.
 */
final class Database
{
    /**
     * @param array{driver: string, path: string} $config Параметры подключения.
     */
    public function __construct(
        private array $config
    ) {
    }

    /**
     * Возвращает объект PDO.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        $pdo = new PDO('sqlite:' . $this->config['path']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        return $pdo;
    }
}
