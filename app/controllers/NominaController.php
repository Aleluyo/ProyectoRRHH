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
        
        $view = $_GET['view'] ?? 'active'; // active | archived
        $showArchived = ($view === 'archived');

        $periodos = PeriodoNomina::all($showArchived);
        
        require __DIR__ . '/../views/nominas/index.php';
    }

    public function archive(): void
    {
        requireLogin();
        requireRole(1);

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            PeriodoNomina::archive($id);
            $_SESSION['flash_success'] = 'Nómina archivada correctamente.';
        }
        header('Location: index.php?controller=nomina&action=index');
        exit;
    }

    public function restore(): void
    {
        requireLogin();
        requireRole(1);

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            PeriodoNomina::restore($id);
            $_SESSION['flash_success'] = 'Nómina restaurada correctamente.';
        }
        header('Location: index.php?controller=nomina&action=index&view=archived');
        exit;
    }

    public function close(): void
    {
        requireLogin();
        requireRole(1);

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            // Al cerrar, el estado pasa a CERRADO.
            // Según la lógica de PeriodoNomina::all, las CERRADO se muestran en "Archivadas".
            // Por lo tanto, cerrar = archivar visualmente.
            PeriodoNomina::close($id);
            $_SESSION['flash_success'] = 'Nómina cerrada correctamente (movida a histórico).';
        }
        header('Location: index.php?controller=nomina&action=index');
        exit;
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
    /**
     * Mostrar formulario de edición de nómina
     */
    public function edit(): void
    {
        requireLogin();
        requireRole(1); // Solo admin

        $idNomina = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $nomina = Nomina::findById($idNomina);

        if (!$nomina) {
            $_SESSION['flash_error'] = 'Nómina no encontrada.';
            header('Location: index.php?controller=nomina&action=index');
            exit;
        }

        // Obtener detalles actuales
        $detalles = NominaDetalle::getByNomina($idNomina);
        
        // Obtener catálogo de conceptos para el select
        $conceptos = ConceptoNomina::all();
        
        // Separar conceptos para facilitar la vista
        $percepcionesDisp = ConceptoNomina::getByTipo('PERCEPCION');
        $deduccionesDisp = ConceptoNomina::getByTipo('DEDUCCION');
        
        // Obtener salario base para cálculos de referencia
        // El salario base no viene directo en Nomina::findById con el query actual, 
        // pero podemos sacarlo del puesto si se hizo el join, o una consulta extra.
        // En findById ya tenemos p.nombre_puesto, pero no salario.
        // Haremos consulta rápida del salario del empleado.
        global $pdo;
        $stmt = $pdo->prepare("SELECT salario_base FROM puestos p INNER JOIN empleados e ON e.id_puesto = p.id_puesto WHERE e.id_empleado = ?");
        $stmt->execute([$nomina['id_empleado']]);
        $salarioBase = (float)$stmt->fetchColumn(); 

        require __DIR__ . '/../views/nominas/edit.php';
    }

    /**
     * Procesar actualización de nómina
     */
    public function update(): void
    {
        requireLogin();
        requireRole(1);

        $idNomina = $_POST['id_nomina'] ?? 0;
        $idNomina = (int)$idNomina;

        if ($idNomina <= 0) {
            $_SESSION['flash_error'] = 'ID de nómina inválido.';
            header('Location: index.php?controller=nomina&action=index');
            exit;
        }

        // Datos del formulario
        // Esperamos arrays: conceptos_ids[], montos[], observaciones[] ??
        // O mejor: items[ index ][ id_concepto ], items[ index ][ monto ]
        
        // Estructura sugerida del form:
        // name="detalles[index][id_concepto]"
        // name="detalles[index][monto]"
        
        $detalles = $_POST['detalles'] ?? [];

        global $pdo;
        try {
            $pdo->beginTransaction();

            // 1. Limpiar detalles anteriores
            NominaDetalle::deleteByNomina($idNomina);

            $totalPercepciones = 0.0;
            $totalDeducciones = 0.0;
            
            $conceptosAll = ConceptoNomina::all();
            $tipoConcepto = []; 
            $nombresConcepto = [];
            foreach($conceptosAll as $c) {
                $tipoConcepto[$c['id_concepto']] = $c['tipo'];
                $nombresConcepto[$c['id_concepto']] = $c['nombre'];
            }

            // 2. Insertar nuevos detalles
            if (is_array($detalles)) {
                foreach ($detalles as $item) {
                    $idConcepto = (int)($item['id_concepto'] ?? 0);
                    $monto = (float)($item['monto'] ?? 0);
                    
                    if ($idConcepto > 0 && $monto >= 0) {
                        $nombre = $nombresConcepto[$idConcepto] ?? '';
                        NominaDetalle::create($idNomina, $idConcepto, $monto, $nombre);
                        
                        $tipo = $tipoConcepto[$idConcepto] ?? '';
                        if ($tipo === 'PERCEPCION') {
                            $totalPercepciones += $monto;
                        } elseif ($tipo === 'DEDUCCION') {
                            $totalDeducciones += $monto;
                        }
                    }
                }
            }

            // 3. Actualizar totales
            Nomina::updateTotals($idNomina, $totalPercepciones, $totalDeducciones);

            $pdo->commit();
            
            $_SESSION['flash_success'] = 'Nómina actualizada correctamente.';
            header('Location: index.php?controller=nomina&action=show&id=' . $_POST['id_periodo']); // Necesitamos id_periodo para volver
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = 'Error al actualizar: ' . $e->getMessage();
            header("Location: index.php?controller=nomina&action=edit&id=$idNomina");
            exit;
        }
    }

    /**
     * Formulario para agregar manualmente un empleado a la nómina
     */
    public function createEntry(): void
    {
        requireLogin();
        requireRole(1);
        
        $idPeriodo = isset($_GET['id_periodo']) ? (int)$_GET['id_periodo'] : 0;
        $periodo = PeriodoNomina::findById($idPeriodo);
        
        if (!$periodo || $periodo['estado'] !== 'ABIERTO') {
            $_SESSION['flash_error'] = 'Periodo no válido o cerrado.';
            header('Location: index.php?controller=nomina&action=index');
            exit;
        }

        // Obtener empleados que NO tienen nómina en este periodo
        // Consultamos todos y filtramos
        global $pdo;
        $sql = "SELECT e.id_empleado, e.nombre, p.nombre_puesto 
                FROM empleados e 
                LEFT JOIN puestos p ON p.id_puesto = e.id_puesto
                WHERE e.estado = 'ACTIVO' 
                AND e.id_empleado NOT IN (SELECT id_empleado FROM nomina_empleado WHERE id_periodo = ?)
                ORDER BY e.nombre";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idPeriodo]);
        $empleadosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Catálogos
        $conceptos = ConceptoNomina::all();
        $percepcionesDisp = ConceptoNomina::getByTipo('PERCEPCION');
        $deduccionesDisp = ConceptoNomina::getByTipo('DEDUCCION');

        require __DIR__ . '/../views/nominas/create_entry.php';
    }

    /**
     * Guardar entrada manual de nómina
     */
    public function storeEntry(): void
    {
        requireLogin();
        requireRole(1);

        $idPeriodo = (int)($_POST['id_periodo'] ?? 0);
        $idEmpleado = (int)($_POST['id_empleado'] ?? 0);
        
        if ($idPeriodo <= 0 || $idEmpleado <= 0) {
            $_SESSION['flash_error'] = 'Datos inválidos.';
            header('Location: index.php?controller=nomina&action=index');
            exit;
        }

        // Verificar si ya existe (doble check)
        if (Nomina::exists($idEmpleado, $idPeriodo)) {
             $_SESSION['flash_error'] = 'El empleado ya tiene nómina en este periodo.';
             header("Location: index.php?controller=nomina&action=show&id=$idPeriodo");
             exit;
        }

        $detalles = $_POST['detalles'] ?? [];

        global $pdo;
        try {
            $pdo->beginTransaction();

            // 1. Crear Nomina Maestra
            $idNomina = Nomina::create($idEmpleado, $idPeriodo);

            $totalPercepciones = 0.0;
            $totalDeducciones = 0.0;
            
            // Catálogo tipos
            $conceptosAll = ConceptoNomina::all();
            $tipoConcepto = []; 
            $nombresConcepto = [];
            foreach($conceptosAll as $c) {
                $tipoConcepto[$c['id_concepto']] = $c['tipo'];
                $nombresConcepto[$c['id_concepto']] = $c['nombre'];
            }

            // 2. Insertar detalles
            if (is_array($detalles)) {
                foreach ($detalles as $item) {
                    $idConcepto = (int)($item['id_concepto'] ?? 0);
                    $monto = (float)($item['monto'] ?? 0);
                    
                    if ($idConcepto > 0 && $monto >= 0) {
                        $nombre = $nombresConcepto[$idConcepto] ?? '';
                        NominaDetalle::create($idNomina, $idConcepto, $monto, $nombre);
                        
                        $tipo = $tipoConcepto[$idConcepto] ?? '';
                        if ($tipo === 'PERCEPCION') {
                            $totalPercepciones += $monto;
                        } elseif ($tipo === 'DEDUCCION') {
                            $totalDeducciones += $monto;
                        }
                    }
                }
            }

            // 3. Actualizar totales
            Nomina::updateTotals($idNomina, $totalPercepciones, $totalDeducciones);

            $pdo->commit();
            
            $_SESSION['flash_success'] = 'Empleado agregado a la nómina correctamente.';
            header('Location: index.php?controller=nomina&action=show&id=' . $idPeriodo);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = 'Error al crear: ' . $e->getMessage();
            header("Location: index.php?controller=nomina&action=createEntry&id_periodo=$idPeriodo");
            exit;
        }
    }
}
