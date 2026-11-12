<?php

require_once __DIR__ . '/../services/ServiceOrderService.php';

class ServiceOrderController
{
    public function store(array $data, int $actorId): array
    {
        $service = new ServiceOrderService();

        return $service->createOrder($data, $actorId);
    }

    public function update(int $orderId, array $data, int $actorId): array
    {
        $service = new ServiceOrderService();

        return $service->updateOrder($orderId, $data, $actorId);
    }

    public function delete(int $orderId, int $actorId, string $actorRole): array
    {
        $service = new ServiceOrderService();

        return $service->deleteOrder($orderId, $actorId, $actorRole);
    }
}
