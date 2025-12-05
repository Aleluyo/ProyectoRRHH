<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/Puesto.php';
require_once __DIR__ . '/../middleware/Auth.php';

class EmpleadoController
{
    /**
     * Premenú del módulo de empleados
     * Muestra las 4 opciones principales: Expedientes, Altas, Movimientos, Documentos
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        require __DIR__ . '/../../public/views/empleados/index.php';
    }

    /**
     * Listado de empleados con filtros
     * GET: ?controller=empleado&action=listado
     */
    public function listado(): void
    {
        requireLogin();
        requireRole(1);

        $search = $_GET['q'] ?? null;
        $estado = isset($_GET['estado']) && $_GET['estado'] !== ''
            ? $_GET['estado']
            : null;

        $idEmpresa = isset($_GET['id_empresa']) && $_GET['id_empresa'] !== ''
            ? (int) $_GET['id_empresa']
            : null;

        $idArea = isset($_GET['id_area']) && $_GET['id_area'] !== ''
            ? (int) $_GET['id_area']
            : null;

        $idPuesto = isset($_GET['id_puesto']) && $_GET['id_puesto'] !== ''
            ? (int) $_GET['id_puesto']
            : null;

        // Consulta de empleados
        $empleados = Empleado::all(
            500,
            0,
            $search,
            $estado,
            $idEmpresa,
            $idArea,
            $idPuesto
        );

        // Combos para filtros (ya dejamos listo para mejorar después)
        $empresas = Empresa::all(500, 0, null, true);
        $areas = $idEmpresa !== null
            ? Area::all(1000, 0, null, $idEmpresa, true)
            : [];
        $puestos = $idArea !== null
            ? Puesto::all(1000, 0, null, $idArea, null)
            : [];

        require __DIR__ . '/../../public/views/empleados/list.php';
    }

    public function create(): void
    {
        requireLogin();
        requireRole(1);

        // Más adelante: aquí cargaremos combos de empresa/área/puesto/turno/ubicación
        require __DIR__ . '/../../public/views/empleados/create.php';
    }

    /**
     * Ver detalle completo del empleado (expediente)
     * GET: ?controller=empleado&action=show&id=X
     */
    public function show(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: ' . url('index.php?controller=empleado&action=listado'));
            exit;
        }

        // Obtener datos completos del empleado
        $empleado = Empleado::findById($id);

        if (!$empleado) {
            header('Location: ' . url('index.php?controller=empleado&action=listado'));
            exit;
        }

        // Aquí más adelante cargaremos:
        // - Cuentas bancarias
        // - Contactos de emergencia
        // - Documentos
        // - Historial de movimientos

        require __DIR__ . '/../../public/views/empleados/show.php';
    }

    /**
     * Formulario de edición de empleado
     * GET: ?controller=empleado&action=edit&id=X
     */
    public function edit(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: ' . url('index.php?controller=empleado&action=listado'));
            exit;
        }

        // Obtener datos del empleado
        $empleado = Empleado::findById($id);

        if (!$empleado) {
            header('Location: ' . url('index.php?controller=empleado&action=listado'));
            exit;
        }

        // Cargar combos para el formulario
        $empresas = Empresa::all(500, 0, null, true);
        $areas = Area::all(1000, 0, null, (int) $empleado['id_empresa'], true);
        $puestos = Puesto::all(1000, 0, null, (int) $empleado['id_area'], null);

        require __DIR__ . '/../../public/views/empleados/edit.php';
    }

    /**
     * Procesar actualización de empleado
     * POST: ?controller=empleado&action=update
     */
    public function update(): void
    {
        requireLogin();
        requireRole(1);

        // Validar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('index.php?controller=empleado&action=listado'));
            exit;
        }

        $id = isset($_POST['id_empleado']) ? (int) $_POST['id_empleado'] : 0;

        if ($id <= 0) {
            header('Location: ' . url('index.php?controller=empleado&action=listado'));
            exit;
        }

        // Obtener datos actuales del empleado (para comparar cambios)
        $empleadoActual = Empleado::findById($id);

        if (!$empleadoActual) {
            header('Location: ' . url('index.php?controller=empleado&action=listado'));
            exit;
        }

        // Preparar datos para actualizar
        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'curp' => trim($_POST['curp'] ?? ''),
            'rfc' => trim($_POST['rfc'] ?? ''),
            'nss' => trim($_POST['nss'] ?? ''),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'genero' => $_POST['genero'] ?? null,
            'estado_civil' => trim($_POST['estado_civil'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'id_puesto' => (int) ($_POST['id_puesto'] ?? 0),
            'fecha_ingreso' => $_POST['fecha_ingreso'] ?? null,
            'estado' => $_POST['estado'] ?? 'ACTIVO',
            'fecha_baja' => $_POST['fecha_baja'] ?? null,
        ];

        // Validaciones básicas
        if (empty($data['nombre'])) {
            // TODO: Implementar manejo de errores con sesión
            header('Location: ' . url('index.php?controller=empleado&action=edit&id=' . $id . '&error=nombre_requerido'));
            exit;
        }

        if ($data['id_puesto'] <= 0) {
            header('Location: ' . url('index.php?controller=empleado&action=edit&id=' . $id . '&error=puesto_requerido'));
            exit;
        }

        // Limpiar campos vacíos (convertir a NULL)
        foreach ($data as $key => $value) {
            if ($value === '' || $value === null) {
                $data[$key] = null;
            }
        }

        // Actualizar empleado
        $success = Empleado::update($id, $data);

        if ($success) {
            // TODO: Registrar cambios en empleados_historial
            // Comparar $empleadoActual con $data para detectar cambios importantes
            // (puesto, área, jefe, estado, etc.)

            // Guardar mensaje de éxito en sesión
            $_SESSION['toast_message'] = 'Los datos se actualizaron correctamente';
            $_SESSION['toast_type'] = 'success';

            // Redirigir al expediente
            header('Location: ' . url('index.php?controller=empleado&action=show&id=' . $id));
            exit;
        } else {
            // Guardar mensaje de error en sesión
            $_SESSION['toast_message'] = 'Error al actualizar los datos';
            $_SESSION['toast_type'] = 'error';

            // Redirigir de vuelta al formulario de edición
            header('Location: ' . url('index.php?controller=empleado&action=edit&id=' . $id));
            exit;
        }
    }
}
