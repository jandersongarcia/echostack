<?php

namespace App\Controllers;

use App\Services\AuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for user login and registration"
 * )
 */
class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=400, description="Missing required fields"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->json(['error' => 'Email and password are required.'], 400);
        }

        $user = $this->authService->login($email, $password);

        if (!$user) {
            return $this->json(['error' => 'Invalid credentials.'], 401);
        }

        return $this->json([
            'message' => 'Login successful',
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User successfully registered"),
     *     @OA\Response(response=400, description="Missing required fields"),
     *     @OA\Response(response=500, description="Registration failed")
     * )
     */
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$name || !$email || !$password) {
            return $this->json(['error' => 'Name, email, and password are required.'], 400);
        }

        $ok = $this->authService->register($name, $email, $password);

        if (!$ok) {
            return $this->json(['error' => 'Failed to register user.'], 500);
        }

        return $this->json(['message' => 'User successfully registered.'], 201);
    }

    private function json(array $data, int $status = 200): Response
    {
        return new Response(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }
}
