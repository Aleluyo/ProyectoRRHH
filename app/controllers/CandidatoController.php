<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../models/Candidato.php';

class CandidatoController
{
    /* =========================================================
     *  LISTADO
     * ========================================================= */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search      = $_GET['q'] ?? null;
        $candidatos  = Candidato::all(500, 0, $search);

        // Vista de lista
        require __DIR__ . '/../../app/views/reclutamiento/candidatos/index.php';
    }

    /* =========================================================
     *  NUEVO CANDIDATO (GET)
     * ========================================================= */
    public function create(): void
    {
        requireLogin();
        requireRole(1);

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/candidatos/create.php';
    }

    /* =========================================================
     *  GUARDAR NUEVO (POST)
     * ========================================================= */
    public function store(): void
    {
        requireLogin();
        requireRole(1);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        $data = [
            'nombre'   => trim($_POST['nombre']   ?? ''),
            'correo'   => trim($_POST['correo']   ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'fuente'   => trim($_POST['fuente']   ?? ''),
            'cv'       => trim($_POST['cv']       ?? ''),
        ];

        $errors = $this->validarCandidato($data);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=candidato&action=create');
            exit;
        }

        // Crea el registro (el ID lo genera la BD con AUTO_INCREMENT)
        Candidato::create($data);

        header('Location: index.php?controller=candidato&action=index');
        exit;
    }

    /* =========================================================
     *  EDITAR CANDIDATO (GET)
     * ========================================================= */
    public function edit(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
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
        $old    = $_SESSION['old_input'] ?? null;
        unset($_SESSION['errors'], $_SESSION['old_input']);

        // Si venimos de un error de validación, rellenar con los datos viejos
        if (is_array($old) && !empty($old)) {
            $candidato = array_merge($candidato, $old);
        }

        require __DIR__ . '/../../public/views/reclutamiento/candidatos/edit.php';
    }

    /* =========================================================
     *  ACTUALIZAR (POST)  <-- ESTA ES LA ACCIÓN QUE NO ENCONTRABA
     * ========================================================= */
    public function update(): void
    {
        requireLogin();
        requireRole(1);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        $data = [
            'nombre'   => trim($_POST['nombre']   ?? ''),
            'correo'   => trim($_POST['correo']   ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'fuente'   => trim($_POST['fuente']   ?? ''),
            'cv'       => trim($_POST['cv']       ?? ''),
        ];

        $errors = $this->validarCandidato($data);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=candidato&action=edit&id=' . $id);
            exit;
        }

        Candidato::update($id, $data);

        $_SESSION['swal'] = [
            'title' => 'Candidato Actualizado',
            'text'  => 'Los datos del candidato se han guardado correctamente.',
            'icon'  => 'success'
        ];

        header('Location: index.php?controller=candidato&action=index');
        exit;
    }

    /**
     * Eliminar candidato (lógico)
     */
    public function delete(): void
    {
        requireLogin();
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id > 0) {
            Candidato::delete($id);
            $_SESSION['swal'] = [
                'title' => 'Candidato Eliminado',
                'text'  => 'El candidato ha sido eliminado correctamente.',
                'icon'  => 'success'
            ];
        }

        header('Location: index.php?controller=candidato&action=index');
        exit;
    }

    /**
     * Ver CV validando existencia
     * GET: ?controller=candidato&action=verCV&id=1
     */
    public function verCV(): void
    {
        requireLogin();
        // requireRole(1); 

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        $candidato = Candidato::findById($id);

        if (!$candidato || empty($candidato['cv'])) {
            session_start();
            $_SESSION['swal'] = [
                'title' => 'Sin CV',
                'text'  => 'Este candidato no tiene un curriculum registrado.',
                'icon'  => 'warning'
            ];
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        // Ruta del archivo (asumiendo guardado relativo en public/)
        $rutaArchivo = __DIR__ . '/../../public/' . ltrim($candidato['cv'], '/');
        
        // Si es URL absoluta
        if (strpos($candidato['cv'], 'http') === 0) {
            header('Location: ' . $candidato['cv']);
            exit;
        }

        if (!file_exists($rutaArchivo)) {
            session_start();
            $_SESSION['swal'] = [
                'title' => 'Archivo no encontrado',
                'text'  => 'El archivo del CV no se encuentra en el servidor.',
                'icon'  => 'error'
            ];
            header('Location: index.php?controller=candidato&action=index');
            exit;
        }

        // Redirigir al archivo
        header('Location: ' . $candidato['cv']);
        exit;
    }

    /* =========================================================
     *  VALIDACIÓN COMÚN
     * ========================================================= */
    private function validarCandidato(array $data): array
    {
        $errors = [];

        // Nombre: requerido, solo letras y espacios
        if ($data['nombre'] === '') {
            $errors['nombre'] = 'El nombre es obligatorio.';
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $data['nombre'])) {
            $errors['nombre'] = 'El nombre solo puede contener letras y espacios.';
        }

        // Correo: requerido, formato de email
        if ($data['correo'] === '') {
            $errors['correo'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'El correo electrónico no tiene un formato válido.';
        }

        // Teléfono: requerido, solo dígitos, longitud 8–15
        if ($data['telefono'] === '') {
            $errors['telefono'] = 'El teléfono es obligatorio.';
        } elseif (!preg_match('/^[0-9]{8,15}$/', $data['telefono'])) {
            $errors['telefono'] = 'El teléfono debe contener solo números (8 a 15 dígitos).';
        }

        return $errors;
    }
}