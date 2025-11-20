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
        $onlyActive = isset($_GET['onlyActive']) ? (bool)$_GET['onlyActive'] : null;

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
            // 1) Tomamos la dirección ya concatenada desde el formulario
            $direccion = trim($_POST['direccion'] ?? '');

            //Si por alguna razón no vino, se rearma en PHP usando los nombres de los inputs
            if ($direccion === '') {
                $calle        = trim($_POST['calle']            ?? '');
                $numExt       = trim($_POST['numero_exterior']  ?? '');
                $numInt       = trim($_POST['numero_interior']  ?? '');
                $colonia      = trim($_POST['colonia']          ?? '');
                $municipio    = trim($_POST['municipio']        ?? '');
                $ciudad       = trim($_POST['ciudad']           ?? '');
                $estado       = trim($_POST['estado']           ?? '');
                $codigoPostal = trim($_POST['codigo_postal']    ?? '');
                $pais         = trim($_POST['pais']             ?? '');

                $partes = [];

                if ($calle) {
                    $linea = 'Calle ' . $calle;
                    if ($numExt) $linea .= ' #' . $numExt;
                    if ($numInt) $linea .= ' Int. ' . $numInt;
                    $partes[] = $linea;
                }

                if ($colonia)      $partes[] = 'Col. ' . $colonia;
                if ($municipio)    $partes[] = $municipio;
                if ($ciudad)       $partes[] = $ciudad;
                if ($estado)       $partes[] = $estado;
                if ($codigoPostal) $partes[] = 'C.P. ' . $codigoPostal;
                if ($pais)         $partes[] = $pais;

                $direccion = implode(', ', $partes);
            }

            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }

            $telefono = preg_replace('/\D+/', '', $_POST['telefono'] ?? '');
            if ($telefono === '') {
                throw new InvalidArgumentException('El teléfono es obligatorio y debe contener solo números.');
            }
            
            $activa = isset($_POST['activa']) ? (int)$_POST['activa'] : 1;

            $data = [
                'nombre'          => $_POST['nombre']          ?? '',
                'rfc'             => $_POST['rfc']             ?? '',
                'correo_contacto' => $_POST['correo_contacto'] ?? '',
                'telefono'        => $telefono,
                'direccion'       => $direccion,
                'activa'          => $activa,
            ];

            $id = Empresa::create($data);

            $_SESSION['flash_success'] = 'Empresa creada correctamente.';
            header('Location: index.php?controller=empresa&action=index');
            exit;

        } catch (\Throwable $e) {
            // En caso de error, recargamos el formulario con mensajes
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

        try {
            // 1) Dirección ya concatenada desde el formulario
            $direccion = trim($_POST['direccion'] ?? '');

            // 2) Si no viene, se arma desde los campos
            if ($direccion === '') {
                $calle        = trim($_POST['calle']           ?? '');
                $numExt       = trim($_POST['numero_exterior'] ?? '');
                $numInt       = trim($_POST['numero_interior'] ?? '');
                $colonia      = trim($_POST['colonia']         ?? '');
                $municipio    = trim($_POST['municipio']       ?? '');
                $ciudad       = trim($_POST['ciudad']          ?? '');
                $estado       = trim($_POST['estado']          ?? '');
                $codigoPostal = trim($_POST['codigo_postal']   ?? '');
                $pais         = trim($_POST['pais']            ?? '');

                $partes = [];

                if ($calle) {
                    $linea = 'Calle ' . $calle;
                    if ($numExt) $linea .= ' #' . $numExt;
                    if ($numInt) $linea .= ' Int. ' . $numInt;
                    $partes[] = $linea;
                }

                if ($colonia)      $partes[] = 'Col. ' . $colonia;
                if ($municipio)    $partes[] = $municipio;
                if ($ciudad)       $partes[] = $ciudad;
                if ($estado)       $partes[] = $estado;
                if ($codigoPostal) $partes[] = 'C.P. ' . $codigoPostal;
                if ($pais)         $partes[] = $pais;

                $direccion = implode(', ', $partes);
            }

            // 3) Validar
            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }

            // Teléfono solo números
            $telefono = preg_replace('/\D+/', '', $_POST['telefono'] ?? '');
            if ($telefono === '') {
                throw new InvalidArgumentException('El teléfono es obligatorio y debe contener solo números.');
            }

            $activa = isset($_POST['activa']) ? (int)$_POST['activa'] : 0;

            $data = [
                'nombre'          => $_POST['nombre']          ?? '',
                'rfc'             => $_POST['rfc']             ?? '',
                'correo_contacto' => $_POST['correo_contacto'] ?? '',
                'telefono'        => $telefono,
                'direccion'       => $direccion,
                'activa'          => $activa,
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
