<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../middleware/Auth.php';

class EmpresaController
{
    /**
     * Lista de empresas
     * GET: ?controller=empresa&action=index&q=texto&onlyActive=1
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search     = $_GET['q']         ?? null;

        // Checkbox "Ver también inactivas"
        $showInactive = isset($_GET['showInactive']) && $_GET['showInactive'] === '1';

        // Por defecto (sin showInactive) → solo activas
        // Empresa::all:
        //   - true  => WHERE activa = 1
        //   - false => WHERE activa = 0 
        //   - null  => sin filtro (todas)
        $onlyActive = $showInactive ? null : true;

        $empresas = Empresa::all(500, 0, $search, $onlyActive);

        // Disponibles en la vista:
        // $empresas, $search, $onlyActive
        require __DIR__ . '/../../public/views/organizacional/empresas/list.php';
    }

    /**
     * Mostrar formulario de nueva empresa
     * GET: ?controller=empresa&action=create
     */
    public function create(): void
    {
        requireRole(1);

        // Puedes pasar variables por defecto si quieres
        $errors = [];
        $old    = [];

        require __DIR__ . '/../../public/views/organizacional/empresas/create.php';
    }

    /**
     * Guardar nueva empresa
     * POST: ?controller=empresa&action=store
     */
    public function store(): void
    {
        requireRole(1);
        session_start();

        try {
            // 1) Leer y TRIM de los campos de dirección
            $calle        = trim($_POST['calle']            ?? '');
            $numExt       = trim($_POST['numero_exterior']  ?? '');
            $numInt       = trim($_POST['numero_interior']  ?? '');
            $colonia      = trim($_POST['colonia']          ?? '');
            $municipio    = trim($_POST['municipio']        ?? '');
            $ciudad       = trim($_POST['ciudad']           ?? '');
            $estado       = trim($_POST['estado']           ?? '');
            $codigoPostal = trim($_POST['codigo_postal']    ?? '');
            $pais         = trim($_POST['pais']             ?? '');

            // 2) Validar obligatorios de dirección 
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
            if ($codigoPostal === '') {
                throw new InvalidArgumentException('El código postal es obligatorio.');
            }
            if ($ciudad === '') {
                throw new InvalidArgumentException('La ciudad es obligatoria.');
            }
            if ($estado === '') {
                throw new InvalidArgumentException('El estado es obligatorio.');
            }
            if ($pais === '') {
                throw new InvalidArgumentException('El país es obligatorio.');
            }

            // 3) Armar dirección SIEMPRE del lado servidor
            $partes = [];

            if ($calle !== '') {
                $linea = 'Calle ' . $calle;
                if ($numExt !== '') $linea .= ' #' . $numExt;
                if ($numInt !== '') $linea .= ' Int. ' . $numInt;
                $partes[] = $linea;
            }

            if ($colonia !== '')      $partes[] = 'Col. ' . $colonia;
            if ($municipio !== '')    $partes[] = $municipio;
            if ($ciudad !== '')       $partes[] = $ciudad;
            if ($estado !== '')       $partes[] = $estado;
            if ($codigoPostal !== '') $partes[] = 'C.P. ' . $codigoPostal;
            if ($pais !== '')         $partes[] = $pais;

            $direccion = implode(', ', $partes);

            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }

            // 4) Teléfono: solo números 
            $telefono = preg_replace('/\D+/', '', $_POST['telefono'] ?? '');
            if ($telefono === '') {
                throw new InvalidArgumentException('El teléfono es obligatorio y debe contener solo números.');
            }

            // 5) Activa
            //    Ignoramos cualquier campo "activa" que venga del formulario.
            $activa = 1;

            // 6) Datos para el modelo (nombre, rfc, correo se validan en Empresa::create)
            $data = [
                'nombre'          => $_POST['nombre']          ?? '',
                'rfc'             => $_POST['rfc']             ?? '',
                'correo_contacto' => $_POST['correo_contacto'] ?? '',
                'telefono'        => $telefono,
                'direccion'       => $direccion,
                'activa'          => $activa, // siempre 1 al crear
            ];

            $id = Empresa::create($data);

            $_SESSION['flash_success'] = 'Empresa creada correctamente.';
            header('Location: index.php?controller=empresa&action=index');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;

            header('Location: index.php?controller=empresa&action=create');
            exit;
        }
    }

    /**
     * Mostrar formulario de edición
     * GET: ?controller=empresa&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=empresa&action=index');
            exit;
        }

        $empresa = Empresa::findById($id);

        if (!$empresa) {
            header('Location: index.php?controller=empresa&action=index');
            exit;
        }

        // $empresa disponible en la vista
        $errors = [];
        require __DIR__ . '/../../public/views/organizacional/empresas/edit.php';
    }

    /**
     * Actualizar empresa
     * POST: ?controller=empresa&action=update&id=1
     */
    public function update(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=empresa&action=index');
            exit;
        }

        // Obtener empresa actual para conservar su estado "activa"
        $empresaActual = Empresa::findById($id);
        if (!$empresaActual) {
            header('Location: index.php?controller=empresa&action=index');
            exit;
        }

        try {
            // 1) Leer y TRIM de la dirección
            $calle        = trim($_POST['calle']            ?? '');
            $numExt       = trim($_POST['numero_exterior']  ?? '');
            $numInt       = trim($_POST['numero_interior']  ?? '');
            $colonia      = trim($_POST['colonia']          ?? '');
            $municipio    = trim($_POST['municipio']        ?? '');
            $ciudad       = trim($_POST['ciudad']           ?? '');
            $estado       = trim($_POST['estado']           ?? '');
            $codigoPostal = trim($_POST['codigo_postal']    ?? '');
            $pais         = trim($_POST['pais']             ?? '');
            //$activaRaw    = $_POST['activa'] ?? '0';
           // $activa       = ($activaRaw === '1') ? 1 : 0;

            // 2) Validar obligatorios 
            if ($calle === '')        throw new InvalidArgumentException('La calle es obligatoria.');
            if ($numExt === '')       throw new InvalidArgumentException('El número exterior es obligatorio.');
            if ($colonia === '')      throw new InvalidArgumentException('La colonia es obligatoria.');
            if ($municipio === '')    throw new InvalidArgumentException('El municipio es obligatorio.');
            if ($codigoPostal === '') throw new InvalidArgumentException('El código postal es obligatorio.');
            if ($ciudad === '')       throw new InvalidArgumentException('La ciudad es obligatoria.');
            if ($estado === '')       throw new InvalidArgumentException('El estado es obligatorio.');
            if ($pais === '')         throw new InvalidArgumentException('El país es obligatorio.');

            // 3) Armar dirección
            $partes = [];

            if ($calle !== '') {
                $linea = 'Calle ' . $calle;
                if ($numExt !== '') $linea .= ' #' . $numExt;
                if ($numInt !== '') $linea .= ' Int. ' . $numInt;
                $partes[] = $linea;
            }

            if ($colonia !== '')      $partes[] = 'Col. ' . $colonia;
            if ($municipio !== '')    $partes[] = $municipio;
            if ($ciudad !== '')       $partes[] = $ciudad;
            if ($estado !== '')       $partes[] = $estado;
            if ($codigoPostal !== '') $partes[] = 'C.P. ' . $codigoPostal;
            if ($pais !== '')         $partes[] = $pais;

            $direccion = implode(', ', $partes);

            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }

            // 4) Teléfono solo números
            $telefono = preg_replace('/\D+/', '', $_POST['telefono'] ?? '');
            if ($telefono === '') {
                throw new InvalidArgumentException('El teléfono es obligatorio y debe contener solo números.');
            }

            // 5) Datos para el modelo
            $data = [
                'nombre'          => $_POST['nombre']          ?? '',
                'rfc'             => $_POST['rfc']             ?? '',
                'correo_contacto' => $_POST['correo_contacto'] ?? '',
                'telefono'        => $telefono,
                'direccion'       => $direccion,
                'activa'          => (int)($empresaActual['activa'] ?? 1),
            ];

            Empresa::update($id, $data);

            $_SESSION['flash_success'] = 'Empresa actualizada correctamente.';
            header('Location: index.php?controller=empresa&action=index');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;
            header('Location: index.php?controller=empresa&action=edit&id=' . $id);
            exit;
        }
    }

    /**
     * Activar / desactivar empresa
     * POST o GET: ?controller=empresa&action=toggle&id=1&active=0
     */
    public function toggle(): void
    {
        requireRole(1);

        session_start();

        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $active = isset($_GET['active']) ? (bool)$_GET['active'] : true;

        if ($id > 0) {
            Empresa::setActive($id, $active);
            $_SESSION['flash_success'] = $active
                ? 'Empresa activada correctamente.'
                : 'Empresa desactivada correctamente.';
        }

        header('Location: index.php?controller=empresa&action=index');
        exit;
    }
}
