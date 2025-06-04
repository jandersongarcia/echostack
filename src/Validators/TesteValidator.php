<?php

namespace App\Validators;

use Respect\Validation\Validator as v;

class TesteValidator
{
    public function validate(array $data)
    {
        // Basic validation example
        v::key('name', v::stringType()->length(1, 100))->assert($data);
    }
}