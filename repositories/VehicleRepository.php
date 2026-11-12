<?php

class VehicleRepository
{
    public static function listAll(): array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query('SELECT * FROM vehicles ORDER BY brand, model ASC');

        return $stmt->fetchAll();
    }

    public static function listByClient(int $clientId): array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT * FROM vehicles WHERE client_id = :client_id ORDER BY model ASC');
        $stmt->execute([':client_id' => $clientId]);

        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT * FROM vehicles WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('INSERT INTO vehicles (client_id, brand, model, plate, year, color, created_at, updated_at) VALUES (:client_id, :brand, :model, :plate, :year, :color, NOW(), NOW())');
        $stmt->execute([
            ':client_id' => $data['client_id'],
            ':brand' => $data['brand'],
            ':model' => $data['model'],
            ':plate' => strtoupper($data['plate'] ?? ''),
            ':year' => $data['year'],
            ':color' => $data['color'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('UPDATE vehicles SET client_id = :client_id, brand = :brand, model = :model, plate = :plate, year = :year, color = :color, updated_at = NOW() WHERE id = :id');

        return $stmt->execute([
            ':id' => $id,
            ':client_id' => $data['client_id'],
            ':brand' => $data['brand'],
            ':model' => $data['model'],
            ':plate' => strtoupper($data['plate'] ?? ''),
            ':year' => $data['year'],
            ':color' => $data['color'],
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('DELETE FROM vehicles WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }
}
