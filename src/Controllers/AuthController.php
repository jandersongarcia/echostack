<?php

namespace App\Controllers;

use App\Services\AuthService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @OA\Tag(name="AuthController")
 */
class AuthController
{
    /**
     * @OA\Post(
     *   path="/auth/login",
     *   summary="User Login (JWT)",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="JWT token generated"),
     *   @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public static function login()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $result = (new AuthService())->login($input['email'] ?? '', $input['password'] ?? '');
        (new JsonResponse($result['body'], $result['status']))->send();
    }

    /**
     * @OA\Post(
     *   path="/auth/register",
     *   summary="Register new user",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name", "last_name", "email", "password"},
     *       @OA\Property(property="name", type="string", example="Janderson"),
     *       @OA\Property(property="last_name", type="string", example="Ganjos"),
     *       @OA\Property(property="email", type="string", example="jganjos.info@gmail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="SenhaForte123!")
     *     )
     *   ),
     *   @OA\Response(response=201, description="User created"),
     *   @OA\Response(response=400, description="Email already registered")
     * )
     */
    public static function register()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $name = $input['name'] ?? '';
        $lastName = $input['last_name'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $result = (new AuthService())->register($name, $lastName, $email, $password);

        (new JsonResponse($result['body'], $result['status']))->send();
    }


    /**
     * @OA\Post(
     *   path="/auth/forgot-password",
     *   summary="Request password recovery",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *       @OA\Property(property="lang", type="string", example="en", description="Optional language code for the email template")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Password recovery email sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="string", example="email_sent")
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Invalid email provided",
     *     @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="invalid_email")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="User not found",
     *     @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="user_not_found")
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal server error",
     *     @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="internal_error")
     *     )
     *   )
     * )
     */
    public static function forgotPassword()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        $lang = strtolower($input['lang'] ?? '');
        if (!$email) {
            (new JsonResponse(['error' => 'invalid_email'], 400))->send();
            return;
        }
        $result = (new AuthService())->forgotPassword($email, $lang);
        (new JsonResponse($result['body'], $result['status']))->send();
    }


    /**
     * @OA\Get(
     *   path="/auth/reset-password",
     *   summary="Validate password recovery token",
     *   tags={"Auth"},
     *   @OA\Parameter(
     *     name="token",
     *     in="query",
     *     required=true,
     *     description="Password recovery token",
     *     @OA\Schema(type="string", example="a1b2c3d4e5")
     *   ),
     *   @OA\Response(response=200, description="Token is valid"),
     *   @OA\Response(response=400, description="Invalid or expired token")
     * )
     */

    /**
     * @OA\Post(
     *   path="/auth/reset-password",
     *   summary="Reset password with recovery token",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"token","new_password"},
     *       @OA\Property(property="token", type="string", example="a1b2c3d4e5"),
     *       @OA\Property(property="new_password", type="string", example="NovaSenha123!")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Password reset"),
     *   @OA\Response(response=400, description="Invalid or expired token")
     * )
     */

    public static function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $token = $_GET['token'] ?? '';
            $result = (new AuthService())->validateResetToken($token);
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            $result = (new AuthService())->resetPassword($input['token'] ?? '', $input['new_password'] ?? '');
        }

        (new JsonResponse($result['body'], $result['status']))->send();
    }


    /**
     * @OA\Post(
     *   path="/auth/logout",
     *   summary="Logout do usuário autenticado",
     *   description="Revoga o token JWT atual e encerra a sessão do usuário.",
     *   tags={"Auth"},
     *   security={{"bearerAuth": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="Logout bem-sucedido",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="success", type="string", example="logout_successful")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Token ausente ou revogado",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="error", type="string", example="token_revoked")
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Erro interno no servidor",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="error", type="string", example="internal_error")
     *     )
     *   )
     * )
     */
    public static function logout()
    {
        $result = (new AuthService())->logout();
        (new JsonResponse($result['body'], $result['status']))->send();
    }

}