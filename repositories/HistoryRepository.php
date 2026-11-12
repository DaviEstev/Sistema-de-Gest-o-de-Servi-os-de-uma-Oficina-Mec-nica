<?php

class HistoryRepository
{
    public static function record(int $userId, int $orderId, string $operation): bool
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('INSERT INTO service_order_history (order_id, user_id, operation, created_at) VALUES (:order_id, :user_id, :operation, NOW())');

        return $stmt->execute([
            ':order_id' => $orderId,
            ':user_id' => $userId,
            ':operation' => $operation,
        ]);
    }

    public static function listByOrder(int $orderId): array
    {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT h.*, u.name AS user_name FROM service_order_history h INNER JOIN users u ON u.id = h.user_id WHERE h.order_id = :order_id ORDER BY h.created_at DESC');
        $stmt->execute([':order_id' => $orderId]);

        return $stmt->fetchAll();
    }
}
