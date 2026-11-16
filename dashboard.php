<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/repositories/ClientRepository.php';
require_once __DIR__ . '/repositories/VehicleRepository.php';
require_once __DIR__ . '/repositories/ServiceOrderRepository.php';

requireAuth();
$user = currentUser();
$clients = ClientRepository::listAll();
$vehicles = VehicleRepository::listAll();
$dashboardFilters = ['client_name' => ''];
if ($user['role'] === 'mechanic') {
    $dashboardFilters['mechanic_id'] = (int) $user['id'];
}
$orders = ServiceOrderRepository::listFiltered($dashboardFilters, 1, 5);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Oficina Mecânica</title>
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
        <section class="card">
            <h2>Bem-vindo, <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p>Perfil: <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></p>
        </section>

        <section class="stats">
            <div class="stat-card">
                <span class="stat-label">Clientes</span>
                <strong><?php echo count($clients); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Veículos</span>
                <strong><?php echo count($vehicles); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Ordens</span>
                <strong><?php echo count($orders); ?></strong>
            </div>
        </section>

        <section class="card">
            <h3>Próximos atendimentos</h3>
            <table>
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Data</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) === 0): ?>
                        <tr><td colspan="5">Nenhuma ordem cadastrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo (int) $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['client_name'] ?? 'Cliente', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(($order['brand'] ?? '') . ' ' . ($order['model'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($order['scheduled_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($order['status'] ?? 'agendado', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
