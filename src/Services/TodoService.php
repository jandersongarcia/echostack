<?php

namespace App\Services;

use Medoo\Medoo;

class TodoService
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
        return $this->db->select('todo', '*');
    }

    public function get($id)
    {
        return $this->db->get('todo', '*', ['id' => $id]);
    }

    public function create(array $data)
    {
        return $this->db->insert('todo', $data)->rowCount();
    }

    public function update($id, array $data)
    {
        return $this->db->update('todo', $data, ['id' => $id])->rowCount();
    }

    public function delete($id)
    {
        return $this->db->delete('todo', ['id' => $id])->rowCount();
    }
}
