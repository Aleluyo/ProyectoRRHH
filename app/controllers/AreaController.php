<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../middleware/Auth.php'; 

/**
 * Controlador de Áreas (estructura organizacional).
 * Acciones: listar, crear, guardar, editar, actualizar, desactivar.
 */
class AreaController
{
    /**
     * Lista de áreas.
     * Filtros opcionales: q (búsqueda), id_empresa, solo_activas.
     */
    public function index(): void
    {
        // Solo usuarios logueados
        requireLogin();
        requireRole(1);

        $search     = $_GET['q']          ?? null;
        $idEmpresa  = isset($_GET['id_empresa']) && $_GET['id_empresa'] !== ''
                        ? (int)$_GET['id_empresa']
                        : null;
        $soloActivas = isset($_GET['solo_activas']) && $_GET['solo_activas'] === '1'
                        ? true
                        : null;

        $page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        // Áreas (con joins a empresa y área padre)
        $areas = Area::all($perPage, $offset, $search, $idEmpresa, $soloActivas);

        // Para combos de filtro
        $empresas = Empresa::all(1000, 0);

        // Filtros actuales para la vista
        $filtros = [
            'q'            => $search,
            'id_empresa'   => $idEmpresa,
            'solo_activas' => $soloActivas,
            'page'         => $page,
        ];

        require __DIR__ . '/../../public/views/organizacional/areas/list.php';
    }

    /**
     * Formulario de nueva área.
     */
    public function create(): void
    {
        // Solo admin (ej. rol 1)
        requireRole(1);

        $empresas = Empresa::all(1000, 0, null, true);

        // Áreas activas agrupadas por empresa para el combo "Área padre"
        $areasPorEmpresa = [];
        foreach ($empresas as $emp) {
            $idEmp = (int)$emp['id_empresa'];
            $areasPorEmpresa[$idEmp] = Area::getByEmpresa($idEmp, true); // true = solo activas
        }

        // Datos antiguos en caso de error anterior
        $old = $_SESSION['old_area'] ?? [];
        unset($_SESSION['old_area']);

        require __DIR__ . '/../../public/views/organizacional/areas/create.php';
    }

    /**
     * Guarda un área nueva (POST).
     */
    public function store(): void
    {
        // Solo admin (ej. rol 1)
        requireRole(1);

        $activaPost = $_POST['activa'] ?? '1';

        $data = [
            'id_empresa'    => $_POST['id_empresa']    ?? 0,
            'id_area_padre' => $_POST['id_area_padre'] ?? null,
            'nombre_area'   => $_POST['nombre_area']   ?? '',
            'descripcion'   => $_POST['descripcion']   ?? '',
            'activa'        => ($activaPost === '1') ? 1 : 0,
        ];

        try {
            Area::create($data);
            $_SESSION['flash_success'] = 'Área creada correctamente.';
            header('Location: index.php?controller=area&action=index');
            exit;
        } catch (\Throwable $e) {
            // Guardar datos viejos y mensaje de error
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_area']    = $data;
            header('Location: index.php?controller=area&action=create');
            exit;
        }
    }

    /**
     * Formulario de edición de un área.
     */
    public function edit(): void
    {
        // Solo admin
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID de área inválido.';
            header('Location: index.php?controller=area&action=index');
            exit;
        }

        $area = Area::findById($id);
        if (!$area) {
            $_SESSION['flash_error'] = 'El área no existe.';
            header('Location: index.php?controller=area&action=index');
            exit;
        }

        $empresas = Empresa::all(1000, 0);

        $empresaEsInactiva = false;
        if (!empty($area['id_empresa'])) {
            $empresaActual = Empresa::findById((int)$area['id_empresa']);
            if ($empresaActual && isset($empresaActual['activa']) && (int)$empresaActual['activa'] === 0) {
                $empresaEsInactiva = true;
            }
        }
        
        // Áreas agrupadas por empresa para el combo de "Área padre"
        $areasPorEmpresa = [];
        foreach ($empresas as $emp) {
            $idEmp = (int)$emp['id_empresa'];
            $areasPorEmpresa[$idEmp] = Area::getByEmpresa($idEmp, true);
        }

        // Datos antiguos si falló actualización
        $old = $_SESSION['old_area'] ?? null;
        unset($_SESSION['old_area']);

        require __DIR__ . '/../../public/views/organizacional/areas/edit.php';
    }

    /**
     * Actualiza un área existente (POST).
     */
    public function update(): void
    {
        // Solo admin
        requireRole(1);

        $id = isset($_POST['id_area']) ? (int)$_POST['id_area'] : 0;
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID de área inválido.';
            header('Location: index.php?controller=area&action=index');
            exit;
        }

        $activaPost = $_POST['activa'] ?? '0';

        $data = [
            'id_area_padre' => $_POST['id_area_padre'] ?? null,
            'nombre_area'   => $_POST['nombre_area']   ?? '',
            'descripcion'   => $_POST['descripcion']   ?? '',
            'activa'        => ($activaPost === '1') ? 1 : 0,
        ];

        try {
            Area::update($id, $data);
            $_SESSION['flash_success'] = 'Área actualizada correctamente.';
            header('Location: index.php?controller=area&action=index');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_area']    = $data;
            header('Location: index.php?controller=area&action=edit&id=' . $id);
            exit;
        }
    }

    /**
     * Desactiva/reactiva un área.
     */
    public function toggleActive(): void
    {
        // Solo admin
        requireRole(1);

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $active = isset($_POST['activa']) ? (bool)$_POST['activa'] : false;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'ID inválido.']);
            return;
        }

        try {
            Area::setActive($id, $active);
            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * “Eliminar” área.
     * Si no se quiere borrar físicamente, solo desactiva la área.
     */
    public function delete(): void
    {
        // Solo admin
        requireRole(1);

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'ID inválido.']);
            return;
        }

        try {
            // OJO: si se decide borrado físico, se debe crear Area::delete()
            Area::setActive($id, false);
            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
        }
    }
}
