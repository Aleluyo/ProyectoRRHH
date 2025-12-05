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

        $search = $_GET['q'] ?? null;
        $idArea = isset($_GET['id_area']) && $_GET['id_area'] !== ''
            ? (int)$_GET['id_area']
            : null;
        $nivel  = $_GET['nivel'] ?? null;

        //por defecto solo activos; si showInactive=1 → también inactivos
        $showInactive = isset($_GET['showInactive']) && $_GET['showInactive'] === '1';

        // Si NO se marca el checkbox → solo activos
        // Si se marca → null (sin filtro de activos)
        $onlyActive = $showInactive ? null : true;

        $puestos = Puesto::all(500, 0, $search, $idArea, $nivel, $onlyActive);

        // Niveles disponibles para filtros o selects en la vista
        $niveles = self::NIVELES;

        // Áreas para filtros (si las usas en la vista)
        $areas = Area::allWithEmpresa(1000, 0);

        // Variables disponibles en la vista:
        // $puestos, $search, $idArea, $nivel, $niveles, $areas, $showInactive
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
                'activa'        => 1,
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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        $niveles = self::NIVELES;

        $idAreaActual = (int)($puesto['id_area'] ?? 0);

        // 1) Base: solo áreas activas de empresas activas
        $areas = Area::getActivasConEmpresaActiva();

        // 2) Cargar área actual (con empresa) aunque esté inactiva
        $areaActual        = null;
        $areaEsInactiva    = false;
        $empresaEsInactiva = false;

        if ($idAreaActual > 0) {
            $areaActual = Area::findWithEmpresa($idAreaActual);

            if ($areaActual) {
                $areaEsInactiva    = isset($areaActual['activa_area'])    && (int)$areaActual['activa_area']    === 0;
                $empresaEsInactiva = isset($areaActual['activa_empresa']) && (int)$areaActual['activa_empresa'] === 0;

                // Ver si ya está incluida (porque podría seguir activa)
                $yaIncluida = false;
                foreach ($areas as $a) {
                    if ((int)($a['id_area'] ?? 0) === $idAreaActual) {
                        $yaIncluida = true;
                        break;
                    }
                }

                // Si NO está incluida, se agrega para que aparezca en el combo
                if (!$yaIncluida) {
                    $areas[] = [
                        'id_area'        => $areaActual['id_area'],
                        'nombre_area'    => $areaActual['nombre_area'],
                        'id_empresa'     => $areaActual['id_empresa'],
                        'nombre_empresa' => $areaActual['nombre_empresa'],
                    ];
                }
            }
        }
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

        // Cargamos el puesto actual para conocer su área original
        $puestoActual = Puesto::findById($id);
        if (!$puestoActual) {
            header('Location: index.php?controller=puesto&action=index');
            exit;
        }

        $idAreaActual = (int)($puestoActual['id_area'] ?? 0);
        $activaActual   = (int)($puestoActual['activa']   ?? 1);

        try {
            $idAreaNueva = isset($_POST['id_area']) ? (int)$_POST['id_area'] : 0;

            if ($idAreaNueva <= 0) {
                throw new \InvalidArgumentException('El área es obligatoria.');
            }

            // Área destino con info de empresa
            $areaDestino = Area::findWithEmpresa($idAreaNueva);
            if (!$areaDestino) {
                throw new \InvalidArgumentException('El área seleccionada no existe.');
            }

            $areaDestinoInactiva    = isset($areaDestino['activa_area'])    && (int)$areaDestino['activa_area']    === 0;
            $empresaDestinoInactiva = isset($areaDestino['activa_empresa']) && (int)$areaDestino['activa_empresa'] === 0;

            // Regla 1.2:
            // - Si NO cambió de área → permitimos aunque esté inactiva (históricos)
            // - Si SÍ cambió de área → solo si área y empresa destino están activas
            if ($idAreaNueva !== $idAreaActual && ($areaDestinoInactiva || $empresaDestinoInactiva)) {
                throw new \InvalidArgumentException(
                    'Solo se pueden asignar puestos a áreas activas de empresas activas.'
                );
            }

            $data = [
                'id_area'       => $idAreaNueva,
                'nombre_puesto' => $_POST['nombre_puesto'] ?? '',
                'nivel'         => $_POST['nivel']         ?? 'OPERATIVO',
                'salario_base'  => $_POST['salario_base']  ?? null,
                'descripcion'   => $_POST['descripcion']   ?? '',
                'activa'        => $activaActual,
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

    /**
     * Activar / desactivar puesto (eliminado lógico).
     * GET: ?controller=puesto&action=toggle&id=1&active=0
     */
    public function toggle(): void
    {
        requireRole(1);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $active = isset($_GET['active']) ? (bool)$_GET['active'] : true;

        if ($id > 0) {
            Puesto::setActive($id, $active);
            $_SESSION['flash_success'] = $active
                ? 'Puesto activado correctamente.'
                : 'Puesto desactivado correctamente.';
        }

        header('Location: index.php?controller=puesto&action=index');
        exit;
    }
}
