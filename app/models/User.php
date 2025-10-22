<?php
declare(strict_types=1);

final class User {
  public int $id;
  public string $username;
  public string $correo;
  public string $hash;
  public int $idRol;
  public string $estado;

  public static function findByUsername(PDO $pdo, string $username): ?self {
    $sql = "SELECT id_usuario, username, correo, contrasena, id_rol, estado
            FROM usuarios WHERE username = :u LIMIT 1";
    $st = $pdo->prepare($sql);
    $st->execute([':u' => $username]);
    $row = $st->fetch();
    if (!$row) return null;

    $u = new self();
    $u->id = (int)$row['id_usuario'];
    $u->username = $row['username'];
    $u->correo = $row['correo'];
    $u->hash = $row['contrasena'];
    $u->idRol = (int)$row['id_rol'];
    $u->estado = $row['estado'];
    return $u;
  }
}
