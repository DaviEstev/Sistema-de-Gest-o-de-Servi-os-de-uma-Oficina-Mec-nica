<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/repositories/ClientRepository.php';
require_once __DIR__ . '/repositories/VehicleRepository.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRole(['admin']);
    $clientId = (int) ($_POST['client_id'] ?? 0);

    if ($clientId <= 0 || ClientRepository::findById($clientId) === null) {
        $_SESSION['flash_message'] = 'Selecione um cliente válido.';
        $_SESSION['flash_type'] = 'error';
    } else {
        VehicleRepository::create([
            'client_id' => $clientId,
            'brand' => trim((string) ($_POST['brand'] ?? '')),
            'model' => trim((string) ($_POST['model'] ?? '')),
            'plate' => strtoupper(trim((string) ($_POST['plate'] ?? ''))),
            'year' => trim((string) ($_POST['year'] ?? '')),
            'color' => trim((string) ($_POST['color'] ?? '')),
        ]);
        $_SESSION['flash_message'] = 'Veículo cadastrado com sucesso.';
        $_SESSION['flash_type'] = 'success';
    }

    redirect('veiculos.php');
}

$vehicles = VehicleRepository::listAll();
$clients = ClientRepository::listAll();
$message = $_SESSION['flash_message'] ?? '';
$type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veículos - Oficina Mecânica</title>
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

        <?php if (currentUser()['role'] === 'admin'): ?>
            <section class="card">
                <h3>Cadastrar veículo</h3>
                <form method="post" class="grid-form">
                    <div class="field">
                        <label>Cliente</label>
                        <select name="client_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo (int) $client['id']; ?>"><?php echo htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label>Marca</label><input type="text" name="brand" required></div>
                    <div class="field"><label>Modelo</label><input type="text" name="model" required></div>
                    <div class="field"><label>Placa</label><input type="text" name="plate" required></div>
                    <div class="field"><label>Ano</label><input type="text" name="year" required></div>
                    <div class="field"><label>Cor</label><input type="text" name="color" required></div>
                    <div class="actions"><button class="btn btn-primary" type="submit">Salvar</button></div>
                </form>
            </section>
        <?php endif; ?>

        <section class="card">
            <h3>Veículos cadastrados</h3>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Placa</th>
                        <th>Ano</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($vehicles) === 0): ?>
                        <tr><td colspan="5">Nenhum veículo cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(($clients[array_search((int) $vehicle['client_id'], array_column($clients, 'id'), true)] ?? ['name' => 'Cliente'])['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['brand'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['model'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['plate'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($vehicle['year'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
