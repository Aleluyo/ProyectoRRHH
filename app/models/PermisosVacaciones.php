<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo para políticas de vacaciones y solicitudes de permisos.
 */
class PermisosVacaciones
{
    public static function listarPoliticas(): array
    {
        global $pdo;
        $sql = "SELECT pv.*, emp.nombre AS empresa_nombre
                FROM politicas_vacaciones pv
                INNER JOIN empresas emp ON emp.id_empresa = pv.id_empresa
                ORDER BY emp.nombre ASC";
        $st = $pdo->query($sql);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function crearPolitica(array $data): int
    {
        global $pdo;

        $idEmpresa       = (int)($data['id_empresa'] ?? 0);
        $diasInicio      = (int)($data['dias_inicio'] ?? 0);
        $incrementoAnual = (int)($data['incremento_anual'] ?? 0);
        $diasMax         = (int)($data['dias_max'] ?? 0);
        $periodoInicio   = trim((string)($data['periodo_anual_inicio'] ?? ''));
        $activa          = isset($data['activa']) ? 1 : 0;

        if ($idEmpresa <= 0) {
            throw new \InvalidArgumentException('Selecciona la empresa.');
        }
        if ($diasInicio <= 0 || $diasMax <= 0) {
            throw new \InvalidArgumentException('Los días deben ser mayores a cero.');
        }
        if ($incrementoAnual < 0) {
            throw new \InvalidArgumentException('El incremento anual no puede ser negativo.');
        }
        if ($diasMax < $diasInicio) {
            throw new \InvalidArgumentException('El tope máximo debe ser mayor al inicio.');
        }

        $sql = "INSERT INTO politicas_vacaciones
                (id_empresa, dias_inicio, incremento_anual, dias_max, periodo_anual_inicio, activa)
                VALUES (?,?,?,?,?,?)";
        $st = $pdo->prepare($sql);
        $st->execute([
            $idEmpresa,
            $diasInicio,
            $incrementoAnual,
            $diasMax,
            $periodoInicio !== '' ? $periodoInicio : null,
            $activa,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function listarSolicitudes(
        int $limit = 100,
        int $offset = 0,
        ?int $idEmpleado = null,
        ?string $tipo = null,
        ?string $estado = null,
        ?int $aprobador = null
    ): array {
        global $pdo;

        $limit  = max(1, min($limit, 500));
        $offset = max(0, $offset);

        $where  = [];
        $params = [];

        if ($idEmpleado !== null && $idEmpleado > 0) {
            $where[] = 'sp.id_empleado = :id_empleado';
            $params[':id_empleado'] = $idEmpleado;
        }
        if ($tipo !== null && $tipo !== '') {
            $where[] = 'sp.tipo = :tipo';
            $params[':tipo'] = $tipo;
        }
        if ($estado !== null && $estado !== '') {
            $where[] = 'sp.estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($aprobador !== null && $aprobador > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM aprobaciones_permiso ap WHERE ap.id_solicitud = sp.id_solicitud AND ap.aprobador = :aprobador)';
            $params[':aprobador'] = $aprobador;
        }

        $sql = "SELECT sp.*, e.nombre AS empleado_nombre,
                       GROUP_CONCAT(CONCAT('Nivel ', ap.nivel, ': ', COALESCE(u.username, CONCAT('ID ', ap.aprobador)), ' → ', ap.decision) ORDER BY ap.nivel SEPARATOR ' | ') AS aprobaciones
                FROM solicitudes_permiso sp
                INNER JOIN empleados e ON e.id_empleado = sp.id_empleado
                LEFT JOIN aprobaciones_permiso ap ON ap.id_solicitud = sp.id_solicitud
                LEFT JOIN usuarios u ON u.id_usuario = ap.aprobador";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY sp.id_solicitud
                  ORDER BY sp.creado_en DESC
                  LIMIT :limit OFFSET :offset';

        $st = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }

        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function listarAprobacionesPendientes(int $aprobadorId): array
    {
        global $pdo;
        $sql = "SELECT ap.*, sp.tipo, sp.fecha_inicio, sp.fecha_fin, sp.estado, e.nombre AS empleado_nombre
                FROM aprobaciones_permiso ap
                INNER JOIN solicitudes_permiso sp ON sp.id_solicitud = ap.id_solicitud
                INNER JOIN empleados e ON e.id_empleado = sp.id_empleado
                WHERE ap.aprobador = ? AND ap.decision = 'PENDIENTE'
                ORDER BY ap.nivel ASC, ap.id_aprobacion ASC";
        $st = $pdo->prepare($sql);
        $st->execute([$aprobadorId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function crearSolicitud(array $data, array $aprobadores = []): int
    {
        global $pdo;

        $idEmpleado = (int)($data['id_empleado'] ?? 0);
        $tipo       = strtoupper(trim((string)($data['tipo'] ?? '')));
        $inicio     = trim((string)($data['fecha_inicio'] ?? ''));
        $fin        = trim((string)($data['fecha_fin'] ?? ''));
        $motivo     = trim((string)($data['motivo'] ?? ''));
        $creadoPor  = (int)($data['creado_por'] ?? 0);
        $dias       = isset($data['dias']) ? (float)$data['dias'] : null;

        if ($idEmpleado <= 0 || $creadoPor <= 0) {
            throw new \InvalidArgumentException('Falta seleccionar empleado o usuario.');
        }
        if ($tipo === '' || !in_array($tipo, ['VACACIONES', 'PERMISO', 'INCAPACIDAD', 'OTRO'], true)) {
            throw new \InvalidArgumentException('Tipo inválido.');
        }
        if ($inicio === '' || $fin === '') {
            throw new \InvalidArgumentException('Debes definir el rango de fechas.');
        }
        $tsInicio = strtotime($inicio);
        $tsFin    = strtotime($fin);
        if ($tsInicio === false || $tsFin === false || $tsFin < $tsInicio) {
            throw new \InvalidArgumentException('El rango de fechas es inválido.');
        }
        if ($dias === null || $dias <= 0) {
            $dias = max(1, ($tsFin - $tsInicio) / 86400 + 1);
        }

        if (empty($aprobadores)) {
            $aprobadores = [
                ['aprobador' => $creadoPor, 'nivel' => 1],
            ];
        }

        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare("INSERT INTO solicitudes_permiso
                (id_empleado, tipo, fecha_inicio, fecha_fin, dias, motivo, estado, documento, creado_por)
                VALUES (?,?,?,?,?,?, 'PENDIENTE', NULL, ?)");
            $st->execute([
                $idEmpleado,
                $tipo,
                $inicio,
                $fin,
                $dias,
                $motivo,
                $creadoPor,
            ]);

            $idSolicitud = (int)$pdo->lastInsertId();

            $apStmt = $pdo->prepare("INSERT INTO aprobaciones_permiso (id_solicitud, aprobador, nivel, decision) VALUES (?,?,?, 'PENDIENTE')");
            foreach ($aprobadores as $ap) {
                $aprobadorId = (int)($ap['aprobador'] ?? 0);
                $nivel       = (int)($ap['nivel'] ?? 1);
                if ($aprobadorId <= 0) {
                    continue;
                }
                $apStmt->execute([$idSolicitud, $aprobadorId, max(1, $nivel)]);
            }

            $pdo->commit();
            return $idSolicitud;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function decidirAprobacion(int $idAprobacion, string $decision, ?string $comentario = null): void
    {
        global $pdo;

        $decision = strtoupper(trim($decision));
        if (!in_array($decision, ['APROBADO', 'RECHAZADO'], true)) {
            throw new \InvalidArgumentException('Decisión inválida.');
        }

        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare("SELECT id_solicitud FROM aprobaciones_permiso WHERE id_aprobacion = ? LIMIT 1");
            $st->execute([$idAprobacion]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                throw new \RuntimeException('No se encontró la aprobación.');
            }
            $idSolicitud = (int)$row['id_solicitud'];

            $upd = $pdo->prepare("UPDATE aprobaciones_permiso
                                   SET decision = ?, comentario = ?, decidido_en = NOW()
                                   WHERE id_aprobacion = ?");
            $upd->execute([$decision, $comentario, $idAprobacion]);

            // Recalcula estado de solicitud
            $estados = $pdo->prepare("SELECT decision FROM aprobaciones_permiso WHERE id_solicitud = ?");
            $estados->execute([$idSolicitud]);
            $decisiones = $estados->fetchAll(\PDO::FETCH_COLUMN);

            $nuevoEstado = 'PENDIENTE';
            if (in_array('RECHAZADO', $decisiones, true)) {
                $nuevoEstado = 'RECHAZADO';
            } elseif (!in_array('PENDIENTE', $decisiones, true)) {
                $nuevoEstado = 'APROBADO';
            }

            $pdo->prepare("UPDATE solicitudes_permiso SET estado = ? WHERE id_solicitud = ?")
                ->execute([$nuevoEstado, $idSolicitud]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
