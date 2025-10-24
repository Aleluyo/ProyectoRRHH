<?php
declare(strict_types=1);

$CFG = require __DIR__ . '/env.php';

function db(): PDO {
  static $pdo = null;
  global $CFG;
  if ($pdo) return $pdo;

  $dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $CFG['db']['host'],
    $CFG['db']['port'],
    $CFG['db']['name'],
    $CFG['db']['charset']
  );
  $pdo = new PDO($dsn, $CFG['db']['user'], $CFG['db']['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}