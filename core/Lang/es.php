<?php

return [
  'install' => [
    'page_title' => 'EchoStack - Instalación Inicial',
    'logo' => '>EchoStack',
    'check_intro' => 'Antes de comenzar, vamos a verificar si tu servidor cumple con los requisitos para ejecutar EchoStack. Estos ítems son fundamentales para asegurar que todo funcione correctamente.',
    'php_version' => 'Versión de PHP',
    'php_version_required' => '(mínimo requerido es 8.1)',
    'extension_required' => '(obligatorio)',
    'db_intro' => 'Ahora necesitamos los datos de tu base de datos. Estos valores se guardarán en el archivo .env y se usarán para conectar tu API a la base de datos seleccionada.',
    'none' => 'Ninguno',
    'form_labels' => [
      'app_url' => 'URL de la Aplicación',
      'db_name' => 'Nombre de la Base de Datos',
      'db_user' => 'Usuario',
      'db_pass' => 'Contraseña',
      'db_host' => 'Host de la Base de Datos',
      'db_driver' => 'Tipo de Base de Datos',
    ],
    'swagger' => [
      'disabled' => 'El acceso a la documentación Swagger está deshabilitado.',
      'unauthorized' => 'Acceso no autorizado a la documentación.',
      'invalid_format' => 'Formato de versión inválido.',
      'not_found' => 'Documentación no encontrada para la versión :version.',
      'deprecated_warning' => 'Esta versión de la API está obsoleta y podría eliminarse pronto.'
    ],
    'form_placeholder' => [
      'app_url' => 'http://localhost:8080',
    ],
    'submit_button' => 'Guardar Configuración',
  ],
];
