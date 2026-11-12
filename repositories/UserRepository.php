<?php

class UserRepository
{
    public static function findByUsername(string $username): ?array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);

        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function listAll(): array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query('SELECT * FROM users ORDER BY name ASC');

        return $stmt->fetchAll();
    }
}
