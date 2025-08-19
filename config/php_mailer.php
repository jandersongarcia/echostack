<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMTP Server Settings
    |--------------------------------------------------------------------------
    |
    | Here you specify the necessary settings for the SMTP server,
    | essential for sending emails through your application.
    |
    */
    'host' => '', // Replace with your SMTP server address
    'port' => 465, // Choose between 587 or 465 for a secure SSL connection
    'username' => '', // Replace with your email address
    'password' => '', // Replace with your password
    'smtp_secure' => 'ssl', // Choose between 'tls' or 'ssl' for a secure connection
    'mail_driver' => 'smtp', // Mail driver to use for sending emails

    /*
    |--------------------------------------------------------------------------
    | Sender Settings
    |--------------------------------------------------------------------------
    |
    | In this section, you define the sender-related settings,
    | important for sending emails from the application.
    |
    */
    'from_email' => '', // Default sender email address
    'from_name' => '', // Default sender name

    /*
    |--------------------------------------------------------------------------
    | Default Email Settings
    |--------------------------------------------------------------------------
    |
    | Here you define the default settings for the email body,
    | essential for sending emails from the application.
    |
    */
    'is_html' => true, // Indicates whether the email will contain HTML content
    'charset' => 'utf-8', // Character set for the email
];
