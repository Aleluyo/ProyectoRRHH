<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Vacante.php';
require_once __DIR__ . '/../middleware/Auth.php';

class VacanteController
{
    /**
     * Listado de vacantes
     * GET: ?controller=vacante&action=index&q=texto&estatus=ABIERTA
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search  = $_GET['q']        ?? null;
        $estatus = $_GET['estatus']  ?? null;

        $vacantes = Vacante::all(500, 0, $search, $estatus);

        // $vacantes, $search, $estatus disponibles en la vista
        require __DIR__ . '/../../public/views/reclutamiento/vacantes/list.php';
    }

    /**
     * Mostrar formulario de nueva vacante (requisición)
     * GET: ?controller=vacante&action=create
     */
    public function create(): void
    {
        requireRole(1);

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        // $errors, $old disponibles en la vista
        require __DIR__ . '/../../public/views/reclutamiento/vacantes/create.php';
    }

    /**
     * Guardar nueva vacante
     * POST: ?controller=vacante&action=store
     */
    public function store(): void
    {
        requireRole(1);
        session_start();

        // Puedes ajustar esta parte según cómo guardes el usuario en sesión
        $solicitadaPor = $_POST['solicitada_por'] ?? ($_SESSION['user_id'] ?? null);

        $data = [
            'id_area'           => $_POST['id_area']          ?? null,
            'id_puesto'         => $_POST['id_puesto']        ?? null,
            'id_ubicacion'      => $_POST['id_ubicacion']     ?? null,
            'solicitada_por'    => $solicitadaPor,
            'estatus'           => $_POST['estatus']          ?? 'EN_APROBACION',
            'requisitos'        => $_POST['requisitos']       ?? '',
            'fecha_publicacion' => $_POST['fecha_publicacion'] ?? null,
        ];

        $errors = $this->validarVacante($data, null);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=vacante&action=create');
            exit;
        }

        try {
            Vacante::create($data);

            $_SESSION['flash_success'] = 'Vacante creada correctamente.';
            header('Location: index.php?controller=vacante&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al crear la vacante: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

            header('Location: index.php?controller=vacante&action=create');
            exit;
        }
    }

    /**
     * Mostrar formulario de edición
     * GET: ?controller=vacante&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $vacante = Vacante::findById($id);
        if (!$vacante) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        // $vacante, $errors, $old disponibles en la vista
        require __DIR__ . '/../../public/views/reclutamiento/vacantes/edit.php';
    }

    /**
     * Actualizar vacante
     * POST: ?controller=vacante&action=update&id=1
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
            'id_area'           => $_POST['id_area']          ?? null,
            'id_puesto'         => $_POST['id_puesto']        ?? null,
            'id_ubicacion'      => $_POST['id_ubicacion']     ?? null,
            'solicitada_por'    => $_POST['solicitada_por']   ?? null,
            'estatus'           => $_POST['estatus']          ?? '',
            'requisitos'        => $_POST['requisitos']       ?? '',
            'fecha_publicacion' => $_POST['fecha_publicacion'] ?? null,
        ];

        $errors = $this->validarVacante($data, $id);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: index.php?controller=vacante&action=edit&id=' . $id);
            exit;
        }

        try {
            Vacante::update($id, $data);

            $_SESSION['flash_success'] = 'Vacante actualizada correctamente.';
            header('Location: index.php?controller=vacante&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al actualizar la vacante: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;
            header('Location: index.php?controller=vacante&action=edit&id=' . $id);
            exit;
        }
    }

    /**
     * Eliminar vacante
     * GET/POST: ?controller=vacante&action=delete&id=1
     */
    public function delete(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            try {
                Vacante::delete($id);
                $_SESSION['flash_success'] = 'Vacante eliminada correctamente.';
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'No se pudo eliminar la vacante: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = 'ID de vacante inválido.';
        }

        header('Location: index.php?controller=vacante&action=index');
        exit;
    }

    /**
     * Validación básica de vacante.
     */
    private function validarVacante(array $data, ?int $idVacante = null): array
    {
        $errors = [];

        // id_area
        $idArea = (int)($data['id_area'] ?? 0);
        if ($idArea <= 0) {
            $errors['id_area'] = 'Debes seleccionar un área.';
        }

        // id_puesto
        $idPuesto = (int)($data['id_puesto'] ?? 0);
        if ($idPuesto <= 0) {
            $errors['id_puesto'] = 'Debes seleccionar un puesto.';
        }

        // id_ubicacion
        $idUbicacion = (int)($data['id_ubicacion'] ?? 0);
        if ($idUbicacion <= 0) {
            $errors['id_ubicacion'] = 'Debes seleccionar una ubicación.';
        }

        // solicitada_por
        $solicitadaPor = (int)($data['solicitada_por'] ?? 0);
        if ($solicitadaPor <= 0) {
            $errors['solicitada_por'] = 'No se pudo identificar al usuario solicitante.';
        }

        // estatus
        $estatus = trim((string)($data['estatus'] ?? ''));
        if ($estatus === '') {
            $errors['estatus'] = 'El estatus de la vacante es obligatorio.';
        }

        // fecha_publicacion (opcional, pero si viene, validar formato)
        $fechaPub = $data['fecha_publicacion'] ?? null;
        if ($fechaPub !== null && trim((string)$fechaPub) !== '') {
            $f = \DateTimeImmutable::createFromFormat('Y-m-d', (string)$fechaPub)
              ?: \DateTimeImmutable::createFromFormat('d/m/Y', (string)$fechaPub);
            if (!$f) {
                $errors['fecha_publicacion'] = 'La fecha de publicación no es válida.';
            }
        }

        return $errors;
    }
}