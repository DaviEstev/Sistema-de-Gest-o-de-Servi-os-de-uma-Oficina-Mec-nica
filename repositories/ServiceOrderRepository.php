<?php

class ServiceOrderRepository
{
    public static function listFiltered(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $pdo = getDatabaseConnection();

        $where = ['1 = 1'];
        $params = [];

        if (!empty($filters['client_name'])) {
            $where[] = 'c.name LIKE :client_name';
            $params[':client_name'] = '%' . trim($filters['client_name']) . '%';
        }

        if (!empty($filters['date'])) {
            $where[] = 'DATE(so.scheduled_at) = :date';
            $params[':date'] = $filters['date'];
        }

        $sql = 'SELECT so.*, c.name AS client_name, v.brand, v.model, v.plate, u.name AS mechanic_name
                FROM service_orders so
                INNER JOIN clients c ON c.id = so.client_id
                INNER JOIN vehicles v ON v.id = so.vehicle_id
                INNER JOIN users u ON u.id = so.mechanic_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY so.scheduled_at DESC
                LIMIT :limit OFFSET :offset';

        $params[':limit'] = (int) $perPage;
        $params[':offset'] = (int) (($page - 1) * $perPage);

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
                continue;
            }
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countFiltered(array $filters = []): int
    {
        $pdo = getDatabaseConnection();
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filters['client_name'])) {
            $where[] = 'c.name LIKE :client_name';
            $params[':client_name'] = '%' . trim($filters['client_name']) . '%';
        }

        if (!empty($filters['date'])) {
            $where[] = 'DATE(so.scheduled_at) = :date';
            $params[':date'] = $filters['date'];
        }

        $sql = 'SELECT COUNT(*) AS total FROM service_orders so
                INNER JOIN clients c ON c.id = so.client_id
                WHERE ' . implode(' AND ', $where);

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public static function findById(int $id): ?array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT so.*, c.name AS client_name, v.brand, v.model, v.plate, u.name AS mechanic_name
            FROM service_orders so
            INNER JOIN clients c ON c.id = so.client_id
            INNER JOIN vehicles v ON v.id = so.vehicle_id
            INNER JOIN users u ON u.id = so.mechanic_id
            WHERE so.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('INSERT INTO service_orders (client_id, vehicle_id, mechanic_id, description, scheduled_at, scheduled_end, status, created_at, updated_at)
            VALUES (:client_id, :vehicle_id, :mechanic_id, :description, :scheduled_at, :scheduled_end, :status, NOW(), NOW())');
        $stmt->execute([
            ':client_id' => (int) $data['client_id'],
            ':vehicle_id' => (int) $data['vehicle_id'],
            ':mechanic_id' => (int) $data['mechanic_id'],
            ':description' => $data['description'],
            ':scheduled_at' => $data['scheduled_at'],
            ':scheduled_end' => $data['scheduled_end'],
            ':status' => $data['status'] ?? 'agendado',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('UPDATE service_orders SET client_id = :client_id, vehicle_id = :vehicle_id, mechanic_id = :mechanic_id, description = :description, scheduled_at = :scheduled_at, scheduled_end = :scheduled_end, status = :status, updated_at = NOW() WHERE id = :id');

        return $stmt->execute([
            ':id' => $id,
            ':client_id' => (int) $data['client_id'],
            ':vehicle_id' => (int) $data['vehicle_id'],
            ':mechanic_id' => (int) $data['mechanic_id'],
            ':description' => $data['description'],
            ':scheduled_at' => $data['scheduled_at'],
            ':scheduled_end' => $data['scheduled_end'],
            ':status' => $data['status'] ?? 'agendado',
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('DELETE FROM service_orders WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    public static function hasConflict(int $mechanicId, string $scheduledAt, string $scheduledEnd, ?int $excludeId = null): bool
    {
        $pdo = getDatabaseConnection();
        $sql = 'SELECT COUNT(*) FROM service_orders WHERE mechanic_id = :mechanic_id
                AND scheduled_at < :scheduled_end
                AND scheduled_end > :scheduled_at';

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
        }

        $stmt = $pdo->prepare($sql);
        $params = [
            ':mechanic_id' => $mechanicId,
            ':scheduled_at' => $scheduledAt,
            ':scheduled_end' => $scheduledEnd,
        ];
        if ($excludeId !== null) {
            $params[':exclude_id'] = $excludeId;
        }

        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }
}
