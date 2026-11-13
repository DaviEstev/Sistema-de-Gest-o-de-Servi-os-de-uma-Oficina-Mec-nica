<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/repositories/ClientRepository.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRole(['admin']);
    $name = trim((string) ($_POST['name'] ?? ''));

    if ($name === '') {
        $_SESSION['flash_message'] = 'Informe o nome do cliente.';
        $_SESSION['flash_type'] = 'error';
    } else {
        ClientRepository::create([
            'name' => $name,
            'document' => trim((string) ($_POST['document'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
        ]);
        $_SESSION['flash_message'] = 'Cliente cadastrado com sucesso.';
        $_SESSION['flash_type'] = 'success';
    }

    redirect('clientes.php');
}

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
    <title>Clientes - Oficina Mecânica</title>
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
                <h3>Cadastrar cliente</h3>
                <form method="post" class="grid-form">
                    <div class="field"><label>Nome</label><input type="text" name="name" required></div>
                    <div class="field"><label>Documento</label><input type="text" name="document"></div>
                    <div class="field"><label>Telefone</label><input type="text" name="phone"></div>
                    <div class="field"><label>Email</label><input type="email" name="email"></div>
                    <div class="field full"><label>Endereço</label><input type="text" name="address"></div>
                    <div class="actions"><button class="btn btn-primary" type="submit">Salvar</button></div>
                </form>
            </section>
        <?php endif; ?>

        <section class="card">
            <h3>Clientes cadastrados</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Documento</th>
                        <th>Telefone</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clients) === 0): ?>
                        <tr><td colspan="4">Nenhum cliente cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($client['document'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($client['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($client['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
