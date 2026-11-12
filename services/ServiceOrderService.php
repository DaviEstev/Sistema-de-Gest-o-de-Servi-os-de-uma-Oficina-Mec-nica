<?php

require_once __DIR__ . '/../repositories/ServiceOrderRepository.php';
require_once __DIR__ . '/../repositories/HistoryRepository.php';

class ServiceOrderService
{
    public function createOrder(array $data, int $actorId): array
    {
        $start = trim((string) ($data['scheduled_at'] ?? ''));
        $end = trim((string) ($data['scheduled_end'] ?? ''));
        $mechanicId = (int) ($data['mechanic_id'] ?? 0);

        if ($start === '' || $end === '' || $mechanicId <= 0) {
            return ['success' => false, 'message' => 'Dados incompletos para agendar a ordem de serviço.'];
        }

        if (ServiceOrderRepository::hasConflict($mechanicId, $start, $end)) {
            return ['success' => false, 'message' => 'Agendamento não permitido: o mecânico selecionado já possui uma ordem de serviço nesse horário. Selecione outro horário ou mecânico.'];
        }

        $orderId = ServiceOrderRepository::create($data);
        HistoryRepository::record($actorId, $orderId, 'Cadastro de ordem de serviço');

        return ['success' => true, 'message' => 'Ordem de serviço cadastrada com sucesso.'];
    }

    public function updateOrder(int $orderId, array $data, int $actorId): array
    {
        $start = trim((string) ($data['scheduled_at'] ?? ''));
        $end = trim((string) ($data['scheduled_end'] ?? ''));
        $mechanicId = (int) ($data['mechanic_id'] ?? 0);

        if ($start === '' || $end === '' || $mechanicId <= 0) {
            return ['success' => false, 'message' => 'Dados incompletos para atualizar a ordem de serviço.'];
        }

        if (ServiceOrderRepository::hasConflict($mechanicId, $start, $end, $orderId)) {
            return ['success' => false, 'message' => 'Agendamento não permitido: o mecânico selecionado já possui uma ordem de serviço nesse horário. Selecione outro horário ou mecânico.'];
        }

        $updated = ServiceOrderRepository::update($orderId, $data);
        if (!$updated) {
            return ['success' => false, 'message' => 'Não foi possível atualizar a ordem de serviço.'];
        }

        HistoryRepository::record($actorId, $orderId, 'Atualização de ordem de serviço');

        return ['success' => true, 'message' => 'Ordem de serviço atualizada com sucesso.'];
    }

    public function deleteOrder(int $orderId, int $actorId, string $actorRole): array
    {
        if ($actorRole !== 'admin') {
            return ['success' => false, 'message' => 'Usuário sem permissão para excluir ordens de serviço.'];
        }

        $deleted = ServiceOrderRepository::delete($orderId);
        if (!$deleted) {
            return ['success' => false, 'message' => 'Não foi possível excluir a ordem de serviço.'];
        }

        HistoryRepository::record($actorId, $orderId, 'Exclusão de ordem de serviço');

        return ['success' => true, 'message' => 'Ordem de serviço excluída com sucesso.'];
    }
}
