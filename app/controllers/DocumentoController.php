<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/EmpleadoDocumento.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../middleware/Auth.php';

class DocumentoController
{
    /**
     * Listado de documentos (con selector de empleado)
     */
    public function listado(): void
    {
        requireLogin();
        requireRole(1);

        $idEmpleado = isset($_GET['id_empleado']) && $_GET['id_empleado'] !== ''
            ? (int) $_GET['id_empleado']
            : null;

        $empleado = null;
        $documentos = [];
        $estadisticas = null;

        if ($idEmpleado) {
            $empleado = Empleado::findById($idEmpleado);
            if ($empleado) {
                $documentos = EmpleadoDocumento::porEmpleado($idEmpleado);
                $estadisticas = EmpleadoDocumento::contarPorEstado($idEmpleado);
            }
        }

        // Lista de empleados para el selector
        $empleados = Empleado::all(limit: 1000, estado: 'ACTIVO');

        require __DIR__ . '/../../public/views/empleados/documentos/list.php';
    }

    /**
     * Formulario de subida de documento
     */
    public function subir(): void
    {
        requireLogin();
        requireRole(1);

        $idEmpleado = isset($_GET['id_empleado']) && $_GET['id_empleado'] !== ''
            ? (int) $_GET['id_empleado']
            : 0;

        if ($idEmpleado <= 0) {
            $_SESSION['mensaje'] = 'Debe seleccionar un empleado';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ' . url('index.php?controller=documento&action=listado'));
            exit;
        }

        $empleado = Empleado::findById($idEmpleado);
        if (!$empleado) {
            $_SESSION['mensaje'] = 'Empleado no encontrado';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ' . url('index.php?controller=documento&action=listado'));
            exit;
        }

        $tiposDocumento = EmpleadoDocumento::tiposDocumento();
        $extensionesPermitidas = EmpleadoDocumento::extensionesPermitidas();
        $tamanoMaximoMB = EmpleadoDocumento::tamanoMaximoKB() / 1024;

        require __DIR__ . '/../../public/views/empleados/documentos/subir.php';
    }

    /**
     * Procesar subida de documento
     */
    public function guardar(): void
    {
        requireLogin();
        requireRole(1);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('index.php?controller=documento&action=listado'));
            exit;
        }

        try {
            $idEmpleado = (int) ($_POST['id_empleado'] ?? 0);
            $tipoDocumento = $_POST['tipo_documento'] ?? '';
            $fechaVigencia = !empty($_POST['fecha_vigencia']) ? $_POST['fecha_vigencia'] : null;
            $observaciones = trim($_POST['observaciones'] ?? '') ?: null;

            // Validar empleado
            if ($idEmpleado <= 0) {
                throw new Exception('ID de empleado inválido');
            }

            $empleado = Empleado::findById($idEmpleado);
            if (!$empleado) {
                throw new Exception('Empleado no encontrado');
            }

            // Validar tipo de documento
            $tiposValidos = array_keys(EmpleadoDocumento::tiposDocumento());
            if (!in_array($tipoDocumento, $tiposValidos)) {
                throw new Exception('Tipo de documento inválido');
            }

            // Validar archivo
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Debe seleccionar un archivo');
            }

            $erroresArchivo = EmpleadoDocumento::validarArchivo($_FILES['archivo']);
            if (!empty($erroresArchivo)) {
                throw new Exception(implode('. ', $erroresArchivo));
            }

            // Crear directorio si no existe
            $directorioBase = __DIR__ . '/../../storage/documentos';
            $directorioEmpleado = $directorioBase . '/' . $idEmpleado;

            if (!is_dir($directorioBase)) {
                mkdir($directorioBase, 0755, true);
            }

            if (!is_dir($directorioEmpleado)) {
                mkdir($directorioEmpleado, 0755, true);
            }

            // Generar nombre único y guardar archivo
            $nombreArchivo = EmpleadoDocumento::generarNombreArchivo(
                $_FILES['archivo']['name'],
                $idEmpleado
            );

            $rutaRelativa = 'storage/documentos/' . $idEmpleado . '/' . $nombreArchivo;
            $rutaCompleta = $directorioEmpleado . '/' . $nombreArchivo;

            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaCompleta)) {
                throw new Exception('Error al guardar el archivo en el servidor');
            }

            // Registrar en BD
            $extension = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            $tamanoKB = (int) ($_FILES['archivo']['size'] / 1024);

            $idDocumento = EmpleadoDocumento::create([
                'id_empleado' => $idEmpleado,
                'tipo_documento' => $tipoDocumento,
                'nombre_archivo' => $_FILES['archivo']['name'],
                'ruta_archivo' => $rutaRelativa,
                'extension' => $extension,
                'tamano_kb' => $tamanoKB,
                'fecha_vigencia' => $fechaVigencia,
                'observaciones' => $observaciones,
                'subido_por' => $_SESSION['user_id']
            ]);

            $_SESSION['mensaje'] = 'Documento subido exitosamente. Estado: PENDIENTE de verificación';
            $_SESSION['tipo_mensaje'] = 'exito';
            header('Location: ' . url('index.php?controller=documento&action=listado&id_empleado=' . $idEmpleado));
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
            
            $idEmpleado = $_POST['id_empleado'] ?? 0;
            header('Location: ' . url('index.php?controller=documento&action=subir&id_empleado=' . $idEmpleado));
            exit;
        }
    }

    /**
     * Descargar/Ver documento
     */
    public function descargar(): void
    {
        requireLogin();

        $idDocumento = isset($_GET['id']) && $_GET['id'] !== ''
            ? (int) $_GET['id']
            : 0;

        if ($idDocumento <= 0) {
            http_response_code(400);
            die('ID de documento inválido');
        }

        $documento = EmpleadoDocumento::findById($idDocumento);
        if (!$documento) {
            http_response_code(404);
            die('Documento no encontrado');
        }

        $rutaCompleta = __DIR__ . '/../../' . $documento['ruta_archivo'];
        if (!file_exists($rutaCompleta)) {
            http_response_code(404);
            die('Archivo no encontrado en el servidor');
        }

        // Determinar tipo MIME
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $contentType = $mimeTypes[$documento['extension']] ?? 'application/octet-stream';

        // Enviar archivo
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: inline; filename="' . $documento['nombre_archivo'] . '"');
        header('Content-Length: ' . filesize($rutaCompleta));
        readfile($rutaCompleta);
        exit;
    }

    /**
     * Verificar documento
     */
    public function verificar(): void
    {
        requireLogin();
        requireRole(1);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido');
        }

        try {
            $idDocumento = (int) ($_POST['id_documento'] ?? 0);
            $observaciones = trim($_POST['observaciones'] ?? '') ?: null;

            if ($idDocumento <= 0) {
                throw new Exception('ID de documento inválido');
            }

            $resultado = EmpleadoDocumento::verificar(
                $idDocumento,
                $_SESSION['user_id'],
                $observaciones
            );

            if ($resultado) {
                $_SESSION['mensaje'] = 'Documento verificado exitosamente';
                $_SESSION['tipo_mensaje'] = 'exito';
            } else {
                throw new Exception('Error al verificar el documento');
            }

        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }

        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? url('index.php?controller=documento&action=listado'));
        exit;
    }

    /**
     * Rechazar documento
     */
    public function rechazar(): void
    {
        requireLogin();
        requireRole(1);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido');
        }

        try {
            $idDocumento = (int) ($_POST['id_documento'] ?? 0);
            $observaciones = trim($_POST['observaciones'] ?? '');

            if ($idDocumento <= 0) {
                throw new Exception('ID de documento inválido');
            }

            if (empty($observaciones)) {
                throw new Exception('Debe proporcionar un motivo de rechazo');
            }

            $resultado = EmpleadoDocumento::rechazar(
                $idDocumento,
                $_SESSION['user_id'],
                $observaciones
            );

            if ($resultado) {
                $_SESSION['mensaje'] = 'Documento rechazado';
                $_SESSION['tipo_mensaje'] = 'exito';
            } else {
                throw new Exception('Error al rechazar el documento');
            }

        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }

        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? url('index.php?controller=documento&action=listado'));
        exit;
    }

    /**
     * Eliminar documento
     */
    public function eliminar(): void
    {
        requireLogin();
        requireRole(1);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido');
        }

        try {
            $idDocumento = (int) ($_POST['id_documento'] ?? 0);

            if ($idDocumento <= 0) {
                throw new Exception('ID de documento inválido');
            }

            $resultado = EmpleadoDocumento::eliminar($idDocumento);

            if ($resultado) {
                $_SESSION['mensaje'] = 'Documento eliminado exitosamente';
                $_SESSION['tipo_mensaje'] = 'exito';
            } else {
                throw new Exception('Error al eliminar el documento');
            }

        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }

        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? url('index.php?controller=documento&action=listado'));
        exit;
    }
}
