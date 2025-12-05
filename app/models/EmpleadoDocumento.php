<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo EmpleadoDocumento
 * Gestiona los documentos del expediente de empleados
 */
class EmpleadoDocumento
{
    /**
     * Tipos de documentos permitidos
     */
    public static function tiposDocumento(): array
    {
        return [
            'INE' => 'Identificación Oficial (INE)',
            'CURP' => 'CURP',
            'RFC' => 'RFC',
            'NSS' => 'Número de Seguro Social',
            'COMPROBANTE_DOMICILIO' => 'Comprobante de Domicilio',
            'ACTA_NACIMIENTO' => 'Acta de Nacimiento',
            'COMPROBANTE_ESTUDIOS' => 'Comprobante de Estudios',
            'CONTRATO' => 'Contrato Laboral',
            'CARTA_RECOMENDACION' => 'Carta de Recomendación',
            'ANTECEDENTES_NO_PENALES' => 'Carta de Antecedentes No Penales',
            'CURRICULUM' => 'Currículum Vitae',
            'FOTOGRAFIAS' => 'Fotografías',
            'CERTIFICADO_MEDICO' => 'Certificado Médico',
            'CARTA_OFERTA' => 'Carta Oferta',
            'OTRO' => 'Otro Documento'
        ];
    }

    /**
     * Extensiones de archivo permitidas
     */
    public static function extensionesPermitidas(): array
    {
        return ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    }

    /**
     * Tamaño máximo de archivo en KB
     */
    public static function tamanoMaximoKB(): int
    {
        return 5120; // 5 MB
    }

    /**
     * Obtener todos los documentos de un empleado
     */
    public static function porEmpleado(int $idEmpleado): array
    {
        global $pdo;

        $sql = "
            SELECT 
                d.*,
                u_subida.username AS usuario_subida,
                u_verifica.username AS usuario_verificacion
            FROM empleados_documentos d
            LEFT JOIN usuarios u_subida ON d.subido_por = u_subida.id_usuario
            LEFT JOIN usuarios u_verifica ON d.verificado_por = u_verifica.id_usuario
            WHERE d.id_empleado = :id_empleado
            ORDER BY d.fecha_subida DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_empleado' => $idEmpleado]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un documento por ID
     */
    public static function findById(int $idDocumento): ?array
    {
        global $pdo;

        $sql = "
            SELECT 
                d.*,
                e.nombre AS nombre_empleado,
                e.curp,
                u_subida.username AS usuario_subida,
                u_verifica.username AS usuario_verificacion
            FROM empleados_documentos d
            INNER JOIN empleados e ON d.id_empleado = e.id_empleado
            LEFT JOIN usuarios u_subida ON d.subido_por = u_subida.id_usuario
            LEFT JOIN usuarios u_verifica ON d.verificado_por = u_verifica.id_usuario
            WHERE d.id_documento = :id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idDocumento]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crear nuevo documento
     */
    public static function create(array $data): int
    {
        global $pdo;

        $sql = "
            INSERT INTO empleados_documentos (
                id_empleado,
                tipo_documento,
                nombre_archivo,
                ruta_archivo,
                extension,
                tamano_kb,
                fecha_vigencia,
                estado,
                observaciones,
                subido_por
            ) VALUES (
                :id_empleado,
                :tipo_documento,
                :nombre_archivo,
                :ruta_archivo,
                :extension,
                :tamano_kb,
                :fecha_vigencia,
                'PENDIENTE',
                :observaciones,
                :subido_por
            )
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_empleado' => $data['id_empleado'],
            ':tipo_documento' => $data['tipo_documento'],
            ':nombre_archivo' => $data['nombre_archivo'],
            ':ruta_archivo' => $data['ruta_archivo'],
            ':extension' => $data['extension'],
            ':tamano_kb' => $data['tamano_kb'],
            ':fecha_vigencia' => $data['fecha_vigencia'] ?? null,
            ':observaciones' => $data['observaciones'] ?? null,
            ':subido_por' => $data['subido_por']
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Verificar documento (cambiar estado a VERIFICADO)
     */
    public static function verificar(int $idDocumento, int $verificadoPor, ?string $observaciones = null): bool
    {
        global $pdo;

        $sql = "
            UPDATE empleados_documentos 
            SET 
                estado = 'VERIFICADO',
                verificado_por = :verificado_por,
                fecha_verificacion = NOW(),
                observaciones = :observaciones
            WHERE id_documento = :id
        ";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $idDocumento,
            ':verificado_por' => $verificadoPor,
            ':observaciones' => $observaciones
        ]);
    }

    /**
     * Rechazar documento
     */
    public static function rechazar(int $idDocumento, int $verificadoPor, string $observaciones): bool
    {
        global $pdo;

        $sql = "
            UPDATE empleados_documentos 
            SET 
                estado = 'RECHAZADO',
                verificado_por = :verificado_por,
                fecha_verificacion = NOW(),
                observaciones = :observaciones
            WHERE id_documento = :id
        ";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $idDocumento,
            ':verificado_por' => $verificadoPor,
            ':observaciones' => $observaciones
        ]);
    }

    /**
     * Eliminar documento (físicamente y de BD)
     */
    public static function eliminar(int $idDocumento): bool
    {
        global $pdo;

        // Obtener info del documento
        $doc = self::findById($idDocumento);
        if (!$doc) {
            return false;
        }

        // Eliminar archivo físico
        $rutaCompleta = __DIR__ . '/../../' . $doc['ruta_archivo'];
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }

        // Eliminar de BD
        $sql = "DELETE FROM empleados_documentos WHERE id_documento = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $idDocumento]);
    }

    /**
     * Validar archivo subido
     */
    public static function validarArchivo(array $archivo): array
    {
        $errores = [];

        // Verificar que se subió correctamente
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'Error al subir el archivo';
            return $errores;
        }

        // Verificar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::extensionesPermitidas())) {
            $errores[] = 'Extensión no permitida. Solo: ' . implode(', ', self::extensionesPermitidas());
        }

        // Verificar tamaño
        $tamanoKB = (int) ($archivo['size'] / 1024);
        if ($tamanoKB > self::tamanoMaximoKB()) {
            $errores[] = 'El archivo excede el tamaño máximo de ' . (self::tamanoMaximoKB() / 1024) . ' MB';
        }

        return $errores;
    }

    /**
     * Generar nombre único para archivo
     */
    public static function generarNombreArchivo(string $nombreOriginal, int $idEmpleado): string
    {
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return "emp_{$idEmpleado}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Contar documentos por estado
     */
    public static function contarPorEstado(int $idEmpleado): array
    {
        global $pdo;

        $sql = "
            SELECT 
                estado,
                COUNT(*) as total
            FROM empleados_documentos
            WHERE id_empleado = :id_empleado
            GROUP BY estado
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_empleado' => $idEmpleado]);

        $resultado = [
            'PENDIENTE' => 0,
            'VERIFICADO' => 0,
            'RECHAZADO' => 0
        ];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $resultado[$row['estado']] = (int) $row['total'];
        }

        return $resultado;
    }
}
