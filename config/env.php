<?php
declare(strict_types=1);

return [
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'rrhh_tec',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
  ],
  'app' => [
    'debug' => true,
    'base_url' => 'http://localhost/ProyectoRRHH'
  ],
  'security' => [
    'session_name' => 'rrhh_sess',
    'csrf_key' => 'RRHH_TEC_CSRF_KEY_32CHARS_MIN'
  ]
];
