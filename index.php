<?php
require_once __DIR__ . '/config/bootstrap.php';

if (isLoggedIn()) {
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

        <form method="post" action="controllers/AuthController.php?action=login">
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
