<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Entrevista.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../middleware/Auth.php';

class EntrevistaController
{
    /**
     * Lista de entrevistas.
     * - Si viene id_postulacion => filtra por esa postulación.
     * - Si no viene => muestra todas las entrevistas.
     * GET: ?controller=entrevista&action=index[&id_postulacion=1][&q=texto]
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search        = $_GET['q'] ?? null;
        $idPostulacion = isset($_GET['id_postulacion']) ? (int)$_GET['id_postulacion'] : 0;
        if ($idPostulacion <= 0) {
            $idPostulacion = null;
        }

        $entrevistas = Entrevista::all(500, 0, $search, $idPostulacion);

        $postulacion = null;
        if ($idPostulacion !== null) {
            $postulacion = Postulacion::findById($idPostulacion);
        }

        // $entrevistas y $postulacion disponibles en la vista
        require __DIR__ . '/../../public/views/reclutamiento/entrevistas/list.php';
    }

    /**
     * Mostrar formulario de nueva entrevista
     * GET: ?controller=entrevista&action=create[&id_postulacion=1]
     */
    public function create(): void
    {
        requireRole(1);

        $idPostulacion = isset($_GET['id_postulacion']) ? (int)$_GET['id_postulacion'] : 0;

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        $postulacion = $idPostulacion > 0 ? Postulacion::findById($idPostulacion) : null;

        // $postulacion, $idPostulacion, $errors, $old disponibles en la vista
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

        // datetime-local: 2025-12-04T10:30 -> 2025-12-04 10:30
        $programadaRaw = $_POST['programada_para'] ?? '';
        if (strpos($programadaRaw, 'T') !== false) {
            $programadaRaw = str_replace('T', ' ', $programadaRaw);
        }

        $data = [
            'id_postulacion' => $_POST['id_postulacion'] ?? '',
            'entrevistador'  => $_POST['entrevistador']  ?? '',
            'programada_para'=> $programadaRaw,
            'resultado'      => $_POST['resultado']      ?? 'PENDIENTE',
            'notas'          => $_POST['notas']          ?? '',
        ];

        $errors        = $this->validarEntrevista($data);
        $idPostulacion = (int)($data['id_postulacion'] ?? 0);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            $redir = 'index.php?controller=entrevista&action=create';
            if ($idPostulacion > 0) {
                $redir .= '&id_postulacion=' . $idPostulacion;
            }

            header('Location: ' . $redir);
            exit;
        }

        try {
            Entrevista::create($data);

            $_SESSION['flash_success'] = 'Entrevista creada correctamente.';

            $redir = 'index.php?controller=entrevista&action=index';
            if ($idPostulacion > 0) {
                $redir .= '&id_postulacion=' . $idPostulacion;
            }

            header('Location: ' . $redir);
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al crear la entrevista: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            $redir = 'index.php?controller=entrevista&action=create';
            if ($idPostulacion > 0) {
                $redir .= '&id_postulacion=' . $idPostulacion;
            }

            header('Location: ' . $redir);
            exit;
        }
    }

    /**
     * Mostrar formulario de edición
     * GET: ?controller=entrevista&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=entrevista&action=index');
            exit;
        }

        $entrevista = Entrevista::findById($id);
        if (!$entrevista) {
            header('Location: index.php?controller=entrevista&action=index');
            exit;
        }

        $idPostulacion = (int)$entrevista['id_postulacion'];

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        $postulacion = Postulacion::findById($idPostulacion);

        // $entrevista, $postulacion, $errors, $old disponibles en la vista
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
            header('Location: index.php?controller=entrevista&action=index');
            exit;
        }

        $programadaRaw = $_POST['programada_para'] ?? '';
        if (strpos($programadaRaw, 'T') !== false) {
            $programadaRaw = str_replace('T', ' ', $programadaRaw);
        }

        $data = [
            'id_postulacion' => $_POST['id_postulacion'] ?? '',
            'entrevistador'  => $_POST['entrevistador']  ?? '',
            'programada_para'=> $programadaRaw,
            'resultado'      => $_POST['resultado']      ?? '',
            'notas'          => $_POST['notas']          ?? '',
        ];

        $errors        = $this->validarEntrevista($data);
        $idPostulacion = (int)($data['id_postulacion'] ?? 0);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=entrevista&action=edit&id=' . $id);
            exit;
        }

        try {
            Entrevista::update($id, $data);

            $_SESSION['flash_success'] = 'Entrevista actualizada correctamente.';

            $redir = 'index.php?controller=entrevista&action=index';
            if ($idPostulacion > 0) {
                $redir .= '&id_postulacion=' . $idPostulacion;
            }

            header('Location: ' . $redir);
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
     * GET/POST: ?controller=entrevista&action=delete&id=1
     */
    public function delete(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            try {
                $entrevista    = Entrevista::findById($id);
                $idPostulacion = $entrevista ? (int)$entrevista['id_postulacion'] : 0;

                Entrevista::delete($id);
                $_SESSION['flash_success'] = 'Entrevista eliminada correctamente.';

                $redir = 'index.php?controller=entrevista&action=index';
                if ($idPostulacion > 0) {
                    $redir .= '&id_postulacion=' . $idPostulacion;
                }

                header('Location: ' . $redir);
                exit;

            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'No se pudo eliminar la entrevista: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = 'ID de entrevista inválido.';
        }

        header('Location: index.php?controller=entrevista&action=index');
        exit;
    }

    /**
     * Validación alineada con el modelo Entrevista
     */
    private function validarEntrevista(array $data): array
    {
        $errors = [];

        // id_postulacion
        $idPostStr = (string)($data['id_postulacion'] ?? '');
        if ($idPostStr === '' || !ctype_digit($idPostStr) || (int)$idPostStr <= 0) {
            $errors['id_postulacion'] = 'La postulación es obligatoria y debe ser un ID válido.';
        }

        // entrevistador (ID numérico)
        $idEntrevStr = (string)($data['entrevistador'] ?? '');
        if ($idEntrevStr === '' || !ctype_digit($idEntrevStr) || (int)$idEntrevStr <= 0) {
            $errors['entrevistador'] = 'El entrevistador es obligatorio y debe ser un ID válido.';
        }

        // programada_para
        $fechaStr = trim((string)($data['programada_para'] ?? ''));
        if ($fechaStr === '') {
            $errors['programada_para'] = 'La fecha y hora programada es obligatoria.';
        } else {
            $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $fechaStr)
                ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fechaStr);

            if (!$dt) {
                $errors['programada_para'] = 'La fecha y hora no es válida.';
            }
        }

        // resultado
        $resultado = strtoupper(trim((string)($data['resultado'] ?? '')));
        $permitidos = ['PENDIENTE','APROBADO','RECHAZADO'];
        if ($resultado === '' || !in_array($resultado, $permitidos, true)) {
            $errors['resultado'] = 'El resultado es inválido.';
        }

        // notas
        $notas = trim((string)($data['notas'] ?? ''));
        if (mb_strlen($notas) > 2000) {
            $errors['notas'] = 'Las notas no deben exceder 2000 caracteres.';
        }

        return $errors;
    }
}