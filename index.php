<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/repositories/UserRepository.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $user = UserRepository::findByUsername($username);

    if (!$user || $password !== $user['password']) {
        $_SESSION['flash_error'] = 'Credenciais inválidas.';
        redirect('index.php');
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'username' => $user['username'],
        'role' => $user['role'],
    ];
    unset($_SESSION['flash_error']);
    redirect('dashboard.php');
}

$errorMessage = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oficina Mecânica - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-box">
        <h1>Oficina Mecânica</h1>
        <p class="subtitle">Sistema de gestão de serviços</p>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label for="username">Usuário</label>
                <input id="username" type="text" name="username" required>
            </div>

            <div class="field">
                <label for="password">Senha</label>
                <input id="password" type="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </main>
</body>
</html>
