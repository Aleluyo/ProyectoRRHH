<?php
declare(strict_types=1);

/**
 * Modelo para información bancaria de empleados
 */
class EmpleadoBanco
{
    /**
     * Obtener todas las cuentas bancarias de un empleado
     */
    public static function porEmpleado(int $idEmpleado): array
    {
        global $pdo;

        $sql = "
            SELECT 
                id_banco,
                id_empleado,
                banco,
                clabe,
                titular,
                activa
            FROM empleados_banco
            WHERE id_empleado = :id_empleado
            ORDER BY activa DESC, id_banco DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_empleado' => $idEmpleado]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener una cuenta bancaria por ID
     */
    public static function findById(int $idBanco): ?array
    {
        global $pdo;

        $sql = "
            SELECT 
                id_banco,
                id_empleado,
                banco,
                clabe,
                titular,
                activa
            FROM empleados_banco
            WHERE id_banco = :id_banco
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_banco' => $idBanco]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crear una nueva cuenta bancaria
     */
    public static function create(array $data): int
    {
        global $pdo;

        // Validaciones
        if (empty($data['id_empleado']) || empty($data['banco']) || empty($data['clabe']) || empty($data['titular'])) {
            throw new InvalidArgumentException('Faltan campos obligatorios');
        }

        // Validar formato de CLABE (18 dígitos)
        if (!preg_match('/^\d{18}$/', $data['clabe'])) {
            throw new InvalidArgumentException('La CLABE debe tener exactamente 18 dígitos');
        }

        $sql = "
            INSERT INTO empleados_banco (
                id_empleado,
                banco,
                clabe,
                titular,
                activa
            ) VALUES (
                :id_empleado,
                :banco,
                :clabe,
                :titular,
                :activa
            )
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_empleado' => $data['id_empleado'],
            ':banco' => $data['banco'],
            ':clabe' => $data['clabe'],
            ':titular' => $data['titular'],
            ':activa' => $data['activa'] ?? 1
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualizar una cuenta bancaria
     */
    public static function update(int $idBanco, array $data): bool
    {
        global $pdo;

        // Validaciones
        if (empty($data['banco']) || empty($data['clabe']) || empty($data['titular'])) {
            throw new InvalidArgumentException('Faltan campos obligatorios');
        }

        // Validar formato de CLABE (18 dígitos)
        if (!preg_match('/^\d{18}$/', $data['clabe'])) {
            throw new InvalidArgumentException('La CLABE debe tener exactamente 18 dígitos');
        }

        $sql = "
            UPDATE empleados_banco
            SET banco = :banco,
                clabe = :clabe,
                titular = :titular,
                activa = :activa
            WHERE id_banco = :id_banco
        ";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id_banco' => $idBanco,
            ':banco' => $data['banco'],
            ':clabe' => $data['clabe'],
            ':titular' => $data['titular'],
            ':activa' => $data['activa'] ?? 1
        ]);
    }

    /**
     * Desactivar una cuenta bancaria
     */
    public static function desactivar(int $idBanco): bool
    {
        global $pdo;

        $sql = "UPDATE empleados_banco SET activa = 0 WHERE id_banco = :id_banco";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id_banco' => $idBanco]);
    }

    /**
     * Eliminar una cuenta bancaria
     */
    public static function delete(int $idBanco): bool
    {
        global $pdo;

        $sql = "DELETE FROM empleados_banco WHERE id_banco = :id_banco";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id_banco' => $idBanco]);
    }

    /**
     * Verificar si la CLABE ya existe para otro empleado
     */
    public static function clabeExiste(string $clabe, ?int $exceptoId = null): bool
    {
        global $pdo;

        $sql = "SELECT COUNT(*) FROM empleados_banco WHERE clabe = :clabe";
        
        if ($exceptoId !== null) {
            $sql .= " AND id_banco != :excepto_id";
        }

        $stmt = $pdo->prepare($sql);
        $params = [':clabe' => $clabe];
        
        if ($exceptoId !== null) {
            $params[':excepto_id'] = $exceptoId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
