<?php

class User
{
    public int $id;
    public string $name;
    public string $username;
    public string $role;
    public ?string $passwordHash;

    public static function fromArray(array $row): self
    {
        $user = new self();
        $user->id = (int) ($row['id'] ?? 0);
        $user->name = (string) ($row['name'] ?? '');
        $user->username = (string) ($row['username'] ?? '');
        $user->role = (string) ($row['role'] ?? 'mechanic');
        $user->passwordHash = $row['password_hash'] ?? null;

        return $user;
    }
}
