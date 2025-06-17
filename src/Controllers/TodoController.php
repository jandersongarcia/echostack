<?php

namespace App\Controllers;

use App\Services\{TodoService};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(name="Todo")
 */
class TodoController
{
    protected $service;

    public function __construct()
    {
        $this->service = new TodoService();
    }

    /**
     * @OA\Get(
     *     path="/todo",
     *     tags={"Todo"},
     *     summary="List all records",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Todo"))
     *     )
     * )
     */
    public function index()
    {
        echo json_encode($this->service->list());
    }

    /**
     * @OA\Get(
     *     path="/todo/id",
     *     tags={"Todo"},
     *     summary="Get a single record",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(ref="#/components/schemas/Todo")),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show($id)
    {
        echo json_encode($this->service->get($id));
    }

    /**
     * @OA\Post(
     *     path="/todo",
     *     tags={"Todo"},
     *     summary="Create a new record",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Todo")),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['created' => $this->service->create($data)]);
    }

    /**
     * @OA\Put(
     *     path="/todo/id",
     *     tags={"Todo"},
     *     summary="Update a record",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Todo")),
     *     @OA\Response(response=200, description="Updated")
     * )
     */
    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['updated' => $this->service->update($id, $data)]);
    }

    /**
     * @OA\Delete(
     *     path="/todo/id",
     *     tags={"Todo"},
     *     summary="Delete a record",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy($id)
    {
        echo json_encode(['deleted' => $this->service->delete($id)]);
    }
}
