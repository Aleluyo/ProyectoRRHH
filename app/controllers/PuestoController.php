<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Puesto.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../middleware/Auth.php';

class PuestoController
{
    /** Niveles válidos del enum */
    private const NIVELES = ['OPERATIVO', 'SUPERVISOR', 'GERENCIAL', 'DIRECTIVO'];

    /**
     * Lista de puestos
     * GET: ?controller=puesto&action=index&q=texto&id_area=1&nivel=OPERATIVO
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search = $_GET['q']        ?? null;
        $idArea = isset($_GET['id_area']) ? (int)$_GET['id_area'] : null;
        $nivel  = $_GET['nivel']    ?? null;

        $puestos = Puesto::all(500, 0, $search, $idArea, $nivel);

        // Niveles disponibles para filtros o selects en la vista
        $niveles = self::NIVELES;

        $areas = Area::allWithEmpresa(1000, 0);

        // Variables disponibles en la vista:
        // $puestos, $search, $idArea, $nivel, $niveles, $areas
        require __DIR__ . '/../../public/views/organizacional/puestos/list.php';
    }

    /**
     * Mostrar formulario de nuevo puesto
     * GET: ?controller=puesto&action=create
     */
    public function create(): void
    {
        requireRole(1);

        $errors  = [];
        $old     = [];

        $niveles = self::NIVELES;
        $areas   = Area::getActivasConEmpresaActiva();

        // $niveles y $areas disponibles en la vista
        require __DIR__ . '/../../public/views/organizacional/puestos/create.php';
    }

    /**
     * Guardar nuevo puesto
     * POST: ?controller=puesto&action=store
     */
    public function store(): void
    {
        requireRole(1);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $data = [
                'id_area'       => $_POST['id_area']       ?? 0,
                'nombre_puesto' => $_POST['nombre_puesto'] ?? '',
                'nivel'         => $_POST['nivel']         ?? 'OPERATIVO',
                'salario_base'  => $_POST['salario_base']  ?? null,
                'descripcion'   => $_POST['descripcion']   ?? '',
            ];

            $id = Puesto::create($data);

            $_SESSION['flash_success'] = 'Puesto creado correctamente.';
            header('Location: index.php?controller=puesto&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;

            header('Location: index.php?controller=puesto&action=create');
            exit;
        }
    }

    /**
     * Mostrar formulario de edición
     * GET: ?controller=puesto&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=puesto&action=index');
            exit;
        }

        $puesto = Puesto::findById($id);

        if (!$puesto) {
            header('Location: index.php?controller=puesto&action=index');
            exit;
        }

        $errors  = [];
        $niveles = self::NIVELES;

        $areas = Area::allWithEmpresa(1000, 0);

        // $puesto, $niveles, $areas disponibles en la vista
        require __DIR__ . '/../../public/views/organizacional/puestos/edit.php';
    }

    /**
     * Actualizar puesto
     * POST: ?controller=puesto&action=update&id=1
     */
    public function update(): void
    {
        requireRole(1);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=puesto&action=index');
            exit;
        }

        try {
            $data = [
                'id_area'       => $_POST['id_area']       ?? 0,
                'nombre_puesto' => $_POST['nombre_puesto'] ?? '',
                'nivel'         => $_POST['nivel']         ?? 'OPERATIVO',
                'salario_base'  => $_POST['salario_base']  ?? null,
                'descripcion'   => $_POST['descripcion']   ?? '',
            ];

            Puesto::update($id, $data);

            $_SESSION['flash_success'] = 'Puesto actualizado correctamente.';
            header('Location: index.php?controller=puesto&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;

            header('Location: index.php?controller=puesto&action=edit&id=' . $id);
            exit;
        }
    }

    /**
     * Eliminar puesto (DELETE físico)
     * GET o POST: ?controller=puesto&action=delete&id=1
     */
    public function delete(): void
    {
        requireRole(1);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            try {
                Puesto::delete($id);
                $_SESSION['flash_success'] = 'Puesto eliminado correctamente.';
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
        }

        header('Location: index.php?controller=puesto&action=index');
        exit;
    }
}
