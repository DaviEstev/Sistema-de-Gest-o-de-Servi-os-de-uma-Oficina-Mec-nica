<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../services/AuthService.php';

class AuthController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('../index.php');
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $service = new AuthService();
        $user = $service->login($username, $password);

        if ($user === null) {
            $_SESSION['flash_error'] = 'Credenciais inválidas.';
            redirect('../index.php');
        }

        $_SESSION['user'] = $user;
        unset($_SESSION['flash_error']);
        redirect('../dashboard.php');
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        redirect('../index.php');
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    $action = $_GET['action'] ?? '';
    $controller = new AuthController();

    if ($action === 'login') {
        $controller->login();
    } elseif ($action === 'logout') {
        $controller->logout();
    } else {
        redirect('../index.php');
    }
}
