<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/repositories/ClientRepository.php';
require_once __DIR__ . '/repositories/VehicleRepository.php';
require_once __DIR__ . '/repositories/ServiceOrderRepository.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/controllers/ServiceOrderController.php';

requireAuth();

$user = currentUser();
$clients = ClientRepository::listAll();
$vehicles = VehicleRepository::listAll();
$mechanics = UserRepository::listAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $result = ['success' => false, 'message' => 'Ação inválida.'];

    if ($action === 'delete') {
        requireRole(['admin']);
        $result = (new ServiceOrderController())->delete((int) ($_POST['id'] ?? 0), (int) $user['id'], $user['role']);
    } elseif ($action === 'update') {
        requireRole(['admin', 'mechanic']);
        $orderId = (int) ($_POST['id'] ?? 0);
        $payload = $_POST;
        if ($user['role'] === 'mechanic' && (int) ($payload['mechanic_id'] ?? 0) !== (int) $user['id']) {
            $_SESSION['flash_message'] = 'Você só pode atualizar ordens para si mesmo.';
            $_SESSION['flash_type'] = 'error';
            redirect('ordens_servico.php');
        }
        $result = (new ServiceOrderController())->update($orderId, $payload, (int) $user['id']);
    } else {
        requireRole(['admin', 'mechanic']);
        $payload = $_POST;
        if ($user['role'] === 'mechanic' && (int) ($payload['mechanic_id'] ?? 0) !== (int) $user['id']) {
            $_SESSION['flash_message'] = 'Você só pode criar ordens para si mesmo.';
            $_SESSION['flash_type'] = 'error';
            redirect('ordens_servico.php');
        }
        $result = (new ServiceOrderController())->store($payload, (int) $user['id']);
    }

    $_SESSION['flash_message'] = $result['message'];
    $_SESSION['flash_type'] = $result['success'] ? 'success' : 'error';
    redirect('ordens_servico.php');
}

$filterClient = $_GET['client_name'] ?? '';
$filterDate = $_GET['date'] ?? '';
$filters = ['client_name' => $filterClient, 'date' => $filterDate];
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$total = ServiceOrderRepository::countFiltered($filters);
$orders = ServiceOrderRepository::listFiltered($filters, $page, $perPage);
if ($user['role'] === 'mechanic') {
    $orders = array_values(array_filter($orders, static fn (array $order) => (int) $order['mechanic_id'] === (int) $user['id']));
}
$totalPages = max(1, (int) ceil($total / $perPage));
$message = $_SESSION['flash_message'] ?? '';
$type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordens de Serviço - Oficina Mecânica</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">Oficina Mecânica</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="clientes.php">Clientes</a>
            <a href="veiculos.php">Veículos</a>
            <a href="ordens_servico.php">Ordens de Serviço</a>
            <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="container">
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo $type === 'error' ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <section class="card">
            <h3>Nova ordem de serviço</h3>
            <form method="post" class="grid-form">
                <input type="hidden" name="action" value="create">
                <div class="field">
                    <label>Cliente</label>
                    <select name="client_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo (int) $client['id']; ?>"><?php echo htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Veículo</label>
                    <select name="vehicle_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?php echo (int) $vehicle['id']; ?>"><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . ' - ' . $vehicle['plate'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Mecânico</label>
                    <select name="mechanic_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($mechanics as $mechanic): ?>
                            <?php if (($mechanic['role'] ?? 'mechanic') !== 'admin'): ?>
                                <option value="<?php echo (int) $mechanic['id']; ?>"><?php echo htmlspecialchars($mechanic['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Início</label>
                    <input type="datetime-local" name="scheduled_at" required>
                </div>
                <div class="field">
                    <label>Fim</label>
                    <input type="datetime-local" name="scheduled_end" required>
                </div>
                <div class="field full">
                    <label>Descrição do serviço</label>
                    <textarea name="description" rows="3" required></textarea>
                </div>
                <div class="field">
                    <label>Situação</label>
                    <select name="status">
                        <option value="agendado">Agendado</option>
                        <option value="em_andamento">Em andamento</option>
                        <option value="concluido">Concluído</option>
                    </select>
                </div>
                <div class="actions"><button class="btn btn-primary" type="submit">Salvar</button></div>
            </form>
        </section>

        <section class="card">
            <h3>Filtros</h3>
            <form method="get" class="grid-form compact">
                <div class="field"><label>Cliente</label><input type="text" name="client_name" value="<?php echo htmlspecialchars($filterClient, ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div class="field"><label>Data</label><input type="date" name="date" value="<?php echo htmlspecialchars($filterDate, ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div class="actions"><button type="submit" class="btn btn-primary">Filtrar</button></div>
            </form>
        </section>

        <section class="card">
            <h3>Ordens de serviço</h3>
            <table>
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Descrição</th>
                        <th>Mecânico</th>
                        <th>Data/Hora</th>
                        <th>Situação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) === 0): ?>
                        <tr><td colspan="8">Nenhuma ordem encontrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo (int) $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['client_name'] ?? 'Cliente', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(($order['brand'] ?? '') . ' ' . ($order['model'] ?? '') . ' - ' . ($order['plate'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($order['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($order['mechanic_name'] ?? 'Mecânico', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(($order['scheduled_at'] ?? '') . ' até ' . ($order['scheduled_end'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($order['status'] ?? 'agendado', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <form method="post" action="ordens_servico.php" onsubmit="return confirm('Deseja excluir esta ordem?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                    <a class="page-link <?php echo $pageNumber === $page ? 'active' : ''; ?>" href="ordens_servico.php?client_name=<?php echo urlencode($filterClient); ?>&date=<?php echo urlencode($filterDate); ?>&page=<?php echo (int) $pageNumber; ?>"><?php echo (int) $pageNumber; ?></a>
                <?php endfor; ?>
            </div>
        </section>
    </main>
    <script src="assets/js/app.js"></script>
</body>
</html>
