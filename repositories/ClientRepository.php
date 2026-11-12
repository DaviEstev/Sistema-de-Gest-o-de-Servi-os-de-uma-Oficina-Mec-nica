<?php

class ClientRepository
{
    public static function listAll(): array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query('SELECT * FROM clients ORDER BY name ASC');

        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('INSERT INTO clients (name, document, phone, email, address, created_at, updated_at) VALUES (:name, :document, :phone, :email, :address, NOW(), NOW())');
        $stmt->execute([
            ':name' => $data['name'],
            ':document' => $data['document'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
            ':address' => $data['address'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('UPDATE clients SET name = :name, document = :document, phone = :phone, email = :email, address = :address, updated_at = NOW() WHERE id = :id');

        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':document' => $data['document'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
            ':address' => $data['address'] ?? null,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('DELETE FROM clients WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }
}
