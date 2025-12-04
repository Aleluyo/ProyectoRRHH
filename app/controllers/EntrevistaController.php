<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Entrevista.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../middleware/Auth.php';

class EntrevistaController
{
    /**
     * Listado de entrevistas por postulación
     * GET: ?controller=entrevista&action=index&id_postulacion=1
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $idPost = isset($_GET['id_postulacion']) ? (int)$_GET['id_postulacion'] : 0;
        if ($idPost <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $postulacion = Postulacion::findById($idPost);
        if (!$postulacion) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $entrevistas = Entrevista::byPostulacion($idPost, 500, 0);

        // $postulacion, $entrevistas disponibles en la vista
        require __DIR__ . '/../../public/views/reclutamiento/entrevistas/list.php';
    }

    /**
     * Formulario de nueva entrevista
     * GET: ?controller=entrevista&action=create&id_postulacion=1
     */
    public function create(): void
    {
        requireRole(1);

        $idPost = isset($_GET['id_postulacion']) ? (int)$_GET['id_postulacion'] : 0;
        if ($idPost <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $postulacion = Postulacion::findById($idPost);
        if (!$postulacion) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/entrevistas/create.php';
    }

    /**
     * Guardar nueva entrevista
     * POST: ?controller=entrevista&action=store
     */
    public function store(): void
    {
        requireRole(1);
        session_start();

        // Igual que en Vacante: puedes ajustar según tu sesión
        $entrevistador = $_POST['entrevistador'] ?? ($_SESSION['user_id'] ?? null);

        $data = [
            'id_postulacion'  => $_POST['id_postulacion']  ?? null,
            'entrevistador'   => $entrevistador,
            'programada_para' => $_POST['programada_para'] ?? '',
            'resultado'       => $_POST['resultado']       ?? 'PENDIENTE',
            'notas'           => $_POST['notas']           ?? '',
        ];

        $errors = $this->validarEntrevista($data, null);
        $idPost = (int)($data['id_postulacion'] ?? 0);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=entrevista&action=create&id_postulacion=' . $idPost);
            exit;
        }

        try {
            Entrevista::create($data);

            $_SESSION['flash_success'] = 'Entrevista creada correctamente.';
            header('Location: index.php?controller=entrevista&action=index&id_postulacion=' . $idPost);
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al crear la entrevista: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            header('Location: index.php?controller=entrevista&action=create&id_postulacion=' . $idPost);
            exit;
        }
    }

    /**
     * Formulario de edición
     * GET: ?controller=entrevista&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $entrevista = Entrevista::findById($id);
        if (!$entrevista) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $postulacion = Postulacion::findById((int)$entrevista['id_postulacion']);

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/entrevistas/edit.php';
    }

    /**
     * Actualizar entrevista
     * POST: ?controller=entrevista&action=update&id=1
     */
    public function update(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $data = [
            'id_postulacion'  => $_POST['id_postulacion']  ?? null,
            'entrevistador'   => $_POST['entrevistador']   ?? ($_SESSION['user_id'] ?? null),
            'programada_para' => $_POST['programada_para'] ?? '',
            'resultado'       => $_POST['resultado']       ?? 'PENDIENTE',
            'notas'           => $_POST['notas']           ?? '',
        ];

        $errors = $this->validarEntrevista($data, $id);
        $idPost = (int)($data['id_postulacion'] ?? 0);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=entrevista&action=edit&id=' . $id);
            exit;
        }

        try {
            Entrevista::update($id, $data);

            $_SESSION['flash_success'] = 'Entrevista actualizada correctamente.';
            header('Location: index.php?controller=entrevista&action=index&id_postulacion=' . $idPost);
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al actualizar la entrevista: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            header('Location: index.php?controller=entrevista&action=edit&id=' . $id);
            exit;
        }
    }

    /**
     * Eliminar entrevista
     * GET/POST: ?controller=entrevista&action=delete&id=1&id_postulacion=1
     */
    public function delete(): void
    {
        requireRole(1);
        session_start();

        $id    = isset($_GET['id'])            ? (int)$_GET['id']            : 0;
        $idPost = isset($_GET['id_postulacion']) ? (int)$_GET['id_postulacion'] : 0;

        if ($id > 0) {
            try {
                Entrevista::delete($id);
                $_SESSION['flash_success'] = 'Entrevista eliminada correctamente.';
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'No se pudo eliminar la entrevista: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = 'ID de entrevista inválido.';
        }

        if ($idPost > 0) {
            header('Location: index.php?controller=entrevista&action=index&id_postulacion=' . $idPost);
        } else {
            header('Location: index.php?controller=vacante&action=index');
        }
        exit;
    }

    /**
     * Validación básica de entrevista.
     */
    private function validarEntrevista(array $data, ?int $idEntrevista = null): array
    {
        $errors = [];

        $idPost = (int)($data['id_postulacion'] ?? 0);
        if ($idPost <= 0) {
            $errors['id_postulacion'] = 'La postulación es obligatoria.';
        }

        $entrevistador = (int)($data['entrevistador'] ?? 0);
        if ($entrevistador <= 0) {
            $errors['entrevistador'] = 'El entrevistador es obligatorio.';
        }

        $prog = trim((string)($data['programada_para'] ?? ''));
        if ($prog === '') {
            $errors['programada_para'] = 'La fecha/hora programada es obligatoria.';
        }

        $resultado = strtoupper(trim((string)($data['resultado'] ?? '')));
        $validos   = ['PENDIENTE', 'APROBADO', 'RECHAZADO'];
        if ($resultado === '') {
            $errors['resultado'] = 'El resultado es obligatorio (o al menos PENDIENTE).';
        } elseif (!in_array($resultado, $validos, true)) {
            $errors['resultado'] = 'Resultado de entrevista inválido.';
        }

        return $errors;
    }
}