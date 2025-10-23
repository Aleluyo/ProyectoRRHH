<?php
declare(strict_types=1);

final class Session {
  public static function start(string $name): void {
    if (session_status() === PHP_SESSION_NONE) {
      session_name($name);
      session_start();
    }
  }
  public static function set(string $k, mixed $v): void { $_SESSION[$k] = $v; }
  public static function get(string $k, mixed $d=null): mixed { return $_SESSION[$k] ?? $d; }
  public static function has(string $k): bool { return isset($_SESSION[$k]); }
  public static function delete(string $k): void { unset($_SESSION[$k]); }
  public static function destroy(): void { session_destroy(); }
}
