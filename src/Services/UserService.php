<?php

namespace Src\Services;

use Medoo\Medoo;

class UserService
{
    protected $db;

    public function __construct()
    {
        $this->db = new Medoo([
            'type' => 'mysql',
            'host' => $_ENV['DB_HOST'],
            'database' => $_ENV['DB_NAME'],
            'username' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'charset' => 'utf8mb4'
        ]);
    }

    public function list()
    {
        return $this->db->select('users', '*');
    }

    public function get($id)
    {
        return $this->db->get('users', '*', ['id' => $id]);
    }

    public function create(array $data)
    {
        return $this->db->insert('users', $data)->rowCount();
    }

    public function update($id, array $data)
    {
        return $this->db->update('users', $data, ['id' => $id])->rowCount();
    }

    public function delete($id)
    {
        return $this->db->delete('users', ['id' => $id])->rowCount();
    }
}
