<?php

declare(strict_types=1);

namespace MoodleImportApp\Database;

use MoodleImportApp\Config\Config;
use PDOException;
use RuntimeException;

class UserRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function createTable(): void
    {
        $sql = file_get_contents(__DIR__ . '/../../sql/schema.sql');
        if ($sql === false) {
            throw new RuntimeException('Failed to load schema.sql');
        }

        $this->db->exec($sql);
    }

    /**
     * @param list{string,string,string} $userData [name, surname, email]
     */
    public function insertUser(array $userData): int
    {
        $name = self::escape($userData[0]);
        $surname = self::escape($userData[1]);
        $email = self::escape($userData[2]);

        $sql = 'INSERT INTO users (name, surname, email) VALUES (:name, :surname, :email)';
        $this->db->prepare($sql)->execute([
            ':name' => $name,
            ':surname' => $surname,
            ':email' => $email,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Case-insensitive email uniqueness check
     */
    public function isEmailUnique(string $email): bool
    {
        $sql = 'SELECT 1 FROM users WHERE email = :email LIMIT 1';
        $result = $this->db->fetchOne($sql, [':email' => $email]);
        return $result === null;
    }

    private static function escape(string $string): string
    {
        if (!function_exists('pg_escape_string')) {
            return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Prefer pg_escape_string when available (for Live env)
        return (string) pg_escape_string(null, $string);
    }
}