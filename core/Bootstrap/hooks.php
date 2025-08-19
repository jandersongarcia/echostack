<?php

/**
 * EchoStack - Hook Loader
 *
 * This file is responsible for loading application-wide hooks.
 *
 * You can continue to define simple hooks directly here.
 * Or, for better organization, use the `/hooks/` directory with PSR-4 autoloading:
 * 
 * In `composer.json`:
 *     "autoload": {
 *         "psr-4": {
 *             "Hooks\\": "hooks/"
 *         }
 *     }
 *
 * This allows you to create hooks like `Hooks\UserRegisteredHook` and call them dynamically.
 *
 * This file will still be included in the core flow (ex: after social login or user registration),
 * so it can serve as both a configuration hub and fallback definition area.
 */

// Example: generic sendEmail hook using MailHelper
use Utils\MailHelper;

if (!function_exists('sendEmail')) {
    function sendEmail(string $to, string $subject, string $message): bool
    {
        return Utils\MailHelper::send($to, $subject, $message);
    }
}

// You can add more hook functions below or register autoloaded hook classes via PSR-4
