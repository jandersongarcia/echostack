<?php

namespace App\Validators;

class AuthValidator
{
    public function validateRegistration(array $data): bool
    {
        return isset($data['name'], $data['email'], $data['password']);
    }
}
