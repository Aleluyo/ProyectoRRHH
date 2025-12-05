<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../models/Vacante.php';

class PostulacionController
{
    /**
     * Lista de postulaciones.
     * - Si viene id_vacante => filtra por esa vacante.
     * - Si no viene => muestra todas las postulaciones.
     * GET: ?controller=postulacion&action=index[&id_vacante=1][&q=texto]
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search    = $_GET['q'] ?? null;
        $idVacante = isset($_GET['id_vacante']) ? (int)$_GET['id_vacante'] : 0;
        if ($idVacante <= 0) {
            $idVacante = null;
        }

        // Usa Postulacion::all con filtro opcional por vacante
        $postulaciones = Postulacion::all(500, 0, $search, $idVacante);

        // Solo cargamos la vacante si hay filtro
        $vacante = null;
        if ($idVacante !== null) {
            $vacante = Vacante::findById($idVacante);
        }

        // $postulaciones y $vacante disponibles en la vista
        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/list.php';
    }

    /**
     * Mostrar formulario de nueva postulación
     * GET: ?controller=postulacion&action=create[&id_vacante=1]
     */
    public function create(): void
    {
        requireRole(1);

        $idVacante = isset($_GET['id_vacante']) ? (int)$_GET['id_vacante'] : 0;

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        $vacante = $idVacante > 0 ? Vacante::findById($idVacante) : null;

        // $vacante, $idVacante, $errors, $old disponibles en la vista
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
            'id_vacante'   => $_POST['id_vacante']   ?? '',
            'id_candidato' => $_POST['id_candidato'] ?? '',
            'estado'       => $_POST['estado']       ?? 'POSTULADO',
            'comentarios'  => $_POST['comentarios']  ?? '',
        ];

        $errors = $this->validarPostulacion($data);
        $idVacante = (int)($data['id_vacante'] ?? 0);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            $redir = 'index.php?controller=postulacion&action=create';
            if ($idVacante > 0) {
                $redir .= '&id_vacante=' . $idVacante;
            }

            header('Location: ' . $redir);
            exit;
        }

        try {
            Postulacion::create($data);

            $_SESSION['flash_success'] = 'Postulación creada correctamente.';

            $redir = 'index.php?controller=postulacion&action=index';
            if ($idVacante > 0) {
                $redir .= '&id_vacante=' . $idVacante;
            }

            header('Location: ' . $redir);
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al crear la postulación: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            $redir = 'index.php?controller=postulacion&action=create';
            if ($idVacante > 0) {
                $redir .= '&id_vacante=' . $idVacante;
            }

            header('Location: ' . $redir);
            exit;
        }
    }

    /**
     * Mostrar formulario de edición
     * GET: ?controller=postulacion&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=postulacion&action=index');
            exit;
        }

        $postulacion = Postulacion::findById($id);
        if (!$postulacion) {
            header('Location: index.php?controller=postulacion&action=index');
            exit;
        }

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        $vacante = isset($postulacion['id_vacante'])
            ? Vacante::findById((int)$postulacion['id_vacante'])
            : null;

        // $postulacion, $vacante, $errors, $old disponibles en la vista
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

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=postulacion&action=index');
            exit;
        }

        $data = [
            'id_vacante'   => $_POST['id_vacante']   ?? '',
            'id_candidato' => $_POST['id_candidato'] ?? '',
            'estado'       => $_POST['estado']       ?? '',
            'comentarios'  => $_POST['comentarios']  ?? '',
        ];

        $errors    = $this->validarPostulacion($data);
        $idVacante = (int)($data['id_vacante'] ?? 0);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=postulacion&action=edit&id=' . $id);
            exit;
        }

        try {
            Postulacion::update($id, $data);

            $_SESSION['flash_success'] = 'Postulación actualizada correctamente.';

            $redir = 'index.php?controller=postulacion&action=index';
            if ($idVacante > 0) {
                $redir .= '&id_vacante=' . $idVacante;
            }

            header('Location: ' . $redir);
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al actualizar la postulación: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            header('Location: index.php?controller=postulacion&action=edit&id=' . $id);
            exit;
        }
    }

    /**
     * Eliminar postulación
     * GET/POST: ?controller=postulacion&action=delete&id=1
     */
    public function delete(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            try {
                $postulacion = Postulacion::findById($id);
                $idVacante   = isset($postulacion['id_vacante']) ? (int)$postulacion['id_vacante'] : 0;

                Postulacion::delete($id);
                $_SESSION['flash_success'] = 'Postulación eliminada correctamente.';

                $redir = 'index.php?controller=postulacion&action=index';
                if ($idVacante > 0) {
                    $redir .= '&id_vacante=' . $idVacante;
                }

                header('Location: ' . $redir);
                exit;

            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'No se pudo eliminar la postulación: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = 'ID de postulación inválido.';
        }

        header('Location: index.php?controller=postulacion&action=index');
        exit;
    }

    /**
     * Validación básica alineada con el modelo Postulacion
     */
    private function validarPostulacion(array $data): array
    {
        $errors = [];

        // id_vacante
        $idVacStr = (string)($data['id_vacante'] ?? '');
        if ($idVacStr === '' || !ctype_digit($idVacStr) || (int)$idVacStr <= 0) {
            $errors['id_vacante'] = 'La vacante es obligatoria y debe ser un ID válido.';
        }

        // id_candidato
        $idCandStr = (string)($data['id_candidato'] ?? '');
        if ($idCandStr === '' || !ctype_digit($idCandStr) || (int)$idCandStr <= 0) {
            $errors['id_candidato'] = 'El candidato es obligatorio y debe ser un ID válido.';
        }

        // estado
        $estado = strtoupper(trim((string)($data['estado'] ?? '')));
        $validos = ['POSTULADO','SCREENING','ENTREVISTA','PRUEBA','OFERTA','CONTRATADO','RECHAZADO'];

        if ($estado === '') {
            $errors['estado'] = 'El estado es obligatorio.';
        } elseif (!in_array($estado, $validos, true)) {
            $errors['estado'] = 'El estado indicado no es válido.';
        }

        // comentarios (opcional)
        $coment = trim((string)($data['comentarios'] ?? ''));
        if (mb_strlen($coment) > 1000) {
            $errors['comentarios'] = 'Los comentarios no deben exceder 1000 caracteres.';
        }

        return $errors;
    }
}