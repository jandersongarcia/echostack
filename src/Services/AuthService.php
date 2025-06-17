<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function login(string $email, string $senha): ?array
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($senha, $user['senha'])) {
            return null;
        }

        // Dados mínimos de retorno
        return [
            'id' => $user['id'],
            'nome' => $user['nome'],
            'email' => $user['email']
        ];
    }

    public function register(string $nome, string $email, string $senha): bool
    {
        $hash = password_hash($senha, PASSWORD_BCRYPT);
        return $this->userModel->create([
            'nome' => $nome,
            'email' => $email,
            'senha' => $hash
        ]);
    }
}
