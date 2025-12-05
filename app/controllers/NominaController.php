<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/PeriodoNomina.php';
require_once __DIR__ . '/../models/Nomina.php';
require_once __DIR__ . '/../models/NominaDetalle.php';
require_once __DIR__ . '/../models/ConceptoNomina.php';
require_once __DIR__ . '/../models/Empleado.php'; // Asegúrate de que existe y tiene Empleado::all()
require_once __DIR__ . '/../middleware/Auth.php';

class NominaController
{
    public function index(): void
    {
        requireLogin();
        // requireRole(1); // Descomentar si solo admin puede ver

        $periodos = PeriodoNomina::all();
        
        // Vista en app/views/nominas/index.php
        require __DIR__ . '/../views/nominas/index.php';
    }

    public function create(): void
    {
        requireLogin();
        requireRole(1);

        require __DIR__ . '/../views/nominas/create.php';
    }

    /**
     * Generar nómina para un nuevo periodo
     */
    public function store(): void
    {
        requireLogin();
        requireRole(1);

        $tipo = $_POST['tipo'] ?? 'QUINCENAL';
        $fecha_inicio = $_POST['fecha_inicio'] ?? '';
        $fecha_fin = $_POST['fecha_fin'] ?? '';

        if (empty($fecha_inicio) || empty($fecha_fin)) {
            $_SESSION['flash_error'] = 'Las fechas son obligatorias.';
            header('Location: index.php?controller=nomina&action=create');
            exit;
        }

        // 1. Crear el periodo
        $idEmpresa = 1; // HARDCODED por ahora
        $periodoData = [
            'id_empresa' => $idEmpresa,
            'tipo' => $tipo,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ];
        
        if (!PeriodoNomina::create($periodoData)) {
            $_SESSION['flash_error'] = 'Error al crear el periodo. Verifique las fechas.';
            header('Location: index.php?controller=nomina&action=create');
            exit;
        }
        
        global $pdo;
        $idPeriodo = (int)$pdo->lastInsertId();

        // 2. Generar nómina para este periodo
        $this->generatePayroll($idPeriodo, $idEmpresa);

        $_SESSION['flash_success'] = 'Nómina generada exitosamente.';
        header('Location: index.php?controller=nomina&action=show&id=' . $idPeriodo);
        exit;
    }

    /**
     * Acción para regenerar nómina de un periodo existente (vacío)
     */
    public function generate(): void
    {
        requireLogin();
        requireRole(1);

        $idPeriodo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $periodo = PeriodoNomina::findById($idPeriodo);

        if (!$periodo || $periodo['estado'] !== 'ABIERTO') {
            $_SESSION['flash_error'] = 'Periodo no válido o cerrado.';
            header('Location: index.php?controller=nomina&action=index');
            exit;
        }

        // Generar
        // Obtenemos empresa del periodo
        $this->generatePayroll($idPeriodo, (int)$periodo['id_empresa']);

        $_SESSION['flash_success'] = 'Nómina procesada correctamente.';
        header('Location: index.php?controller=nomina&action=show&id=' . $idPeriodo);
        exit;
    }

    /**
     * Lógica central de generación
     */
    private function generatePayroll(int $idPeriodo, int $idEmpresa): void 
    {
        global $pdo;
        // Obtener empleados activos
        $empleados = Empleado::all(1000, 0, null, 'ACTIVO', $idEmpresa);
        
        // Evitar duplicados si ya existen
        // Borramos anteriores del mismo periodo si es recálculo? 
        // Por seguridad, solo insertamos si NO existe.
        
        foreach ($empleados as $emp) {
            $idEmp = (int)$emp['id_empleado'];
            
            if (Nomina::exists($idEmp, $idPeriodo)) {
                continue; // Ya tiene nómina
            }
            
            // Crear registro maestro
            $idNomina = Nomina::create($idEmp, $idPeriodo);
            
            // Obtener salario (SIMULADO: Puestos.salario_base)
            $stmt = $pdo->prepare("SELECT salario_base FROM puestos WHERE id_puesto = ?");
            $stmt->execute([$emp['id_puesto']]);
            $salarioBase = (float)$stmt->fetchColumn(); 
            
            // Lógica simple: Quincenal = salario / 2
            $periodoActual = PeriodoNomina::findById($idPeriodo);
            $factor = ($periodoActual['tipo'] === 'MENSUAL') ? 1 : (($periodoActual['tipo'] === 'SEMANAL') ? 0.25 : 0.5);
            
            $sueldoPeriodo = $salarioBase * $factor;
            $isr = $sueldoPeriodo * 0.16;
            
            NominaDetalle::create($idNomina, 1, $sueldoPeriodo, 'Sueldo Base'); 
            NominaDetalle::create($idNomina, 2, $isr, 'ISR Retenido');
            
            Nomina::updateTotals($idNomina, $sueldoPeriodo, $isr);
        }
    }

    public function show(): void
    {
        requireLogin();
        require_once __DIR__ . '/../helpers/dates.php'; // Cargar helper de fechas
        
        $idPeriodo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $periodo = PeriodoNomina::findById($idPeriodo);
        
        if (!$periodo) {
            $_SESSION['flash_error'] = 'Periodo no encontrado.';
            header('Location: index.php?controller=nomina&action=index');
            exit;
        }
        
        $nominas = Nomina::getByPeriodo($idPeriodo);
        
        require __DIR__ . '/../views/nominas/show.php';
    }

    public function recibo(): void
    {
        requireLogin();
        
        $idNomina = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $nomina = Nomina::findById($idNomina);
        
        if (!$nomina) { 
            die("Recibo no encontrado");
        }

        // Obtener datos de empresa
        require_once __DIR__ . '/../models/Empresa.php';
        // Nomina no tiene id_empresa directo, pero el empleado sí.
        // Ojo: Nomina::findById trae "empresa_id" si lo agregamos al join, o lo sacamos del empleado.
        // El metodo Nomina::findById ya tiene un join con areas y empresas:
        // "INNER JOIN empresas emp ON emp.id_empresa = a.id_empresa" (En Empleado::findById, pero en Nomina::findById no estaba completo el join a empresa)
        // Revisemos Nomina::findById en app/models/Nomina.php
        // En el paso anterior, Nomina::findById no incluía join a empresas explícito para traer todos los datos, solo nombre.
        
        // Haremos un fetch explícito de la empresa para tener RFC y dirección.
        // Necesitamos id_empresa. 
        // Opción A: Modificar Nomina::findById.
        // Opción B: Obtenerlo aquí via Empleado.
        
        $empleado = Empleado::findById((int)$nomina['id_empleado']);
        $empresa = Empresa::findById((int)$empleado['id_empresa']);
        
        $detalles = NominaDetalle::getByNomina($idNomina);
        
        require __DIR__ . '/../views/nominas/recibo.php';
    }
}
