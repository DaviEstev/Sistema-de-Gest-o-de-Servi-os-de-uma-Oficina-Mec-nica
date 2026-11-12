<?php

class AuthorizationMiddleware
{
    public static function requireRole(array $roles): void
    {
        requireAuth();
        $user = currentUser();

        if (!$user || !in_array($user['role'], $roles, true)) {
            $_SESSION['flash_error'] = 'Acesso não autorizado.';
            redirect('dashboard.php');
        }
    }
}
