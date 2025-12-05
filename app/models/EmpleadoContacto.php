<?php
declare(strict_types=1);

/**
 * Modelo para contactos de emergencia y personales de empleados
 */
class EmpleadoContacto
{
    /**
     * Obtener todos los contactos de un empleado
     */
    public static function porEmpleado(int $idEmpleado, ?string $tipo = null): array
    {
        global $pdo;

        $sql = "
            SELECT 
                id_contacto,
                id_empleado,
                tipo,
                nombre,
                telefono,
                correo,
                parentesco,
                activo
            FROM empleados_contactos
            WHERE id_empleado = :id_empleado
        ";

        $params = [':id_empleado' => $idEmpleado];

        if ($tipo !== null) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $sql .= " ORDER BY tipo, nombre";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un contacto por ID
     */
    public static function findById(int $idContacto): ?array
    {
        global $pdo;

        $sql = "
            SELECT 
                id_contacto,
                id_empleado,
                tipo,
                nombre,
                telefono,
                correo,
                parentesco,
                activo
            FROM empleados_contactos
            WHERE id_contacto = :id_contacto
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_contacto' => $idContacto]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crear un nuevo contacto
     */
    public static function create(array $data): int
    {
        global $pdo;

        // Validaciones
        if (empty($data['id_empleado']) || empty($data['nombre']) || empty($data['telefono'])) {
            throw new InvalidArgumentException('Faltan campos obligatorios');
        }

        // Validar tipo
        $tiposValidos = ['EMERGENCIA', 'PERSONAL', 'OTRO'];
        if (!in_array($data['tipo'] ?? 'EMERGENCIA', $tiposValidos)) {
            throw new InvalidArgumentException('Tipo de contacto no válido');
        }

        $sql = "
            INSERT INTO empleados_contactos (
                id_empleado,
                tipo,
                nombre,
                telefono,
                correo,
                parentesco,
                activo
            ) VALUES (
                :id_empleado,
                :tipo,
                :nombre,
                :telefono,
                :correo,
                :parentesco,
                :activo
            )
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_empleado' => $data['id_empleado'],
            ':tipo' => $data['tipo'] ?? 'EMERGENCIA',
            ':nombre' => $data['nombre'],
            ':telefono' => $data['telefono'],
            ':correo' => $data['correo'] ?? null,
            ':parentesco' => $data['parentesco'] ?? null,
            ':activo' => $data['activo'] ?? 1
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualizar un contacto
     */
    public static function update(int $idContacto, array $data): bool
    {
        global $pdo;

        // Validaciones
        if (empty($data['nombre']) || empty($data['telefono'])) {
            throw new InvalidArgumentException('Faltan campos obligatorios');
        }

        // Validar tipo
        $tiposValidos = ['EMERGENCIA', 'PERSONAL', 'OTRO'];
        if (isset($data['tipo']) && !in_array($data['tipo'], $tiposValidos)) {
            throw new InvalidArgumentException('Tipo de contacto no válido');
        }

        $sql = "
            UPDATE empleados_contactos
            SET tipo = :tipo,
                nombre = :nombre,
                telefono = :telefono,
                correo = :correo,
                parentesco = :parentesco,
                activo = :activo
            WHERE id_contacto = :id_contacto
        ";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_contacto' => $idContacto,
            ':tipo' => $data['tipo'] ?? 'EMERGENCIA',
            ':nombre' => $data['nombre'],
            ':telefono' => $data['telefono'],
            ':correo' => $data['correo'] ?? null,
            ':parentesco' => $data['parentesco'] ?? null,
            ':activo' => $data['activo'] ?? 1
        ]);
    }

    /**
     * Desactivar un contacto
     */
    public static function desactivar(int $idContacto): bool
    {
        global $pdo;

        $sql = "UPDATE empleados_contactos SET activo = 0 WHERE id_contacto = :id_contacto";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id_contacto' => $idContacto]);
    }

    /**
     * Eliminar un contacto
     */
    public static function delete(int $idContacto): bool
    {
        global $pdo;

        $sql = "DELETE FROM empleados_contactos WHERE id_contacto = :id_contacto";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id_contacto' => $idContacto]);
    }

    /**
     * Obtener tipos de contacto disponibles
     */
    public static function getTipos(): array
    {
        return [
            'EMERGENCIA' => 'Contacto de Emergencia',
            'PERSONAL' => 'Contacto Personal',
            'OTRO' => 'Otro'
        ];
    }
}
