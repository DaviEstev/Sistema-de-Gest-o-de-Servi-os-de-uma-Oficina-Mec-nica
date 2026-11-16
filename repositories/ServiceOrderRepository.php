<?php

class ServiceOrderRepository
{
    private static function buildFilters(array $filters, array &$params): array
    {
        $where = ['1 = 1'];
        if (!empty($filters['client_name'])) {
            $where[] = 'c.name LIKE :client_name';
            $params[':client_name'] = '%' . trim($filters['client_name']) . '%';
        }
        if (!empty($filters['date'])) {
            $where[] = 'so.scheduled_at >= :date_start AND so.scheduled_at < :date_end';
            $params[':date_start'] = $filters['date'] . ' 00:00:00';
            $params[':date_end'] = date('Y-m-d', strtotime($filters['date'] . ' +1 day')) . ' 00:00:00';
        }
        if (!empty($filters['mechanic_id'])) {
            $where[] = 'so.mechanic_id = :mechanic_id';
            $params[':mechanic_id'] = (int) $filters['mechanic_id'];
        }
        return $where;
    }

    public static function listFiltered(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $pdo = getDatabaseConnection();
        $params = [];
        $where = self::buildFilters($filters, $params);
        $sql = 'SELECT so.*, c.name AS client_name, v.brand, v.model, v.plate, u.name AS mechanic_name
                FROM service_orders so
                INNER JOIN clients c ON c.id = so.client_id
                INNER JOIN vehicles v ON v.id = so.vehicle_id
                INNER JOIN users u ON u.id = so.mechanic_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY so.scheduled_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $perPage), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, ($page - 1) * $perPage), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countFiltered(array $filters = []): int
    {
        $pdo = getDatabaseConnection();
        $params = [];
        $where = self::buildFilters($filters, $params);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM service_orders so INNER JOIN clients c ON c.id = so.client_id WHERE ' . implode(' AND ', $where));
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function findById(int $id): ?array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT so.*, c.name AS client_name, v.brand, v.model, v.plate, u.name AS mechanic_name
            FROM service_orders so INNER JOIN clients c ON c.id = so.client_id
            INNER JOIN vehicles v ON v.id = so.vehicle_id INNER JOIN users u ON u.id = so.mechanic_id
            WHERE so.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('INSERT INTO service_orders (client_id, vehicle_id, mechanic_id, description, scheduled_at, scheduled_end, status, created_at, updated_at)
            VALUES (:client_id, :vehicle_id, :mechanic_id, :description, :scheduled_at, :scheduled_end, :status, NOW(), NOW())');
        $stmt->execute(self::orderParams($data));
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = getDatabaseConnection();
        $params = self::orderParams($data);
        $params[':id'] = $id;
        $stmt = $pdo->prepare('UPDATE service_orders SET client_id = :client_id, vehicle_id = :vehicle_id, mechanic_id = :mechanic_id,
            description = :description, scheduled_at = :scheduled_at, scheduled_end = :scheduled_end, status = :status, updated_at = NOW() WHERE id = :id');
        return $stmt->execute($params);
    }

    private static function orderParams(array $data): array
    {
        return [
            ':client_id' => (int) ($data['client_id'] ?? 0),
            ':vehicle_id' => (int) ($data['vehicle_id'] ?? 0),
            ':mechanic_id' => (int) ($data['mechanic_id'] ?? 0),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':scheduled_at' => str_replace('T', ' ', (string) ($data['scheduled_at'] ?? '')),
            ':scheduled_end' => str_replace('T', ' ', (string) ($data['scheduled_end'] ?? '')),
            ':status' => $data['status'] ?? 'agendado',
        ];
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
        $sql = 'SELECT COUNT(*) FROM service_orders WHERE mechanic_id = :mechanic_id AND scheduled_at < :scheduled_end AND scheduled_end > :scheduled_at';
        $params = [':mechanic_id' => $mechanicId, ':scheduled_at' => str_replace('T', ' ', $scheduledAt), ':scheduled_end' => str_replace('T', ' ', $scheduledEnd)];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
