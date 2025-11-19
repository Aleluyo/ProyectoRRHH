<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Turno.php';
require_once __DIR__ . '/../middleware/Auth.php';

class TurnoController
{
    /**
     * Lista de turnos
     * GET: ?controller=turno&action=index&q=texto
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search = $_GET['q'] ?? null;

        $turnos = Turno::all(500, 0, $search);

        // Disponibles en la vista:
        // $turnos, $search
        require __DIR__ . '/../../public/views/organizacional/turnos/list.php';
    }

    /**
     * Mostrar formulario de nuevo turno
     * GET: ?controller=turno&action=create
     */
    public function create(): void
    {
        requireRole(1);

        // Opcional: pasar variables por defecto
        $errors = [];
        $old    = [];

        require __DIR__ . '/../../public/views/organizacional/turnos/create.php';
    }

    /**
     * Guardar nuevo turno
     * POST: ?controller=turno&action=store
     */
    public function store(): void
    {
        requireRole(1);
        session_start();

        try {
            // Normalizamos días laborales (pueden venir como array de checkboxes)
            $diasInput = $_POST['dias_laborales'] ?? null;
            if (is_array($diasInput)) {
                $diasLaborales = $diasInput; // El modelo Turno ya admite array
            } else {
                $diasLaborales = (string)$diasInput;
            }

            $data = [
                'nombre_turno'      => $_POST['nombre_turno']      ?? '',
                'hora_entrada'      => $_POST['hora_entrada']      ?? '',
                'hora_salida'       => $_POST['hora_salida']       ?? '',
                'tolerancia_minutos'=> $_POST['tolerancia_minutos']?? 10,
                'dias_laborales'    => $diasLaborales,
            ];

            $id = Turno::create($data);

            $_SESSION['flash_success'] = 'Turno creado correctamente.';
            header('Location: index.php?controller=turno&action=index');
            exit;

        } catch (\Throwable $e) {
            // En caso de error, recargamos el formulario con mensajes
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;

            header('Location: index.php?controller=turno&action=create');
            exit;
        }
    }

    /**
     * Mostrar formulario de edición
     * GET: ?controller=turno&action=edit&id=1
     */
    public function edit(): void
    {
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=turno&action=index');
            exit;
        }

        $turno = Turno::findById($id);

        if (!$turno) {
            header('Location: index.php?controller=turno&action=index');
            exit;
        }

        // $turno disponible en la vista
        $errors = [];
        require __DIR__ . '/../../public/views/organizacional/turnos/edit.php';
    }

    /**
     * Actualizar turno
     * POST: ?controller=turno&action=update&id=1
     */
    public function update(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=turno&action=index');
            exit;
        }

        try {
            $diasInput = $_POST['dias_laborales'] ?? null;
            if (is_array($diasInput)) {
                $diasLaborales = $diasInput;
            } else {
                $diasLaborales = (string)$diasInput;
            }

            $data = [
                'nombre_turno'      => $_POST['nombre_turno']      ?? '',
                'hora_entrada'      => $_POST['hora_entrada']      ?? '',
                'hora_salida'       => $_POST['hora_salida']       ?? '',
                'tolerancia_minutos'=> $_POST['tolerancia_minutos']?? 10,
                'dias_laborales'    => $diasLaborales,
            ];

            Turno::update($id, $data);

            $_SESSION['flash_success'] = 'Turno actualizado correctamente.';
            header('Location: index.php?controller=turno&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $_SESSION['old_input']   = $_POST;
            header('Location: index.php?controller=turno&action=edit&id=' . $id);
            exit;
        }
    }

    /**
     * Eliminar turno (DELETE duro)
     * GET/POST: ?controller=turno&action=delete&id=1
     */
    public function delete(): void
    {
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            try {
                Turno::delete($id);
                $_SESSION['flash_success'] = 'Turno eliminado correctamente.';
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'No se pudo eliminar el turno: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = 'ID de turno inválido.';
        }

        header('Location: index.php?controller=turno&action=index');
        exit;
    }
}
