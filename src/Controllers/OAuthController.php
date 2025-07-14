<?php

namespace App\Controllers;

use App\Services\OAuthService;

/**
 * @OA\Tag(
 *   name="OAuth",
 *   description="Endpoints for OAuth2 authorization"
 * )
 */
class OAuthController
{
    private OAuthService $oauth;

    public function __construct()
    {
        $this->oauth = new OAuthService();
    }

    /**
     * @OA\Get(
     *   path="/oauth/{provider}/redirect",
     *   summary="Redirect to OAuth provider",
     *   description="Generates the authorization URL with PKCE and redirects the user to the OAuth2 provider.",
     *   tags={"OAuth"},
     *   @OA\Parameter(
     *     name="provider",
     *     in="path",
     *     required=true,
     *     description="Provider name (e.g., azure)"
     *   ),
     *   @OA\Response(response=302, description="Redirect to the provider")
     * )
     */
    public function redirect($params)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $providerName = $params['provider'];
        $provider = $this->oauth->getProvider($providerName);

        $codeVerifier = bin2hex(random_bytes(64));
        $codeChallenge = rtrim(strtr(
            base64_encode(hash('sha256', $codeVerifier, true)),
            '+/',
            '-_'
        ), '=');

        $authUrl = $provider->getAuthorizationUrl([
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $_SESSION['oauth2state'] = $provider->getState();
        $_SESSION['oauth2pkce'] = $codeVerifier;

        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * @OA\Get(
     *   path="/oauth/{provider}/callback",
     *   summary="OAuth callback handler",
     *   description="Handles the OAuth2 callback, exchanges the authorization code for an access token, verifies if the user exists, and returns your JWT.",
     *   tags={"OAuth"},
     *   @OA\Parameter(
     *     name="provider",
     *     in="path",
     *     required=true,
     *     description="Provider name (e.g., azure)"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OAuth callback successful",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="success", type="string", example="login_successful"),
     *       @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *       @OA\Property(
     *         property="user",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="John Doe"),
     *         @OA\Property(property="avatar", type="string", example="https://cdn.example.com/avatar.jpg"),
     *         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *         @OA\Property(property="status", type="integer", example=1),
     *         @OA\Property(property="last_login", type="string", example="2024-01-01 12:00:00")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=400, description="Invalid request or state"),
     *   @OA\Response(response=404, description="User not found"),
     *   @OA\Response(response=500, description="Error exchanging token")
     * )
     */
    public function callback($params)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $providerName = $params['provider'];
        $provider = $this->oauth->getProvider($providerName);

        // URL base do seu frontend
        $url = rtrim($_ENV['APP_URL'] ?? 'http://localhost:3000', '/');

        // Validação do state
        if (
            empty($_GET['state']) ||
            empty($_SESSION['oauth2state']) ||
            $_GET['state'] !== $_SESSION['oauth2state']
        ) {
            header("Location: {$url}/oauth/error?error=invalid_state");
            exit;
        }

        // Validação do PKCE
        if (empty($_SESSION['oauth2pkce'])) {
            header("Location: {$url}/oauth/error?error=missing_pkce");
            exit;
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code'],
                'code_verifier' => $_SESSION['oauth2pkce'],
            ]);

            // Obtém o resource owner
            $user = $provider->getResourceOwner($token);
            $userData = $user->toArray();

            // Extrai o e-mail usando todos os fallbacks possíveis
            $email = $userData['mail']
                ?? $userData['userPrincipalName']
                ?? (
                    (isset($userData['preferred_username']) && str_contains($userData['preferred_username'], '#'))
                    ? explode('#', $userData['preferred_username'])[1]
                    : null
                )
                ?? (
                    (isset($userData['unique_name']) && str_contains($userData['unique_name'], '#'))
                    ? explode('#', $userData['unique_name'])[1]
                    : null
                );

            if (!$email) {
                header("Location: {$url}/oauth/error?error=email_not_returned");
                exit;
            }

            // Login no seu sistema
            $result = $this->oauth->loginWithOAuth($email);

            // Se login falhou, redireciona com erro
            if ($result['status'] !== 200) {
                $errorCode = $result['body']['error'] ?? 'login_failed';
                header("Location: {$url}/oauth/error?error=" . urlencode($errorCode));
                exit;
            }

            // Login bem-sucedido: redireciona com token
            $tokenJwt = $result['body']['token'] ?? null;
            if (!$tokenJwt) {
                header("Location: {$url}/oauth/error?error=token_generation_failed");
                exit;
            }

            unset($_SESSION['oauth2pkce']);

            // Redireciona com token
            header("Location: {$url}/oauth/success?token=" . urlencode($tokenJwt));
            exit;
        } catch (\Exception $e) {
            header("Location: {$url}/oauth/error?error=" . urlencode($e->getMessage()));
            exit;
        }
    }

}
