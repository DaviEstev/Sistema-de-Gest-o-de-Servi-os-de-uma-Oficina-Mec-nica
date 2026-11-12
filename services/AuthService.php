<?php

require_once __DIR__ . '/../repositories/UserRepository.php';

class AuthService
{
    public function login(string $username, string $password): ?array
    {
        $user = UserRepository::findByUsername(trim($username));
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
    }
}
