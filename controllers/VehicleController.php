<?php

require_once __DIR__ . '/../repositories/VehicleRepository.php';

class VehicleController
{
    public function store(array $data): array
    {
        if (empty((int) ($data['client_id'] ?? 0))) {
            return ['success' => false, 'message' => 'Selecione um cliente válido.'];
        }

        $plate = strtoupper(trim((string) ($data['plate'] ?? '')));
        $id = VehicleRepository::create([
            'client_id' => (int) $data['client_id'],
            'brand' => trim((string) ($data['brand'] ?? '')),
            'model' => trim((string) ($data['model'] ?? '')),
            'plate' => $plate,
            'year' => trim((string) ($data['year'] ?? '')),
            'color' => trim((string) ($data['color'] ?? '')),
        ]);

        return ['success' => true, 'message' => 'Veículo cadastrado com sucesso.', 'id' => $id];
    }
}
