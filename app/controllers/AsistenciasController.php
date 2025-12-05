<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Asistencias.php';
require_once __DIR__ . '/../middleware/Auth.php';

class AsistenciasController
{
    /**
     * Lista de asistencias
     * GET: ?controller=asistencia&action=index
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $idEmpleado = isset($_GET['id_empleado']) ? (int)$_GET['id_empleado'] : null;
        if ($idEmpleado !== null && $idEmpleado <= 0) $idEmpleado = null;
        $desde = $_GET['desde'] ?? null;
        $hasta = $_GET['hasta'] ?? null;
        $tipo  = $_GET['tipo']  ?? null;

        $registros = Asistencia::all(500, 0, $idEmpleado, $desde ?: null, $hasta ?: null, $tipo ?: null);

        // Obtener lista de empleados activos para el filtro
        global $pdo;
        $st = $pdo->prepare("SELECT id_empleado, nombre FROM empleados WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
        $st->execute();
        $empleados = $st->fetchAll(\PDO::FETCH_ASSOC);

        // La vista lista espera $registros y $empleados
        require __DIR__ . '/../../public/views/Asistencias/create.php';
    }

    /**
     * Mostrar formulario para crear una asistencia manual
     * GET: ?controller=asistencia&action=create
     */
    public function create(): void
    {
        requireRole(1);

        // Lista de empleados activos para el combo
        global $pdo;
        $st = $pdo->prepare("SELECT id_empleado, nombre FROM empleados WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
        $st->execute();
        $empleados = $st->fetchAll(\PDO::FETCH_ASSOC);

        // usamos la vista de edición/creación existente, que ahora recibirá $empleados
        require __DIR__ . '/../../public/views/Asistencias/edit.php';
    }

    /**
     * Guardar asistencia manual
     * POST: ?controller=asistencia&action=store
     */
    public function store(): void
    {
        requireRole(1);
        session_start();

        try {
            $idEmpleado = isset($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : 0;
            $fecha = $_POST['fecha'] ?? '';
            $esFalta = isset($_POST['es_falta']) && ((string)$_POST['es_falta'] === '1');
            $horaEntrada = trim((string)($_POST['hora_entrada'] ?? ''));
            $horaSalida  = trim((string)($_POST['hora_salida'] ?? ''));
            $observ      = trim((string)($_POST['observaciones'] ?? ''));

            if ($idEmpleado <= 0) throw new \InvalidArgumentException('Empleado es obligatorio.');
            if ($fecha === '') throw new \InvalidArgumentException('Fecha es obligatoria.');

            if ($esFalta) {
                Asistencia::marcarFalta($idEmpleado, $fecha, 'MANUAL', $observ);
            } else {
                if ($horaEntrada !== '') {
                    Asistencia::registrarEntrada($idEmpleado, $fecha, $horaEntrada, 'MANUAL', $observ);
                }
                if ($horaSalida !== '') {
                    Asistencia::registrarSalida($idEmpleado, $fecha, $horaSalida, 'MANUAL', $observ);
                }
            }

            $_SESSION['flash_success'] = 'Asistencia registrada correctamente.';
            header('Location: index.php?controller=asistencia&action=index');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;
            header('Location: index.php?controller=asistencia&action=create');
            exit;
        }
    }

    /**
     * Mostrar formulario de edición
     * GET: ?controller=asistencia&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=asistencia&action=index');
            exit;
        }

        $registro = Asistencia::findById($id);
        if (!$registro) {
            header('Location: index.php?controller=asistencia&action=index');
            exit;
        }

        // Lista de empleados
        global $pdo;
        $st = $pdo->prepare("SELECT id_empleado, nombre FROM empleados WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
        $st->execute();
        $empleados = $st->fetchAll(\PDO::FETCH_ASSOC);

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/Asistencias/edit.php';
    }

    /**
     * Actualizar asistencia
     * POST: ?controller=asistencia&action=update&id=1
     */
    public function update(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=asistencia&action=index');
            exit;
        }

        try {
            $data = $_POST;
            Asistencia::actualizarManual($id, $data);

            $_SESSION['flash_success'] = 'Asistencia actualizada correctamente.';
            header('Location: index.php?controller=asistencia&action=index');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;
            header('Location: index.php?controller=asistencia&action=edit&id=' . $id);
            exit;
        }
    }
}

