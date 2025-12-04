<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../models/Vacante.php';
require_once __DIR__ . '/../models/Candidato.php';
require_once __DIR__ . '/../middleware/Auth.php';

class PostulacionController
{
    /**
     * Lista de postulaciones por vacante
     * GET: ?controller=postulacion&action=index&id_vacante=1&estado=POSTULADO
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $idVacante = isset($_GET['id_vacante']) ? (int)$_GET['id_vacante'] : 0;
        $estado    = $_GET['estado'] ?? null;

        if ($idVacante <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $vacante      = Vacante::findById($idVacante);
        $postulaciones = Postulacion::byVacante($idVacante, 500, 0, $estado);

        // Candidatos para combos en la vista (por ejemplo, alta rápida)
        $candidatos = Candidato::all(500, 0, null, null);

        // $vacante, $postulaciones, $candidatos, $estado disponibles en la vista
        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/list.php';
    }

    /**
     * Formulario de nueva postulación
     * GET: ?controller=postulacion&action=create&id_vacante=1
     */
    public function create(): void
    {
        requireRole(1);

        $idVacante = isset($_GET['id_vacante']) ? (int)$_GET['id_vacante'] : 0;
        if ($idVacante <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $vacante = Vacante::findById($idVacante);
        if (!$vacante) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $errors     = $_SESSION['errors']    ?? [];
        $old        = $_SESSION['old_input'] ?? [];
        $candidatos = Candidato::all(500, 0, null, null);

        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/create.php';
    }

    /**
     * Guardar nueva postulación
     * POST: ?controller=postulacion&action=store
     */
    public function store(): void
    {
        requireRole(1);
        session_start();

        $data = [
            'id_vacante'   => $_POST['id_vacante']   ?? null,
            'id_candidato' => $_POST['id_candidato'] ?? null,
            'estado'       => $_POST['estado']       ?? 'POSTULADO',
            'comentarios'  => $_POST['comentarios']  ?? '',
        ];

        $errors = $this->validarPostulacion($data, null);

        $idVacante = (int)($data['id_vacante'] ?? 0);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=postulacion&action=create&id_vacante=' . $idVacante);
            exit;
        }

        try {
            Postulacion::create($data);

            $_SESSION['flash_success'] = 'Postulación creada correctamente.';
            header('Location: index.php?controller=postulacion&action=index&id_vacante=' . $idVacante);
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al crear la postulación: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            header('Location: index.php?controller=postulacion&action=create&id_vacante=' . $idVacante);
            exit;
        }
    }

    /**
     * Formulario de edición de postulación
     * GET: ?controller=postulacion&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $idPost = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($idPost <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $postulacion = Postulacion::findById($idPost);
        if (!$postulacion) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $vacante    = Vacante::findById((int)$postulacion['id_vacante']);
        $candidatos = Candidato::all(500, 0, null, null);

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/edit.php';
    }

    /**
     * Actualizar postulación
     * POST: ?controller=postulacion&action=update&id=1
     */
    public function update(): void
    {
        requireRole(1);
        session_start();

        $idPost = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($idPost <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $data = [
            'id_vacante'   => $_POST['id_vacante']   ?? null,
            'id_candidato' => $_POST['id_candidato'] ?? null,
            'estado'       => $_POST['estado']       ?? 'POSTULADO',
            'comentarios'  => $_POST['comentarios']  ?? '',
        ];

        $errors = $this->validarPostulacion($data, $idPost);
        $idVacante = (int)($data['id_vacante'] ?? 0);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=postulacion&action=edit&id=' . $idPost);
            exit;
        }

        try {
            Postulacion::update($idPost, $data);

            $_SESSION['flash_success'] = 'Postulación actualizada correctamente.';
            header('Location: index.php?controller=postulacion&action=index&id_vacante=' . $idVacante);
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al actualizar la postulación: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            header('Location: index.php?controller=postulacion&action=edit&id=' . $idPost);
            exit;
        }
    }

    /**
     * Eliminar postulación
     * GET/POST: ?controller=postulacion&action=delete&id=1&id_vacante=1
     */
    public function delete(): void
    {
        requireRole(1);
        session_start();

        $idPost    = isset($_GET['id'])          ? (int)$_GET['id']          : 0;
        $idVacante = isset($_GET['id_vacante']) ? (int)$_GET['id_vacante'] : 0;

        if ($idPost > 0) {
            try {
                Postulacion::delete($idPost);
                $_SESSION['flash_success'] = 'Postulación eliminada correctamente.';
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'No se pudo eliminar la postulación: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = 'ID de postulación inválido.';
        }

        if ($idVacante > 0) {
            header('Location: index.php?controller=postulacion&action=index&id_vacante=' . $idVacante);
        } else {
            header('Location: index.php?controller=vacante&action=index');
        }
        exit;
    }

    /**
     * Validación básica de postulaciones.
     */
    private function validarPostulacion(array $data, ?int $idPost = null): array
    {
        $errors = [];

        $idVacante = (int)($data['id_vacante'] ?? 0);
        if ($idVacante <= 0) {
            $errors['id_vacante'] = 'La vacante es obligatoria.';
        }

        $idCandidato = (int)($data['id_candidato'] ?? 0);
        if ($idCandidato <= 0) {
            $errors['id_candidato'] = 'El candidato es obligatorio.';
        }

        $estado = strtoupper(trim((string)($data['estado'] ?? '')));
        $estadosValidos = [
            'POSTULADO',
            'SCREENING',
            'ENTREVISTA',
            'PRUEBA',
            'OFERTA',
            'CONTRATADO',
            'RECHAZADO',
        ];

        if ($estado === '') {
            $errors['estado'] = 'El estado de la postulación es obligatorio.';
        } elseif (!in_array($estado, $estadosValidos, true)) {
            $errors['estado'] = 'Estado de la postulación inválido.';
        }

        return $errors;
    }
}