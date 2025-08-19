<?php

namespace Utils;

class ResponseHelper
{
    public static function jsonErrorResponse(array $data): never
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
