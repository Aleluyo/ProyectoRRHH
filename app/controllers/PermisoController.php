<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/PermisosVacaciones.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Usuario.php';

class PermisoController
{
    public function index(): void
    {
        requireLogin();

        $idEmpleado = isset($_GET['id_empleado']) ? (int)$_GET['id_empleado'] : null;
        $tipo       = $_GET['tipo']   ?? null;
        $estado     = $_GET['estado'] ?? null;

        $politicas   = PermisosVacaciones::listarPoliticas();
        $solicitudes = PermisosVacaciones::listarSolicitudes(100, 0, $idEmpleado, $tipo ?: null, $estado ?: null);
        $pendientes  = PermisosVacaciones::listarAprobacionesPendientes((int)($_SESSION['user_id'] ?? 0));

        // Catálogos para combos
        $empresas  = Empresa::getActivasParaCombo();
        $empleados = Empleado::all(500, 0, null, 'ACTIVO');

        // Debug simple para verificar que haya registros
        error_log('DEBUG PERMISOS: empresas=' . count($empresas) . ' empleados=' . count($empleados));

        // Hacer las variables disponibles en el scope de la vista
        extract([
            'politicas'   => $politicas,
            'solicitudes' => $solicitudes,
            'pendientes'  => $pendientes,
            'empresas'    => $empresas,
            'empleados'   => $empleados
        ]);

        require __DIR__ . '/../../public/views/permisos/list.php';
    }

    public function guardarPolitica(): void
    {
        requireRole(1);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        error_log('DEBUG PERMISOS guardarPolitica REQUEST: ' . json_encode($_REQUEST));

        try {
            PermisosVacaciones::crearPolitica($_POST);
            $_SESSION['flash_success'] = 'Política creada.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input'] = $_POST;
        }

        redirect('index.php?controller=permiso&action=index');
    }

    public function guardarSolicitud(): void
    {
        requireLogin();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        error_log('DEBUG PERMISOS guardarSolicitud REQUEST: ' . json_encode($_REQUEST));

        try {
            $aprobadores = $this->parseAprobadores($_POST['aprobadores'] ?? '');
            $payload = $_POST;
            $payload['creado_por'] = (int)($_SESSION['user_id'] ?? 0);
            PermisosVacaciones::crearSolicitud($payload, $aprobadores);
            $_SESSION['flash_success'] = 'Solicitud registrada.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input'] = $_POST;
        }

        redirect('index.php?controller=permiso&action=index');
    }

    public function decidir(): void
    {
        requireLogin();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        error_log('DEBUG PERMISOS decidir REQUEST: ' . json_encode($_REQUEST));

        $idAprobacion = isset($_POST['id_aprobacion']) ? (int)$_POST['id_aprobacion'] : 0;
        $decision     = $_POST['decision'] ?? '';
        $comentario   = trim((string)($_POST['comentario'] ?? ''));

        if ($idAprobacion <= 0) {
            $_SESSION['flash_error'] = 'Aprobación inválida.';
            header('Location: index.php?controller=permiso&action=index');
            exit;
        }

        try {
            PermisosVacaciones::decidirAprobacion($idAprobacion, $decision, $comentario);
            $_SESSION['flash_success'] = 'Decisión registrada.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('index.php?controller=permiso&action=index');
    }

    private function parseAprobadores(string $raw): array
    {
        $clean = trim($raw);
        if ($clean === '') return [];

        $result = [];
        $parts = preg_split('/[\n,;]+/', $clean);
        $nivel = 1;
        foreach ($parts as $part) {
            $id = (int)trim($part);
            if ($id > 0) {
                $result[] = ['aprobador' => $id, 'nivel' => $nivel++];
            }
        }
        return $result;
    }
}
