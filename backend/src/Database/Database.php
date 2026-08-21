<?php

declare(strict_types=1);

namespace MoodleImportApp\Database;

use MoodleImportApp\Config\Config;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;

    public function __construct()
    {
        $host = Config::get('DB_HOST', 'localhost');
        $port = Config::getInt('DB_PORT', 5432);
        $dbname = Config::get('DB_NAME', 'moodle_import_app');
        $user = Config::get('DB_USER', 'moodle_import_app');
        $password = Config::get('DB_PASSWORD', '');

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $host,
            $port,
            $dbname
        );

        try {
            self::$connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            new self();
        }

        return self::$connection;
    }

    public function prepare(string $sql): PDOStatement
    {
        return self::getConnection()->prepare($sql);
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }

    public function exec(string $sql): int
    {
        return (int) self::getConnection()->exec($sql);
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->fetchAll($sql, $params);
        return $result[0] ?? null;
    }

    public function lastInsertId(?string $name = null): int
    {
        return (int) self::getConnection()->lastInsertId($name);
    }
}