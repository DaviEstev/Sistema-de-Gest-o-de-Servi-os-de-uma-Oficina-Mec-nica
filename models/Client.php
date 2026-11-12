<?php

class Client
{
    public int $id;
    public string $name;
    public string $document;
    public string $phone;
    public string $email;
    public ?string $address;

    public static function fromArray(array $row): self
    {
        $client = new self();
        $client->id = (int) ($row['id'] ?? 0);
        $client->name = (string) ($row['name'] ?? '');
        $client->document = (string) ($row['document'] ?? '');
        $client->phone = (string) ($row['phone'] ?? '');
        $client->email = (string) ($row['email'] ?? '');
        $client->address = $row['address'] ?? null;

        return $client;
    }
}
