<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php'; // Asumiendo que necesitaremos verificar roles o usuarios

class ReportesController {
    
    public function index() {
        // Verificar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            redirect('login.php');
        }

        // Aquí podríamos cargar datos iniciales o listas para filtros
        // Por ahora, solo renderizamos la vista principal
        
        $pageTitle = 'Reportes y Exportaciones';
        
        require_once __DIR__ . '/../views/reportes/index.php';
    }

    public function nomina() {
        require_once __DIR__ . '/../models/Nomina.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "nomina_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID Nomina', 'Periodo Inicio', 'Periodo Fin', 'Tipo', 
            'ID Empleado', 'Nombre', 'RFC', 'Puesto', 'Area',
            'Percepciones', 'Deducciones', 'Neto'
        ]);

        $nominas = Nomina::getAllExtended(10000, 0);

        foreach ($nominas as $n) {
            fputcsv($output, [
                $n['id_nomina'],
                $n['fecha_inicio'],
                $n['fecha_fin'],
                $n['periodo_tipo'],
                $n['id_empleado'],
                $n['empleado_nombre'],
                $n['rfc'],
                $n['nombre_puesto'],
                $n['nombre_area'],
                number_format((float)$n['total_percepciones'], 2, '.', ''),
                number_format((float)$n['total_deducciones'], 2, '.', ''),
                number_format((float)$n['total_neto'], 2, '.', '')
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function empleados() {
        require_once __DIR__ . '/../models/Empleado.php';
        require_once __DIR__ . '/../models/Empresa.php';
        require_once __DIR__ . '/../models/Area.php';
        require_once __DIR__ . '/../models/Puesto.php';

        // Verificar permisos (Admin)
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "empleados_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($output, [
            'ID', 'Nombre', 'CURP', 'RFC', 'NSS', 
            'Fecha Nacimiento', 'Genero', 'Estado Civil',
            'Telefono', 'Correo', 'Direccion',
            'Fecha Ingreso', 'Estado', 
            'Empresa', 'Area', 'Puesto', 'Ubicacion', 'Turno'
        ]);

        // Obtener datos
        $empleados = Empleado::all(10000, 0); // Traer todos

        foreach ($empleados as $emp) {
            fputcsv($output, [
                $emp['id_empleado'],
                $emp['nombre'],
                $emp['curp'],
                $emp['rfc'],
                $emp['nss'],
                $emp['fecha_nacimiento'] ?? '',
                $emp['genero'] ?? '',
                $emp['estado_civil'] ?? '',
                $emp['telefono'] ?? '',
                $emp['correo'] ?? '',
                $emp['direccion'] ?? '',
                $emp['fecha_ingreso'],
                $emp['estado'],
                $emp['empresa_nombre'] ?? '',
                $emp['nombre_area'] ?? '',
                $emp['nombre_puesto'] ?? '',
                $emp['ubicacion_nombre'] ?? '',
                $emp['turno_nombre'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function asistencias() {
        require_once __DIR__ . '/../models/Asistencias.php';

        // Verificar permisos (Admin)
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "asistencias_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($output, ['ID', 'Empleado', 'Fecha', 'Entrada', 'Salida', 'Tipo', 'Origen', 'Observaciones']);

        // Obtener datos (últimos 30 días por defecto si no hay filtros, o todos)
        // Por simplicidad para el reporte general, traemos los últimos 1000 registros
        $asistencias = Asistencia::all(1000, 0); 

        foreach ($asistencias as $asis) {
            fputcsv($output, [
                $asis['id_asistencia'],
                $asis['nombre_empleado'] ?? 'ID: ' . $asis['id_empleado'],
                $asis['fecha'],
                $asis['hora_entrada'],
                $asis['hora_salida'],
                $asis['tipo'],
                $asis['origen'] ?? '',
                $asis['observaciones']
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function vacantes() {
        require_once __DIR__ . '/../models/Vacante.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "vacantes_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Area', 'Puesto', 'Ubicacion', 'Solicitante', 'Estatus', 'Fecha Publicacion', 'Requisitos']);

        $vacantes = Vacante::all(10000, 0);

        foreach ($vacantes as $v) {
            fputcsv($output, [
                $v['id_vacante'],
                $v['id_area'], 
                $v['id_puesto'],
                $v['id_ubicacion'],
                $v['solicitada_por'],
                $v['estatus'],
                $v['fecha_publicacion'],
                $v['requisitos']
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function candidatos() {
        require_once __DIR__ . '/../models/Candidato.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "candidatos_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Nombre', 'Correo', 'Telefono', 'Fuente', 'CV']);

        $candidatos = Candidato::all(10000, 0);

        foreach ($candidatos as $c) {
            fputcsv($output, [
                $c['id_candidato'],
                $c['nombre'],
                $c['correo'],
                $c['telefono'],
                $c['fuente'],
                $c['cv']
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function areas() {
        require_once __DIR__ . '/../models/Area.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "areas_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Empresa', 'Nombre Area', 'Descripcion', 'Activa']);

        $areas = Area::all(10000, 0);

        foreach ($areas as $a) {
            fputcsv($output, [
                $a['id_area'],
                $a['empresa_nombre'] ?? $a['id_empresa'],
                $a['nombre_area'],
                $a['descripcion'],
                $a['activa'] ? 'SI' : 'NO'
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function puestos() {
        require_once __DIR__ . '/../models/Puesto.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "puestos_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Empresa', 'Area', 'Nombre Puesto', 'Nivel', 'Salario Base', 'Descripcion']);

        $puestos = Puesto::all(10000, 0);

        foreach ($puestos as $p) {
            fputcsv($output, [
                $p['id_puesto'],
                $p['nombre_empresa'] ?? '',
                $p['nombre_area'] ?? $p['id_area'],
                $p['nombre_puesto'],
                $p['nivel'],
                $p['salario_base'],
                $p['descripcion']
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function ubicaciones() {
        require_once __DIR__ . '/../models/Ubicacion.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "ubicaciones_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Nombre', 'Direccion', 'Ciudad', 'Estado', 'Pais', 'Activa']);

        $ubicaciones = Ubicacion::all(10000, 0);

        foreach ($ubicaciones as $u) {
            fputcsv($output, [
                $u['id_ubicacion'],
                $u['nombre'],
                $u['direccion'],
                $u['ciudad'],
                $u['estado_region'],
                $u['pais'],
                $u['activa'] ? 'SI' : 'NO'
            ]);
        }
        
        fclose($output);
        exit;
    }

    public function turnos() {
        require_once __DIR__ . '/../models/Turno.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "turnos_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Nombre', 'Entrada', 'Salida', 'Tolerancia', 'Dias Laborales']);

        $turnos = Turno::all(10000, 0);

        foreach ($turnos as $t) {
            fputcsv($output, [
                $t['id_turno'],
                $t['nombre_turno'],
                $t['hora_entrada'],
                $t['hora_salida'],
                $t['tolerancia_minutos'],
                $t['dias_laborales']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
