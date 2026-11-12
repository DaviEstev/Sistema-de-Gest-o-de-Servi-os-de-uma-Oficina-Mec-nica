<?php

require_once __DIR__ . '/../repositories/ClientRepository.php';

class ClientController
{
    public function store(array $data): array
    {
        if (empty(trim((string) ($data['name'] ?? '')))) {
            return ['success' => false, 'message' => 'Informe o nome do cliente.'];
        }

        $id = ClientRepository::create([
            'name' => trim((string) $data['name']),
            'document' => trim((string) ($data['document'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
            'address' => trim((string) ($data['address'] ?? '')),
        ]);

        return ['success' => true, 'message' => 'Cliente cadastrado com sucesso.', 'id' => $id];
    }
}
