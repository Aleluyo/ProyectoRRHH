<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Candidato.php';
require_once __DIR__ . '/../middleware/Auth.php';

class CandidatoController
{
    /**
     * Listado de candidatos
     * GET: ?controller=candidato&action=index&q=texto&fuente=LinkedIn
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search = $_GET['q']      ?? null;
        $fuente = $_GET['fuente'] ?? null;

        $candidatos = Candidato::all(500, 0, $search, $fuente);

        // $candidatos, $search, $fuente disponibles en la vista
        require __DIR__ . '/../../public/views/reclutamiento/candidatos/list.php';
    }

    /**
     * Formulario de nuevo candidato
     * GET: ?controller=candidato&action=create
     */
    public function create(): void
    {
        requireRole(1);

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/candidatos/create.php';
    }

    /**
     * Guardar nuevo candidato
     * POST: ?controller=candidato&action=store
     */
    public function store(): void
    {
        requireRole(1);
        session_start();

        $data = [
            'nombre'   => $_POST['nombre']   ?? '',
            'correo'   => $_POST['correo']   ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'cv'       => $_POST['cv']       ?? '',
            'fuente'   => $_POST['fuente']   ?? '',
        ];

        $errors = $this->validarCandidato($data, null);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=candidato&action=create');
            exit;
        }

        try {
            Candidato::create($data);

            $_SESSION['flash_success'] = 'Candidato creado correctamente.';
            header('Location: index.php?controller=candidato&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al crear el candidato: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            header('Location: index.php?controller=candidato&action=create');
            exit;
        }
    }

    /**
     * Formulario de edición
     * GET: ?controller=candidato&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        $candidato = Candidato::findById($id);
        if (!$candidato) {
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/candidatos/edit.php';
    }

    /**
     * Actualizar candidato
     * POST: ?controller=candidato&action=update&id=1
     */
    public function update(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        $data = [
            'nombre'   => $_POST['nombre']   ?? '',
            'correo'   => $_POST['correo']   ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'cv'       => $_POST['cv']       ?? '',
            'fuente'   => $_POST['fuente']   ?? '',
        ];

        $errors = $this->validarCandidato($data, $id);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: index.php?controller=candidato&action=edit&id=' . $id);
            exit;
        }

        try {
            Candidato::update($id, $data);

            $_SESSION['flash_success'] = 'Candidato actualizado correctamente.';
            header('Location: index.php?controller=candidato&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al actualizar el candidato: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;
            header('Location: index.php?controller=candidato&action=edit&id=' . $id);
            exit;
        }
    }

    /**
     * Eliminar candidato
     * GET/POST: ?controller=candidato&action=delete&id=1
     */
    public function delete(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            try {
                Candidato::delete($id);
                $_SESSION['flash_success'] = 'Candidato eliminado correctamente.';
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'No se pudo eliminar el candidato: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = 'ID de candidato inválido.';
        }

        header('Location: index.php?controller=candidato&action=index');
        exit;
    }

    /**
     * Validación básica para crear/editar candidatos.
     */
    private function validarCandidato(array $data, ?int $idCandidato = null): array
    {
        $errors = [];

        $nombre = trim($data['nombre'] ?? '');
        if ($nombre === '') {
            $errors['nombre'] = 'El nombre del candidato es obligatorio.';
        } elseif (mb_strlen($nombre) > 120) {
            $errors['nombre'] = 'El nombre es demasiado largo.';
        }

        $correo = trim($data['correo'] ?? '');
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'El correo electrónico no es válido.';
        } elseif ($correo !== '' && Candidato::existsByCorreo($correo, $idCandidato)) {
            $errors['correo'] = 'Ya existe un candidato con ese correo.';
        }

        $telefono = trim($data['telefono'] ?? '');
        if ($telefono !== '' && (strlen($telefono) < 5 || strlen($telefono) > 25)) {
            $errors['telefono'] = 'El teléfono tiene una longitud inválida.';
        }

        return $errors;
    }
}