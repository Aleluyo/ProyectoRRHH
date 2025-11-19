<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Ubicacion.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../middleware/Auth.php';

class UbicacionController
{
    /**
     * Lista de ubicaciones
     * GET: ?controller=ubicacion&action=index&q=texto&onlyActive=1&id_empresa=3
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search     = $_GET['q']          ?? null;
        $onlyActive = isset($_GET['onlyActive']) ? (bool)$_GET['onlyActive'] : null;
        $idEmpresa  = isset($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : null;

        if ($idEmpresa !== null && $idEmpresa <= 0) {
            $idEmpresa = null;
        }

        // Lista de ubicaciones (con filtro opcional por empresa)
        $ubicaciones = Ubicacion::all(500, 0, $search, $onlyActive, $idEmpresa);

        // Lista de empresas para filtro / combos en la vista
        $empresas = Empresa::all(500, 0, null, true);

        // Variables disponibles en la vista:
        // $ubicaciones, $empresas, $search, $onlyActive, $idEmpresa
        require __DIR__ . '/../../public/views/organizacional/ubicaciones/list.php';
    }

    /**
     * Mostrar formulario de nueva ubicación
     * GET: ?controller=ubicacion&action=create
     */
    public function create(): void
    {
        requireRole(1);

        $errors  = [];
        $old     = [];
        // Para el select de empresa
        $empresas = Empresa::all(500, 0, null, true);

        // $empresas, $errors, $old disponibles en la vista
        require __DIR__ . '/../../public/views/organizacional/ubicaciones/create.php';
    }

    /**
     * Guardar nueva ubicación
     * POST: ?controller=ubicacion&action=store
     */
    public function store(): void
    {
        requireRole(1);
        session_start();

        try {
            $idEmpresa = isset($_POST['id_empresa']) ? (int)$_POST['id_empresa'] : 0;
            $nombre    = trim($_POST['nombre']        ?? '');
            $direccion = trim($_POST['direccion']     ?? '');
            $ciudad    = trim($_POST['ciudad']        ?? '');
            $estadoReg = trim($_POST['estado_region'] ?? '');
            $pais      = trim($_POST['pais']          ?? '');
            $activa    = isset($_POST['activa']) ? 1 : 0;

            // Si por alguna razón no vino la dirección concatenada
            if ($direccion === '') {
                $calle        = trim($_POST['calle']           ?? '');
                $numExt       = trim($_POST['numero_exterior'] ?? '');
                $numInt       = trim($_POST['numero_interior'] ?? '');
                $colonia      = trim($_POST['colonia']         ?? '');
                $municipio    = trim($_POST['municipio']       ?? '');
                $ciudadPost   = trim($_POST['ciudad']          ?? '');
                $estadoPost   = trim($_POST['estado_region']   ?? '');
                $cp           = trim($_POST['codigo_postal']   ?? '');
                $paisPost     = trim($_POST['pais']            ?? '');

                $partes = [];

                if ($calle) {
                    $linea = 'Calle ' . $calle;
                    if ($numExt) $linea .= ' #' . $numExt;
                    if ($numInt) $linea .= ' Int. ' . $numInt;
                    $partes[] = $linea;
                }

                if ($colonia)   $partes[] = 'Col. ' . $colonia;
                if ($municipio) $partes[] = $municipio;
                if ($ciudadPost) $partes[] = $ciudadPost;
                if ($estadoPost) $partes[] = $estadoPost;
                if ($cp)         $partes[] = 'C.P. ' . $cp;
                if ($paisPost)   $partes[] = $paisPost;

                $direccion = implode(', ', $partes);

                // Por si venían vacíos los de arriba, sincronizamos ciudad/estado/pais
                if ($ciudad === '')    $ciudad    = $ciudadPost;
                if ($estadoReg === '') $estadoReg = $estadoPost;
                if ($pais === '')      $pais      = $paisPost;
            }

            if ($idEmpresa <= 0) {
                throw new InvalidArgumentException('La empresa es obligatoria.');
            }

            if ($nombre === '') {
                throw new InvalidArgumentException('El nombre de la sede es obligatorio.');
            }

            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }

            if ($ciudad === '') {
                throw new InvalidArgumentException('La ciudad es obligatoria.');
            }

            if ($estadoReg === '') {
                throw new InvalidArgumentException('El estado o región es obligatorio.');
            }

            if ($pais === '') {
                throw new InvalidArgumentException('El país es obligatorio.');
            }

            $data = [
                'id_empresa'    => $idEmpresa,
                'nombre'        => $nombre,
                'direccion'     => $direccion,
                'ciudad'        => $ciudad,
                'estado_region' => $estadoReg,
                'pais'          => $pais,
                'activa'        => $activa,
            ];

            Ubicacion::create($data);

            $_SESSION['flash_success'] = 'Ubicación creada correctamente.';
            header('Location: index.php?controller=ubicacion&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;

            header('Location: index.php?controller=ubicacion&action=create');
            exit;
        }
    }


    /**
     * Mostrar formulario de edición
     * GET: ?controller=ubicacion&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=ubicacion&action=index');
            exit;
        }

        $ubicacion = Ubicacion::findById($id);

        if (!$ubicacion) {
            header('Location: index.php?controller=ubicacion&action=index');
            exit;
        }

        $errors   = [];
        // Para el select de empresa en edición
        $empresas = Empresa::all(500, 0, null, true);

        // $ubicacion, $empresas, $errors disponibles en la vista
        require __DIR__ . '/../../public/views/organizacional/ubicaciones/edit.php';
    }

    /**
     * Actualizar ubicación
     * POST: ?controller=ubicacion&action=update&id=1
     */
    public function update(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=ubicacion&action=index');
            exit;
        }

        try {
            $idEmpresa = isset($_POST['id_empresa']) ? (int)$_POST['id_empresa'] : 0;
            $nombre    = trim($_POST['nombre']        ?? '');
            $direccion = trim($_POST['direccion']     ?? '');
            $ciudad    = trim($_POST['ciudad']        ?? '');
            $estadoReg = trim($_POST['estado_region'] ?? '');
            $pais      = trim($_POST['pais']          ?? '');
            $activa    = isset($_POST['activa']) ? 1 : 0;

            // Igual que en store(): reconstruimos si hace falta
            if ($direccion === '') {
                $calle        = trim($_POST['calle']           ?? '');
                $numExt       = trim($_POST['numero_exterior'] ?? '');
                $numInt       = trim($_POST['numero_interior'] ?? '');
                $colonia      = trim($_POST['colonia']         ?? '');
                $municipio    = trim($_POST['municipio']       ?? '');
                $ciudadPost   = trim($_POST['ciudad']          ?? '');
                $estadoPost   = trim($_POST['estado_region']   ?? '');
                $cp           = trim($_POST['codigo_postal']   ?? '');
                $paisPost     = trim($_POST['pais']            ?? '');

                $partes = [];

                if ($calle) {
                    $linea = 'Calle ' . $calle;
                    if ($numExt) $linea .= ' #' . $numExt;
                    if ($numInt) $linea .= ' Int. ' . $numInt;
                    $partes[] = $linea;
                }

                if ($colonia)   $partes[] = 'Col. ' . $colonia;
                if ($municipio) $partes[] = $municipio;
                if ($ciudadPost) $partes[] = $ciudadPost;
                if ($estadoPost) $partes[] = $estadoPost;
                if ($cp)         $partes[] = 'C.P. ' . $cp;
                if ($paisPost)   $partes[] = $paisPost;

                $direccion = implode(', ', $partes);

                if ($ciudad === '')    $ciudad    = $ciudadPost;
                if ($estadoReg === '') $estadoReg = $estadoPost;
                if ($pais === '')      $pais      = $paisPost;
            }

            if ($idEmpresa <= 0) {
                throw new InvalidArgumentException('La empresa es obligatoria.');
            }

            if ($nombre === '') {
                throw new InvalidArgumentException('El nombre de la sede es obligatorio.');
            }

            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }

            if ($ciudad === '') {
                throw new InvalidArgumentException('La ciudad es obligatoria.');
            }

            if ($estadoReg === '') {
                throw new InvalidArgumentException('El estado o región es obligatorio.');
            }

            if ($pais === '') {
                throw new InvalidArgumentException('El país es obligatorio.');
            }

            $data = [
                'id_empresa'    => $idEmpresa,
                'nombre'        => $nombre,
                'direccion'     => $direccion,
                'ciudad'        => $ciudad,
                'estado_region' => $estadoReg,
                'pais'          => $pais,
                'activa'        => $activa,
            ];

            Ubicacion::update($id, $data);

            $_SESSION['flash_success'] = 'Ubicación actualizada correctamente.';
            header('Location: index.php?controller=ubicacion&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;

            header('Location: index.php?controller=ubicacion&action=edit&id=' . $id);
            exit;
        }
    }


    /**
     * Activar / desactivar ubicación
     * GET: ?controller=ubicacion&action=toggle&id=1&active=0
     */
    public function toggle(): void
    {
        requireRole(1);
        session_start();

        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $active = isset($_GET['active']) ? (bool)$_GET['active'] : true;

        if ($id > 0) {
            Ubicacion::setActive($id, $active);

            $_SESSION['flash_success'] = $active
                ? 'Ubicación activada correctamente.'
                : 'Ubicación desactivada correctamente.';
        }

        // Conserva filtro por empresa si venía en la URL
        $redirect = 'index.php?controller=ubicacion&action=index';
        if (isset($_GET['id_empresa'])) {
            $redirect .= '&id_empresa=' . (int)$_GET['id_empresa'];
        }

        header('Location: ' . $redirect);
        exit;
    }
}
