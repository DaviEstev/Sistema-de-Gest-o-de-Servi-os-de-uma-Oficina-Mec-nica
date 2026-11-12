<?php

session_start();

require_once __DIR__ . '/database.php';

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']['id']);
}

function requireAuth(): void
{
    if (!isLoggedIn()) {
        redirect('index.php');
    }
}

function requireRole(array $roles): void
{
    requireAuth();

    $user = currentUser();
    if (!$user || !in_array($user['role'], $roles, true)) {
        redirect('dashboard.php');
    }
}
