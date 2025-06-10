<?php

namespace App\Controllers;

use App\Services\TodoService;

class TodoController
{
    protected $service;

    public function __construct()
    {
        $this->service = new TodoService();
    }

    public function index()
    {
        echo json_encode($this->service->list());
    }

    public function show($id)
    {
        echo json_encode($this->service->get($id));
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['created' => $this->service->create($data)]);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['updated' => $this->service->update($id, $data)]);
    }

    public function destroy($id)
    {
        echo json_encode(['deleted' => $this->service->delete($id)]);
    }
}
