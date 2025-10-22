<?php
declare(strict_types=1);

require_once CORE_PATH . '/Session.php';
require_once CONF_PATH . '/paths.php';

function requireLogin(): void {
  if (!Session::has('user')) redirect('/login');
}
function requireGuest(): void {
  if (Session::has('user')) redirect('/dashboard');
}
