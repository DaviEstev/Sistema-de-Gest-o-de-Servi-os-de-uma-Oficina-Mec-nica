<?php

class Vehicle
{
    public int $id;
    public int $clientId;
    public string $brand;
    public string $model;
    public string $plate;
    public string $year;
    public string $color;

    public static function fromArray(array $row): self
    {
        $vehicle = new self();
        $vehicle->id = (int) ($row['id'] ?? 0);
        $vehicle->clientId = (int) ($row['client_id'] ?? 0);
        $vehicle->brand = (string) ($row['brand'] ?? '');
        $vehicle->model = (string) ($row['model'] ?? '');
        $vehicle->plate = (string) ($row['plate'] ?? '');
        $vehicle->year = (string) ($row['year'] ?? '');
        $vehicle->color = (string) ($row['color'] ?? '');

        return $vehicle;
    }
}
