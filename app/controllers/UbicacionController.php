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
        
        // Checkbox "Ver también inactivas"
        $showInactive = isset($_GET['showInactive']) && $_GET['showInactive'] === '1';

        // Por defecto (sin showInactive) → solo activas
        // Ubicacion::all:
        //   - true  => WHERE activa = 1
        //   - false => WHERE activa = 0 
        //   - null  => sin filtro (todas)
        $onlyActive = $showInactive ? null : true;

        $idEmpresa = isset($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : null;
        if ($idEmpresa !== null && $idEmpresa <= 0) {
            $idEmpresa = null;
        }

        // Lista de ubicaciones (con filtro opcional por empresa)
        $ubicaciones = Ubicacion::all(500, 0, $search, $onlyActive, $idEmpresa);

        // Lista de empresas para filtro / combos en la vista
        $empresas = Empresa::all(500, 0, null, null);

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
            // 1) Leer y TRIM de todo lo importante
            $idEmpresa = isset($_POST['id_empresa']) ? (int)$_POST['id_empresa'] : 0;
            $nombre    = trim($_POST['nombre']        ?? '');
            $nombre = preg_replace('/\s+/u', ' ', $nombre);

            $calle        = trim($_POST['calle']           ?? '');
            $numExt       = trim($_POST['numero_exterior'] ?? '');
            $numInt       = trim($_POST['numero_interior'] ?? '');
            $colonia      = trim($_POST['colonia']         ?? '');
            $municipio    = trim($_POST['municipio']       ?? '');
            $ciudad       = trim($_POST['ciudad']          ?? '');
            $estadoReg    = trim($_POST['estado_region']   ?? '');
            $cp           = trim($_POST['codigo_postal']   ?? '');
            $pais         = trim($_POST['pais']            ?? '');
            $activa       = isset($_POST['activa']) ? 1 : 0;

            // 2) Validar obligatorios (después de trim, así espacios cuentan como vacío)
            if ($idEmpresa <= 0) {
                throw new InvalidArgumentException('La empresa es obligatoria.');
            }

            if ($nombre === '') {
                throw new InvalidArgumentException('El nombre de la sede es obligatorio.');
            }

            if ($calle === '') {
                throw new InvalidArgumentException('La calle es obligatoria.');
            }

            if ($numExt === '') {
                throw new InvalidArgumentException('El número exterior es obligatorio.');
            }

            if ($colonia === '') {
                throw new InvalidArgumentException('La colonia es obligatoria.');
            }

            if ($municipio === '') {
                throw new InvalidArgumentException('El municipio es obligatorio.');
            }

            if ($cp === '') {
                throw new InvalidArgumentException('El código postal es obligatorio.');
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

            // 3) Longitudes
            if (mb_strlen($nombre) > 100) {
                throw new InvalidArgumentException('El nombre no debe superar los 100 caracteres.');
            }
            if ($ciudad !== '' && mb_strlen($ciudad) > 80) {
                throw new InvalidArgumentException('La ciudad no debe superar los 80 caracteres.');
            }
            if ($estadoReg !== '' && mb_strlen($estadoReg) > 80) {
                throw new InvalidArgumentException('El estado o región no debe superar los 80 caracteres.');
            }
            if ($pais !== '' && mb_strlen($pais) > 80) {
                throw new InvalidArgumentException('El país no debe superar los 80 caracteres.');
            }

            // 4) Concatenar dirección
            $partes = [];

            if ($calle !== '') {
                $linea = 'Calle ' . $calle;
                if ($numExt !== '') $linea .= ' #' . $numExt;
                if ($numInt !== '') $linea .= ' Int. ' . $numInt;
                $partes[] = $linea;
            }

            if ($colonia !== '')   $partes[] = 'Col. ' . $colonia;
            if ($municipio !== '') $partes[] = $municipio;
            if ($ciudad !== '')    $partes[] = $ciudad;
            if ($estadoReg !== '') $partes[] = $estadoReg;
            if ($cp !== '')        $partes[] = 'C.P. ' . $cp;
            if ($pais !== '')      $partes[] = $pais;

            $direccion = implode(', ', $partes);

            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }

            // 5) Dejar al modelo validar duplicado por empresa (create ya llama a existsNombreEnEmpresa)
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
        
        $idEmpresaActual = (int)$ubicacion['id_empresa'];

        // Empresa actual de la ubicación
        $empresaActual = Empresa::findById($idEmpresaActual);
        $empresaEstaActiva = $empresaActual && (int)$empresaActual['activa'] === 1;

        // Por defecto: solo empresas activas
        $empresas = Empresa::all(500, 0, null, true);

        // Bandera para la vista: ¿debo bloquear el select?
        $empresaSoloLectura = false;

        // Si la empresa actual está inactiva: la agregamos a la lista y bloqueamos el select
        if (!$empresaEstaActiva && $empresaActual) {
            $yaIncluida = false;
            foreach ($empresas as $e) {
                if ((int)$e['id_empresa'] === $idEmpresaActual) {
                    $yaIncluida = true;
                    break;
                }
            }

            if (!$yaIncluida) {
                $empresas[] = $empresaActual;
            }

            $empresaSoloLectura = true;
        }

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

        // cargar la ubicación y la empresa actual
        $ubicacionActual = Ubicacion::findById($id);
        if (!$ubicacionActual) {
            header('Location: index.php?controller=ubicacion&action=index');
            exit;
        }

        $idEmpresaActual = (int)$ubicacionActual['id_empresa'];

        try {
            // 1) Leer y TRIM de todo
            $idEmpresa = isset($_POST['id_empresa']) ? (int)$_POST['id_empresa'] : 0;

            // Empresa actual (para saber si está activa o no)
            $empresaActual = Empresa::findById($idEmpresaActual);
            $empresaActualActiva = $empresaActual && (int)$empresaActual['activa'] === 1;

            // Si la empresa actual está INACTIVA → no permitir cambiarla: se fuerza al valor actual
            if (!$empresaActualActiva) {
                $idEmpresa = $idEmpresaActual;
            }

            if ($idEmpresa <= 0) {
                throw new InvalidArgumentException('La empresa es obligatoria.');
            }

            // Validar que la empresa destino exista
            $empresaDestino = Empresa::findById($idEmpresa);
            if (!$empresaDestino) {
                throw new InvalidArgumentException('La empresa seleccionada no existe.');
            }

            // Si se intenta CAMBIAR de empresa, solo se permite si la nueva está activa
            if ($idEmpresa !== $idEmpresaActual && (int)$empresaDestino['activa'] !== 1) {
                throw new InvalidArgumentException('Solo se pueden asignar empresas activas.');
            }

            $nombre    = trim($_POST['nombre']        ?? '');
            $nombre    = preg_replace('/\s+/u', ' ', $nombre);

            $calle        = trim($_POST['calle']           ?? '');
            $numExt       = trim($_POST['numero_exterior'] ?? '');
            $numInt       = trim($_POST['numero_interior'] ?? '');
            $colonia      = trim($_POST['colonia']         ?? '');
            $municipio    = trim($_POST['municipio']       ?? '');
            $ciudad       = trim($_POST['ciudad']          ?? '');
            $estadoReg    = trim($_POST['estado_region']   ?? '');
            $cp           = trim($_POST['codigo_postal']   ?? '');
            $pais         = trim($_POST['pais']            ?? '');
            $activa       = isset($_POST['activa']) ? 1 : 0;

            // 2) Validar obligatorios (después de trim)
            if ($nombre === '') {
                throw new InvalidArgumentException('El nombre de la sede es obligatorio.');
            }

            if ($calle === '') {
                throw new InvalidArgumentException('La calle es obligatoria.');
            }

            if ($numExt === '') {
                throw new InvalidArgumentException('El número exterior es obligatorio.');
            }

            if ($colonia === '') {
                throw new InvalidArgumentException('La colonia es obligatoria.');
            }

            if ($municipio === '') {
                throw new InvalidArgumentException('El municipio es obligatorio.');
            }

            if ($cp === '') {
                throw new InvalidArgumentException('El código postal es obligatorio.');
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

            // 3) Longitudes
            if (mb_strlen($nombre) > 100) {
                throw new InvalidArgumentException('El nombre no debe superar los 100 caracteres.');
            }
            if ($ciudad !== '' && mb_strlen($ciudad) > 80) {
                throw new InvalidArgumentException('La ciudad no debe superar los 80 caracteres.');
            }
            if ($estadoReg !== '' && mb_strlen($estadoReg) > 80) {
                throw new InvalidArgumentException('El estado o región no debe superar los 80 caracteres.');
            }
            if ($pais !== '' && mb_strlen($pais) > 80) {
                throw new InvalidArgumentException('El país no debe superar los 80 caracteres.');
            }

            // 4) Validar NOMBRE DUPLICADO 
            if (Ubicacion::existsNombreEnEmpresa($idEmpresa, $nombre, $id)) {
                throw new InvalidArgumentException('Ya existe una ubicación con ese nombre para esta empresa.');
            }

            // 5) Concatenar dirección
            $partes = [];

            if ($calle !== '') {
                $linea = 'Calle ' . $calle;
                if ($numExt !== '') $linea .= ' #' . $numExt;
                if ($numInt !== '') $linea .= ' Int. ' . $numInt;
                $partes[] = $linea;
            }

            if ($colonia !== '')   $partes[] = 'Col. ' . $colonia;
            if ($municipio !== '') $partes[] = $municipio;
            if ($ciudad !== '')    $partes[] = $ciudad;
            if ($estadoReg !== '') $partes[] = $estadoReg;
            if ($cp !== '')        $partes[] = 'C.P. ' . $cp;
            if ($pais !== '')      $partes[] = $pais;

            $direccion = implode(', ', $partes);

            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }

            // 6) Actualizar
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
