<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/Puesto.php';
require_once __DIR__ . '/../middleware/Auth.php';

class EmpleadoController
{
    public function index(): void
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

    // store(), edit(), update(), show() los llenamos en la siguiente fase
}
