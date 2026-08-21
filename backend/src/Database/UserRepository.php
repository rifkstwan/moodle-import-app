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
        $sql = 'INSERT INTO users (name, surname, email) VALUES (:name, :surname, :email) RETURNING id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $userData[0],
            ':surname' => $userData[1],
            ':email' => $userData[2],
        ]);

        $id = $stmt->fetchColumn();
        return (int) $id;
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
}