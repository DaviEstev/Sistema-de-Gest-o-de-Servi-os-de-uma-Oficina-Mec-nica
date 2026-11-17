<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/repositories/ClientRepository.php';
require_once __DIR__ . '/repositories/VehicleRepository.php';
require_once __DIR__ . '/repositories/ServiceOrderRepository.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/services/ServiceOrderService.php';

requireAuth();
$user = currentUser();
$service = new ServiceOrderService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'delete') {
        requireRole(['admin']);
        $result = $service->deleteOrder((int) ($_POST['id'] ?? 0), (int) $user['id'], $user['role']);
    } elseif ($action === 'update') {
        requireRole(['admin', 'mechanic']);
        $existingOrder = ServiceOrderRepository::findById((int) ($_POST['id'] ?? 0));
        if ($user['role'] === 'mechanic' && (!$existingOrder || (int) $existingOrder['mechanic_id'] !== (int) $user['id'] || (int) ($_POST['mechanic_id'] ?? 0) !== (int) $user['id'])) {
            $result = ['success' => false, 'message' => 'Você só pode atualizar ordens atribuídas a si mesmo.'];
        } else {
            $result = $service->updateOrder((int) ($_POST['id'] ?? 0), $_POST, (int) $user['id']);
        }
    } else {
        requireRole(['admin', 'mechanic']);
        if ($user['role'] === 'mechanic' && (int) ($_POST['mechanic_id'] ?? 0) !== (int) $user['id']) {
            $result = ['success' => false, 'message' => 'Você só pode criar ordens para si mesmo.'];
        } else {
            $result = $service->createOrder($_POST, (int) $user['id']);
        }
    }
    $_SESSION['flash_message'] = $result['message'];
    $_SESSION['flash_type'] = $result['success'] ? 'success' : 'error';
    $redirectUrl = 'ordens_servico.php';
    if (($action === 'update' || $action === 'create') && !$result['success'] && $action === 'update') {
        $redirectUrl .= '?edit=' . (int) ($_POST['id'] ?? 0);
    }
    redirect($redirectUrl);
}

$filterClient = trim((string) ($_GET['client_name'] ?? ''));
$filterDate = trim((string) ($_GET['date'] ?? ''));
$filters = ['client_name' => $filterClient, 'date' => $filterDate];
if ($user['role'] === 'mechanic') $filters['mechanic_id'] = (int) $user['id'];
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$total = ServiceOrderRepository::countFiltered($filters);
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$orders = ServiceOrderRepository::listFiltered($filters, $page, $perPage);
$clients = ClientRepository::listAll();
$vehicles = VehicleRepository::listAll();
$mechanics = array_values(array_filter(UserRepository::listAll(), static fn(array $item): bool => $item['role'] === 'mechanic'));
$editingOrder = null;
if (isset($_GET['edit'])) {
    $editingOrder = ServiceOrderRepository::findById((int) $_GET['edit']);
    if ($editingOrder && $user['role'] === 'mechanic' && (int) $editingOrder['mechanic_id'] !== (int) $user['id']) $editingOrder = null;
}
$message = $_SESSION['flash_message'] ?? '';
$type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$toInputDate = static fn($value): string => str_replace(' ', 'T', substr((string) $value, 0, 16));
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
    <?php if ($user['role'] === 'admin'): ?>
        <a href="usuarios.php">Usuários</a>
        <?php endif; ?>
        <a href="logout.php">Sair</a>
</nav>
</header>
<main class="container">
    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo $type === 'error' ? 'error' : 'success'; ?>">
            <?php echo $escape($message); ?>
        </div><?php endif; ?>

    <section class="card">
        <h3>
            <?php echo $editingOrder ? 'Editar ordem de serviço #' . (int) $editingOrder['id'] : 'Nova ordem de serviço'; ?>
        </h3>
        <form method="post" class="grid-form">
            <input type="hidden" name="action" value="<?php echo $editingOrder ? 'update' : 'create'; ?>">
            <?php if ($editingOrder): ?>
                <input type="hidden" name="id" value="<?php echo (int) $editingOrder['id']; ?>">
                <?php endif; ?>
            <div class="field">
                <label>Cliente</label>
                <select name="client_id" required>
                    <option value="">Selecione</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo (int) $client['id']; ?>" <?php echo $editingOrder && (int)$editingOrder['client_id']===(int)$client['id']?'selected':''; ?>><?php echo $escape($client['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
            <div class="field">
                <label>Veículo</label>
                <select name="vehicle_id" required>
                    <option value="">Selecione</option>
                <?php foreach ($vehicles as $vehicle): ?>
                    <option value="<?php echo (int) $vehicle['id']; ?>" <?php echo $editingOrder && (int)$editingOrder['vehicle_id']===(int)$vehicle['id']?'selected':''; ?>>
                        <?php echo $escape($vehicle['brand'].' '.$vehicle['model'].' - '.$vehicle['plate']); ?>
                    </option>
                    <?php endforeach; ?>
            </select>
        </div>
            <div class="field">
                <label>Mecânico</label>
                <select name="mechanic_id" required>
                <?php foreach ($mechanics as $mechanic): ?>
                    <option value="<?php echo (int) $mechanic['id']; ?>" <?php echo ($editingOrder && (int)$editingOrder['mechanic_id']===(int)$mechanic['id']) || (!$editingOrder && $user['role']==='mechanic' && (int)$user['id']===(int)$mechanic['id']?'selected':''); ?> <?php echo $user['role']==='mechanic' && (int)$user['id']!==(int)$mechanic['id']?'disabled':''; ?>><?php echo $escape($mechanic['name']); ?></option><?php endforeach; ?>
            </select>
        </div>
            <div class="field">
                <label>Início</label>
                <input type="datetime-local" name="scheduled_at" value="<?php echo $editingOrder ? $escape($toInputDate($editingOrder['scheduled_at'])) : ''; ?>" required>
            </div>
            <div class="field">
                <label>Fim</label>
                <input type="datetime-local" name="scheduled_end" value="<?php echo $editingOrder ? $escape($toInputDate($editingOrder['scheduled_end'])) : ''; ?>" required>
            </div>
            <div class="field">
                <label>Situação</label>
                <select name="status">
                <?php foreach (['agendado'=>'Agendado','em_andamento'=>'Em andamento','concluido'=>'Concluído'] as $key=>$label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $editingOrder && $editingOrder['status']===$key?'selected':''; ?>><?php echo $label; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
            <div class="field full">
                <label>Descrição do serviço</label>
                <textarea name="description" rows="3" required><?php echo $editingOrder ? $escape($editingOrder['description']) : ''; ?>
            </textarea>
        </div>
            <div class="actions">
                <button class="btn btn-primary" type="submit">
                    <?php echo $editingOrder ? 'Salvar alterações' : 'Cadastrar ordem'; ?>
                </button>
                <?php if ($editingOrder): ?>
                    <a class="btn btn-secondary" href="ordens_servico.php">Cancelar edição</a>
                    <?php endif; ?>
                </div>
        </form>
    </section>

    <section class="card"><h3>Filtros</h3>
    <form method="get" class="grid-form compact">
        <div class="field">
            <label>Nome do cliente</label>
            <input type="text" name="client_name" value="<?php echo $escape($filterClient); ?>">
        </div>
        <div class="field">
            <label>Data do atendimento</label>
            <input type="date" name="date" value="<?php echo $escape($filterDate); ?>">
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a class="btn btn-secondary" href="ordens_servico.php">Limpar</a>
        </div>
    </form>
</section>

    <section class="card">
        <h3>Ordens de serviço</h3>
        <p class="muted">Total encontrado: <?php echo $total; ?></p>
        <div class="table-responsive">
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
        <?php if (!$orders): ?>
            <tr>
                <td colspan="8">Nenhuma ordem encontrada.</td>
            </tr>
            <?php else: foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo (int)$order['id']; ?></td>
                <td><?php echo $escape($order['client_name']); ?>
            </td>
            <td><?php echo $escape($order['brand'].' '.$order['model'].' - '.$order['plate']); ?></td>
                <td><?php echo $escape($order['description']); ?></td>
                <td><?php echo $escape($order['mechanic_name']); ?></td>
                <td><?php echo $escape($order['scheduled_at'].' até '.$order['scheduled_end']); ?></td>
                <td><?php echo $escape($order['status']); ?></td>
                <td class="action-buttons">
                <?php if ($user['role']==='admin' || (int)$order['mechanic_id']===(int)$user['id']): ?>
                    <a class="btn btn-small btn-primary" href="ordens_servico.php?edit=<?php echo (int)$order['id']; ?>">Editar</a>
                    <?php endif; ?>
                <?php if ($user['role']==='admin'): ?><form method="post" onsubmit="return confirm('Deseja realmente excluir esta ordem de serviço?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-small">Excluir</button>
                </form><?php endif; ?>
            </td>
        </tr>
        <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
        <div class="pagination"><?php for ($n=1; $n<=$totalPages; $n++): ?>
            <a class="page-link <?php echo $n===$page?'active':''; ?>" href="?client_name=<?php echo urlencode($filterClient); ?>&date=<?php echo urlencode($filterDate); ?>&page=<?php echo $n; ?>">
                <?php echo $n; ?>
            </a><?php endfor; ?>
        </div>
    </section>
</main>
</body>
</html>
