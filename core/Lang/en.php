<?php

return [
  'install' => [
    'page_title' => 'EchoStack - Initial Setup',
    'logo' => '>EchoStack',
    'check_intro' => 'Before starting, let’s verify that your server meets the prerequisites for running EchoStack. These items ensure everything works properly.',
    'php_version' => 'PHP Version',
    'php_version_required' => '(minimum required is 8.1)',
    'extension_required' => '(required)',
    'db_intro' => 'Now we need your database connection info. These values will be saved to the .env file and used to connect your API to your selected database.',
    'none' => 'None',
    'form_labels' => [
      'app_url' => 'Application URL',
      'db_name' => 'Database Name',
      'db_user' => 'Username',
      'db_pass' => 'Password',
      'db_host' => 'Database Host',
      'db_driver' => 'Database Type',
    ],
    'swagger' => [
      'disabled' => 'Swagger documentation access is disabled.',
      'unauthorized' => 'Unauthorized access to documentation.',
      'invalid_format' => 'Invalid version format.',
      'not_found' => 'Documentation not found for version :version.',
      'deprecated_warning' => 'This API version is deprecated and may be removed soon.'
    ],
    'form_placeholder' => [
      'app_url' => 'http://localhost:8080',
    ],
    'submit_button' => 'Save Configuration',
  ],
];
