<?php

class HistoryEntry
{
    public int $id;
    public int $orderId;
    public int $userId;
    public string $operation;
    public ?string $createdAt;

    public static function fromArray(array $row): self
    {
        $entry = new self();
        $entry->id = (int) ($row['id'] ?? 0);
        $entry->orderId = (int) ($row['order_id'] ?? 0);
        $entry->userId = (int) ($row['user_id'] ?? 0);
        $entry->operation = (string) ($row['operation'] ?? '');
        $entry->createdAt = $row['created_at'] ?? null;

        return $entry;
    }
}
