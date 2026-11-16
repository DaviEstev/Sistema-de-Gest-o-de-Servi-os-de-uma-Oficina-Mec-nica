<?php

require_once __DIR__ . '/../repositories/ServiceOrderRepository.php';
require_once __DIR__ . '/../repositories/HistoryRepository.php';
require_once __DIR__ . '/../repositories/VehicleRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

class ServiceOrderService
{
    private function validate(array $data): ?string
    {
        $start = trim((string) ($data['scheduled_at'] ?? ''));
        $end = trim((string) ($data['scheduled_end'] ?? ''));
        if ((int) ($data['client_id'] ?? 0) <= 0 || (int) ($data['vehicle_id'] ?? 0) <= 0 ||
            (int) ($data['mechanic_id'] ?? 0) <= 0 || trim((string) ($data['description'] ?? '')) === '' || $start === '' || $end === '') {
            return 'Preencha todos os campos obrigatórios da ordem de serviço.';
        }
        if (strtotime(str_replace('T', ' ', $end)) <= strtotime(str_replace('T', ' ', $start))) {
            return 'O horário de término deve ser posterior ao horário de início.';
        }
        $vehicle = VehicleRepository::findById((int) $data['vehicle_id']);
        if (!$vehicle || (int) $vehicle['client_id'] !== (int) $data['client_id']) {
            return 'O veículo selecionado não pertence ao cliente informado.';
        }
        $mechanic = UserRepository::findById((int) $data['mechanic_id']);
        if (!$mechanic || $mechanic['role'] !== 'mechanic') {
            return 'Selecione um mecânico válido.';
        }
        return null;
    }

    public function createOrder(array $data, int $actorId): array
    {
        $error = $this->validate($data);
        if ($error) return ['success' => false, 'message' => $error];
        if (ServiceOrderRepository::hasConflict((int) $data['mechanic_id'], $data['scheduled_at'], $data['scheduled_end'])) {
            return ['success' => false, 'message' => 'Agendamento não permitido: o mecânico selecionado já possui uma ordem de serviço nesse horário. Selecione outro horário ou mecânico.'];
        }
        try {
            $orderId = ServiceOrderRepository::create($data);
            HistoryRepository::record($actorId, $orderId, 'Cadastro de ordem de serviço');
            return ['success' => true, 'message' => 'Ordem de serviço cadastrada com sucesso.'];
        } catch (Throwable $exception) {
            return ['success' => false, 'message' => 'Não foi possível cadastrar a ordem de serviço. Verifique os dados e tente novamente.'];
        }
    }

    public function updateOrder(int $orderId, array $data, int $actorId): array
    {
        if (!ServiceOrderRepository::findById($orderId)) return ['success' => false, 'message' => 'Ordem de serviço não encontrada.'];
        $error = $this->validate($data);
        if ($error) return ['success' => false, 'message' => $error];
        if (ServiceOrderRepository::hasConflict((int) $data['mechanic_id'], $data['scheduled_at'], $data['scheduled_end'], $orderId)) {
            return ['success' => false, 'message' => 'Agendamento não permitido: o mecânico selecionado já possui uma ordem de serviço nesse horário. Selecione outro horário ou mecânico.'];
        }
        try {
            ServiceOrderRepository::update($orderId, $data);
            HistoryRepository::record($actorId, $orderId, 'Atualização de ordem de serviço');
            return ['success' => true, 'message' => 'Ordem de serviço atualizada com sucesso.'];
        } catch (Throwable $exception) {
            return ['success' => false, 'message' => 'Não foi possível atualizar a ordem de serviço.'];
        }
    }

    public function deleteOrder(int $orderId, int $actorId, string $actorRole): array
    {
        if ($actorRole !== 'admin') return ['success' => false, 'message' => 'Usuário sem permissão para excluir ordens de serviço.'];
        if (!ServiceOrderRepository::findById($orderId)) return ['success' => false, 'message' => 'Ordem de serviço não encontrada.'];
        try {
            // O histórico de exclusão é registrado antes da remoção; a FK usa ON DELETE SET NULL.
            HistoryRepository::record($actorId, $orderId, 'Exclusão de ordem de serviço');
            ServiceOrderRepository::delete($orderId);
            return ['success' => true, 'message' => 'Ordem de serviço excluída com sucesso.'];
        } catch (Throwable $exception) {
            return ['success' => false, 'message' => 'Não foi possível excluir a ordem de serviço.'];
        }
    }
}
