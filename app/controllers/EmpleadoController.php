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

    /**
     * Vista de Altas & Reingresos
     * GET: ?controller=empleado&action=altas
     */
    public function altas(): void
    {
        requireLogin();
        requireRole(1);

        // Obtener últimos 10 empleados con fecha_ingreso reciente
        $ultimosIngresos = Empleado::getRecentHires(10);

        require __DIR__ . '/../../public/views/empleados/altas.php';
    }


    public function create(): void
    {
        requireLogin();
        requireRole(1);

        // Cargar combos para el formulario
        $empresas = Empresa::all();
        $areas = Area::all();
        $puestos = Puesto::all();
        $ubicaciones = Ubicacion::all();
        $turnos = Turno::all();

        require __DIR__ . '/../../public/views/empleados/create.php';
    }

    /**
     * Procesar alta de nuevo empleado
     * POST: ?controller=empleado&action=store
     */
    public function store(): void
    {
        requireLogin();
        requireRole(1);

        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('index.php?controller=empleado&action=create'));
            exit;
        }

        // Obtener datos del formulario
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
            'id_empresa' => (int) ($_POST['id_empresa'] ?? 0),
            'id_area' => (int) ($_POST['id_area'] ?? 0),
            'id_puesto' => (int) ($_POST['id_puesto'] ?? 0),
            'fecha_ingreso' => $_POST['fecha_ingreso'] ?? null,
            'estado' => 'ACTIVO', // Por defecto ACTIVO
            'id_ubicacion' => (int) ($_POST['id_ubicacion'] ?? 0),
            'id_turno' => (int) ($_POST['id_turno'] ?? 0),
        ];

        // Validaciones básicas
        if (empty($data['nombre'])) {
            $_SESSION['toast_message'] = 'El nombre del empleado es requerido';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . url('index.php?controller=empleado&action=create'));
            exit;
        }

        if ($data['id_puesto'] === 0) {
            $_SESSION['toast_message'] = 'El puesto es requerido';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . url('index.php?controller=empleado&action=create'));
            exit;
        }

        // Crear empleado
        $idEmpleado = Empleado::create($data);

        if ($idEmpleado) {
            // Implementación de Registro en movimientos (Integración BD Changes)
            require_once __DIR__ . '/../models/Movimiento.php';
            Movimiento::create([
                'id_empleado' => $idEmpleado,
                'tipo_movimiento' => 'ALTA',
                'fecha_movimiento' => date('Y-m-d'),
                'motivo' => 'Contratación inicial',
                'observaciones' => 'Registro desde sistema administrativo',
                'valor_anterior' => null,
                'valor_nuevo' => 'ACTIVO',
                'autorizado_por' => $_SESSION['user_id'] ?? 1 // Fallback a admin si no hay sesión (caso raro)
            ]);

            $_SESSION['toast_message'] = 'Empleado registrado correctamente';
            $_SESSION['toast_type'] = 'success';

            // Redirigir al expediente del nuevo empleado
            header('Location: ' . url('index.php?controller=empleado&action=show&id=' . $idEmpleado));
            exit;
        } else {
            $_SESSION['toast_message'] = 'Error al registrar el empleado';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . url('index.php?controller=empleado&action=create'));
            exit;
        }
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

        // Cargar documentos del empleado
        require_once __DIR__ . '/../models/EmpleadoDocumento.php';
        $documentos = EmpleadoDocumento::porEmpleado($id);
        
        // Cargar historial de movimientos
        require_once __DIR__ . '/../models/Movimiento.php';
        $movimientos = Movimiento::historialEmpleado($id);
        
        // Cargar información bancaria
        require_once __DIR__ . '/../models/EmpleadoBanco.php';
        $cuentasBancarias = EmpleadoBanco::porEmpleado($id);
        
        // Cargar contactos de emergencia
        require_once __DIR__ . '/../models/EmpleadoContacto.php';
        $contactos = EmpleadoContacto::porEmpleado($id);

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

            require_once __DIR__ . '/../models/Movimiento.php';
            require_once __DIR__ . '/../models/Puesto.php';
            $userId = $_SESSION['user_id'] ?? 1;
            $today = date('Y-m-d');

            // Detectar cambios que requieren registrar movimientos
            
            // 1. Detectar Cambio de Puesto (y potencialmente de área)
            if ($empleadoActual['id_puesto'] != $data['id_puesto']) {
                // Obtener el área del puesto anterior y del nuevo puesto
                $puestoAnterior = Puesto::findById($empleadoActual['id_puesto']);
                $puestoNuevo = Puesto::findById($data['id_puesto']);
                
                // Si cambió el área, registrar cambio de área primero
                if ($puestoAnterior && $puestoNuevo && $puestoAnterior['id_area'] != $puestoNuevo['id_area']) {
                    Movimiento::cambiarArea(
                        $id, 
                        $puestoNuevo['id_area'],
                        $today,
                        'Reasignación Administrativa por cambio de puesto',
                        'Cambio realizado desde edición de perfil',
                        $userId
                    );
                }
                
                // Registrar cambio de puesto
                Movimiento::cambiarPuesto(
                    $id, 
                    $data['id_puesto'],
                    $today,
                    'Promoción o Cambio Lateral',
                    'Cambio realizado desde edición de perfil',
                    $userId
                );
            }
            
            // 3. Detectar Baja
            // Si el estado cambió a BAJA y antes no lo estaba
            if ($data['estado'] === 'BAJA' && $empleadoActual['estado'] !== 'BAJA') {
                $fechaBaja = $data['fecha_baja'] ?? $today;
                Movimiento::registrarBaja(
                    $id,
                    $fechaBaja,
                    'Baja Administrativa',
                    $userId,
                    'Registrado desde edición de perfil'
                );
            }
            
            // 4. Detectar Reingreso
             if ($data['estado'] === 'ACTIVO' && $empleadoActual['estado'] === 'BAJA') {
                 Movimiento::create([
                    'id_empleado' => $id,
                    'tipo_movimiento' => 'REINGRESO',
                    'fecha_movimiento' => $today,
                    'motivo' => 'Reingreso Laboral',
                    'observaciones' => 'Reactivación de expediente',
                    'valor_anterior' => 'BAJA',
                    'valor_nuevo' => 'ACTIVO',
                    'autorizado_por' => $userId
                ]);
             }

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
