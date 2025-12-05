<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Movimiento.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/Puesto.php';
require_once __DIR__ . '/../middleware/Auth.php';

class MovimientoController
{
    /**
     * Listado de movimientos
     * GET: ?controller=movimiento&action=listado
     */
    public function listado(): void
    {
        requireLogin();
        requireRole(1);

        $tipoMovimiento = isset($_GET['tipo']) && $_GET['tipo'] !== ''
            ? $_GET['tipo']
            : null;

        $idEmpleado = isset($_GET['id_empleado']) && $_GET['id_empleado'] !== ''
            ? (int) $_GET['id_empleado']
            : null;

        $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== ''
            ? $_GET['fecha_inicio']
            : null;

        $fechaFin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== ''
            ? $_GET['fecha_fin']
            : null;

        // Consulta de movimientos
        $movimientos = Movimiento::all(
            limit: 500,
            offset: 0,
            tipoMovimiento: $tipoMovimiento,
            idEmpleado: $idEmpleado,
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin
        );

        $total = Movimiento::count($tipoMovimiento, $idEmpleado, $fechaInicio, $fechaFin);

        // Obtener empleados para filtro
        $empleados = Empleado::all(limit: 1000, estado: 'ACTIVO');

        // Tipos de movimiento
        $tiposMovimiento = Movimiento::tiposMovimiento();

        require __DIR__ . '/../../public/views/empleados/movimientos/list.php';
    }

    /**
     * Muestra formulario para crear nuevo movimiento
     * GET: ?controller=movimiento&action=crear
     */
    public function crear(): void
    {
        requireLogin();
        requireRole(1);

        // Obtener empleados activos
        $empleados = Empleado::all(limit: 1000, estado: 'ACTIVO');
        $areas = Area::all();
        $puestos = Puesto::all();
        $tiposMovimiento = Movimiento::tiposMovimiento();

        require __DIR__ . '/../../public/views/empleados/movimientos/create.php';
    }

    /**
     * Procesa el registro de un nuevo movimiento
     * POST: ?controller=movimiento&action=guardar
     */
    public function guardar(): void
    {
        requireLogin();
        requireRole(1);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=movimiento&action=listado');
            exit;
        }

        try {
            $idEmpleado = (int) ($_POST['id_empleado'] ?? 0);
            $tipoMovimiento = $_POST['tipo_movimiento'] ?? '';
            $fechaMovimiento = $_POST['fecha_movimiento'] ?? '';
            $motivo = trim($_POST['motivo'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '') ?: null;
            $autorizadoPor = $_SESSION['user_id'] ?? 0;

            // Validaciones básicas
            if ($idEmpleado <= 0) {
                throw new Exception("Debe seleccionar un empleado");
            }

            if (empty($tipoMovimiento)) {
                throw new Exception("Debe seleccionar un tipo de movimiento");
            }

            if (empty($fechaMovimiento)) {
                throw new Exception("Debe ingresar la fecha del movimiento");
            }

            if (empty($motivo)) {
                throw new Exception("Debe ingresar el motivo del movimiento");
            }

            // Procesar según el tipo de movimiento
            $idMovimiento = 0;

            switch ($tipoMovimiento) {
                case 'BAJA':
                    $idMovimiento = Movimiento::registrarBaja(
                        $idEmpleado,
                        $fechaMovimiento,
                        $motivo,
                        $autorizadoPor,
                        $observaciones
                    );
                    break;

                case 'CAMBIO_AREA':
                    $nuevaArea = (int) ($_POST['valor_nuevo'] ?? 0);
                    if ($nuevaArea <= 0) {
                        throw new Exception("Debe seleccionar el área nueva");
                    }
                    $idMovimiento = Movimiento::cambiarArea(
                        $idEmpleado,
                        $nuevaArea,
                        $fechaMovimiento,
                        $motivo,
                        $observaciones,
                        $autorizadoPor
                    );
                    break;

                case 'CAMBIO_PUESTO':
                    $nuevoPuesto = (int) ($_POST['valor_nuevo'] ?? 0);
                    if ($nuevoPuesto <= 0) {
                        throw new Exception("Debe seleccionar el puesto nuevo");
                    }
                    $idMovimiento = Movimiento::cambiarPuesto(
                        $idEmpleado,
                        $nuevoPuesto,
                        $fechaMovimiento,
                        $motivo,
                        $observaciones,
                        $autorizadoPor
                    );
                    break;

                case 'CAMBIO_JEFE':
                    $nuevoJefe = isset($_POST['valor_nuevo']) && $_POST['valor_nuevo'] !== ''
                        ? (int) $_POST['valor_nuevo']
                        : null;
                    $idMovimiento = Movimiento::cambiarJefeInmediato(
                        $idEmpleado,
                        $nuevoJefe,
                        $fechaMovimiento,
                        $motivo,
                        $observaciones,
                        $autorizadoPor
                    );
                    break;

                default:
                    throw new Exception("Tipo de movimiento no válido");
            }

            $_SESSION['mensaje'] = "Movimiento registrado exitosamente";
            $_SESSION['tipo_mensaje'] = 'success';
            header("Location: ?controller=movimiento&action=ver&id={$idMovimiento}");
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ?controller=movimiento&action=crear');
            exit;
        }
    }

    /**
     * Muestra el detalle de un movimiento
     * GET: ?controller=movimiento&action=ver&id=123
     */
    public function ver(): void
    {
        requireLogin();
        requireRole(1);

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            header('Location: ?controller=movimiento&action=listado');
            exit;
        }

        $movimiento = Movimiento::find($id);

        if (!$movimiento) {
            $_SESSION['mensaje'] = "Movimiento no encontrado";
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ?controller=movimiento&action=listado');
            exit;
        }

        require __DIR__ . '/../../public/views/empleados/movimientos/show.php';
    }

    /**
     * Muestra el historial de movimientos de un empleado
     * GET: ?controller=movimiento&action=historial&id_empleado=123
     */
    public function historial(): void
    {
        requireLogin();
        requireRole(1);

        $idEmpleado = (int) ($_GET['id_empleado'] ?? 0);

        if ($idEmpleado <= 0) {
            header('Location: ?controller=empleado&action=listado');
            exit;
        }

        $empleado = Empleado::findById($idEmpleado);

        if (!$empleado) {
            $_SESSION['mensaje'] = "Empleado no encontrado";
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ?controller=empleado&action=listado');
            exit;
        }

        $movimientos = Movimiento::historialEmpleado($idEmpleado);

        require __DIR__ . '/../../public/views/empleados/movimientos/historial.php';
    }

    /**
     * API: Obtiene información del empleado para prellenar formulario
     * GET: ?controller=movimiento&action=obtenerDatosEmpleado&id=123
     */
    public function obtenerDatosEmpleado(): void
    {
        requireLogin();
        requireRole(1);

        header('Content-Type: application/json');

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        $empleado = Empleado::findById($id);

        if (!$empleado) {
            echo json_encode(['error' => 'Empleado no encontrado']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'empleado' => [
                'id_empleado' => $empleado['id_empleado'],
                'nombre' => $empleado['nombre'],
                'curp' => $empleado['curp'],
                'estado' => $empleado['estado'],
                'id_empresa' => $empleado['id_empresa'] ?? null,
                'empresa' => $empleado['razon_social'] ?? 'N/A',
                'id_area' => $empleado['id_area'] ?? null,
                'area' => $empleado['nombre_area'] ?? 'N/A',
                'id_puesto' => $empleado['id_puesto'] ?? null,
                'puesto' => $empleado['nombre_puesto'] ?? 'N/A',
                'id_jefe' => $empleado['id_jefe'] ?? null,
                'jefe' => $empleado['jefe_inmediato'] ?? 'Sin jefe'
            ]
        ]);
        exit;
    }
}
