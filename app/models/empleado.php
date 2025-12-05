<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Empleado
 * Maneja la lectura del expediente principal.
 */
class Empleado
{
    /**
     * Lista de empleados con información enriquecida.
     *
     * Filtros opcionales:
     *  - $search: nombre, CURP, RFC o NSS.
     *  - $estado: 'ACTIVO' o 'BAJA'.
     *  - $idEmpresa, $idArea, $idPuesto: filtros organizacionales.
     */
    public static function all(
        int $limit = 500,
        int $offset = 0,
        ?string $search = null,
        ?string $estado = null,
        ?int $idEmpresa = null,
        ?int $idArea = null,
        ?int $idPuesto = null
    ): array {
        global $pdo;

        $limit = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where = [];
        $params = [];

        // Búsqueda por nombre / CURP / RFC / NSS
        if ($search !== null && trim($search) !== '') {
            $q = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($search)) . '%';
            $where[] = '(e.nombre LIKE :q OR e.curp LIKE :q OR e.rfc LIKE :q OR e.nss LIKE :q)';
            $params[':q'] = $q;
        }

        // Filtro por estado (ACTIVO / BAJA)
        if ($estado !== null && $estado !== '') {
            $where[] = 'e.estado = :estado';
            $params[':estado'] = $estado;
        }

        // Filtro por empresa
        if ($idEmpresa !== null) {
            $where[] = 'emp.id_empresa = :id_empresa';
            $params[':id_empresa'] = $idEmpresa;
        }

        // Filtro por área
        if ($idArea !== null) {
            $where[] = 'a.id_area = :id_area';
            $params[':id_area'] = $idArea;
        }

        // Filtro por puesto
        if ($idPuesto !== null) {
            $where[] = 'p.id_puesto = :id_puesto';
            $params[':id_puesto'] = $idPuesto;
        }

        $sql = "SELECT
                    e.*,
                    p.nombre_puesto,
                    a.nombre_area,
                    emp.nombre   AS empresa_nombre,
                    u.nombre     AS ubicacion_nombre,
                    t.nombre_turno AS turno_nombre
                FROM empleados e
                INNER JOIN puestos     p   ON p.id_puesto    = e.id_puesto
                INNER JOIN areas       a   ON a.id_area      = p.id_area
                INNER JOIN empresas    emp ON emp.id_empresa = a.id_empresa
                LEFT  JOIN ubicaciones u   ON u.id_ubicacion = e.id_ubicacion
                LEFT  JOIN turnos      t   ON t.id_turno     = e.id_turno";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY emp.nombre, a.nombre_area, p.nombre_puesto, e.nombre
                  LIMIT :limit OFFSET :offset";

        $st = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }

        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca un empleado por ID, con los mismos joins que el listado.
     * (La usaremos en la vista de detalle/expediente).
     */
    public static function findById(int $idEmpleado): ?array
    {
        global $pdo;

        if ($idEmpleado <= 0) {
            return null;
        }

        $sql = "SELECT
                    e.*,
                    p.nombre_puesto,
                    a.nombre_area,
                    emp.nombre   AS empresa_nombre,
                    u.nombre     AS ubicacion_nombre,
                    t.nombre_turno AS turno_nombre
                FROM empleados e
                INNER JOIN puestos     p   ON p.id_puesto    = e.id_puesto
                INNER JOIN areas       a   ON a.id_area      = p.id_area
                INNER JOIN empresas    emp ON emp.id_empresa = a.id_empresa
                LEFT  JOIN ubicaciones u   ON u.id_ubicacion = e.id_ubicacion
                LEFT  JOIN turnos      t   ON t.id_turno     = e.id_turno
                WHERE e.id_empleado = :id";

        $st = $pdo->prepare($sql);
        $st->bindValue(':id', $idEmpleado, \PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
