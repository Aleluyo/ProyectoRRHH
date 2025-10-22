<?php
declare(strict_types=1);

final class Csrf {
  public static function token(): string {
    if (!Session::has('_csrf')) {
      Session::set('_csrf', bin2hex(random_bytes(32)));
    }
    return Session::get('_csrf');
  }
  public static function verify(?string $token): bool {
    return hash_equals(Session::get('_csrf') ?? '', (string)$token);
  }
}
