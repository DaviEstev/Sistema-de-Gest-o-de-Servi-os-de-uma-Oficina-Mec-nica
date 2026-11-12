<?php

class ServiceOrder
{
    public int $id;
    public int $clientId;
    public int $vehicleId;
    public int $mechanicId;
    public string $description;
    public string $scheduledAt;
    public string $scheduledEnd;
    public string $status;
    public ?string $createdAt;
    public ?string $updatedAt;

    public static function fromArray(array $row): self
    {
        $order = new self();
        $order->id = (int) ($row['id'] ?? 0);
        $order->clientId = (int) ($row['client_id'] ?? 0);
        $order->vehicleId = (int) ($row['vehicle_id'] ?? 0);
        $order->mechanicId = (int) ($row['mechanic_id'] ?? 0);
        $order->description = (string) ($row['description'] ?? '');
        $order->scheduledAt = (string) ($row['scheduled_at'] ?? '');
        $order->scheduledEnd = (string) ($row['scheduled_end'] ?? '');
        $order->status = (string) ($row['status'] ?? 'agendado');
        $order->createdAt = $row['created_at'] ?? null;
        $order->updatedAt = $row['updated_at'] ?? null;

        return $order;
    }
}
