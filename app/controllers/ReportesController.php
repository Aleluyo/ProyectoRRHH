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
        
        // Limpiar buffer de salida para evitar corrupción
        if (ob_get_level()) ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID Nomina', 'Periodo Inicio', 'Periodo Fin', 'Tipo', 
            'ID Empleado', 'Nombre', 'RFC', 'Puesto', 'Area',
            'Percepciones', 'Deducciones', 'Neto'
        ]);

        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;

        $nominas = Nomina::getAllExtended(10000, 0, $fechaInicio, $fechaFin);

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
        
         if (ob_get_level()) ob_end_clean();

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
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;

        $empleados = Empleado::all(10000, 0, null, null, null, null, null, $fechaInicio, $fechaFin); // Traer todos

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
        
         if (ob_get_level()) ob_end_clean();

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
        
         if (ob_get_level()) ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Area', 'Puesto', 'Ubicacion', 'Solicitante', 'Estatus', 'Fecha Publicacion', 'Requisitos']);

        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;

        $vacantes = Vacante::all(10000, 0, null, $fechaInicio, $fechaFin);

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
        
         if (ob_get_level()) ob_end_clean();

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







    public function turnos() {
        require_once __DIR__ . '/../models/Turno.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "turnos_" . date('Y-m-d') . ".csv";

         if (ob_get_level()) ob_end_clean();
        
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


    public function movimientos() {
        require_once __DIR__ . '/../models/Movimiento.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
            redirect('index.php');
        }

        $filename = "movimientos_" . date('Y-m-d') . ".csv";
        
         if (ob_get_level()) ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Empleado', 'Tipo', 'Fecha', 'Motivo', 'Anterior', 'Nuevo', 'Observaciones', 'Registrado']);

        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        $tipo = $_GET['tipo'] ?? null;

        $movs = Movimiento::all(10000, 0, null, $tipo, $fechaInicio, $fechaFin);

        foreach ($movs as $m) {
            fputcsv($output, [
                $m['id_movimiento'],
                $m['nombre_empleado'] ?? $m['id_empleado'],
                $m['tipo_movimiento'],
                $m['fecha_movimiento'],
                $m['motivo'],
                $m['valor_anterior'],
                $m['valor_nuevo'],
                $m['observaciones'],
                $m['fecha_registro']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
