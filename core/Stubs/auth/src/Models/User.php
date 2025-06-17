<?php

namespace App\Models;

use Medoo\Medoo;

class User
{
    private Medoo $db;
    protected string $table = 'usuarios';

    public function __construct(Medoo $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        $user = $this->db->get($this->table, '*', ['email' => $email]);
        return $user ?: null;
    }

    public function create(array $data): bool
    {
        return $this->db->insert($this->table, $data)->rowCount() > 0;
    }
}
