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

        // Checkbox "Ver también inactivas"
        $showInactive = isset($_GET['showInactive']) && $_GET['showInactive'] === '1';

        // Por defecto (sin showInactive) → solo activas
        // Empresa::all:
        //   - true  => WHERE activa = 1
        //   - false => WHERE activa = 0 
        //   - null  => sin filtro (todas)
        $onlyActive = $showInactive ? null : true;

        $turnos = Turno::all(500, 0, $search, $onlyActive);

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
        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

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

        // Normalizamos días laborales (pueden venir como array de checkboxes)
        $diasInput = $_POST['dias_laborales'] ?? null;
        if (is_array($diasInput)) {
                $diasLaborales = $diasInput; // El modelo Turno ya admite array
        } else {
            $diasLaborales = (string)$diasInput;
        }           

         // Data cruda del formulario
        $data = [
            'nombre_turno'       => $_POST['nombre_turno']       ?? '',
            'hora_entrada'       => $_POST['hora_entrada']       ?? '',
            'hora_salida'        => $_POST['hora_salida']        ?? '',
            'tolerancia_minutos' => $_POST['tolerancia_minutos'] ?? '',
            'dias_laborales'     => $diasLaborales,
        ];

         // Validar
        $errors = $this->validarTurno($data, null);

        if (!empty($errors)) {
            // Guardamos errores y old input en sesión y redirigimos
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=turno&action=create');
            exit;
        }

        try {
            Turno::create($data);

            $_SESSION['flash_success'] = 'Turno creado correctamente.';
            header('Location: index.php?controller=turno&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al crear el turno: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;

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

        // Errores y old_input si es un POST fallido
        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        // $turno, $errors, $old disponibles en la vista
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

        // Validar con ID (para la unicidad del nombre)
        $errors = $this->validarTurno($data, $id);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: index.php?controller=turno&action=edit&id=' . $id);
            exit;
        }


        try {
        
            Turno::update($id, $data);

            $_SESSION['flash_success'] = 'Turno actualizado correctamente.';
            header('Location: index.php?controller=turno&action=index');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error al actualizar el turno: ' . $e->getMessage();
            $_SESSION['old_input']   = $data;
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

    /**
     * Valida datos de turno para crear/editar
     */
    private function validarTurno(array $data, ?int $idTurno = null): array
    {
        $errors = [];

        // ---- nombre_turno ----
        $nombre = trim($data['nombre_turno'] ?? '');

        if ($nombre === '') {
            $errors['nombre_turno'] = 'El nombre del turno es obligatorio.';
        } elseif (mb_strlen($nombre) > 60) {
            $errors['nombre_turno'] = 'El nombre no debe exceder 60 caracteres.';
        } elseif (Turno::existsByNombre($nombre, $idTurno)) {
            $errors['nombre_turno'] = 'Ya existe un turno con ese nombre.';
        }

        // ---- hora_entrada y hora_salida ----
        $horaEntrada = trim($data['hora_entrada'] ?? '');
        $horaSalida  = trim($data['hora_salida'] ?? '');

        $entrada = \DateTimeImmutable::createFromFormat('H:i', $horaEntrada);
        $salida  = \DateTimeImmutable::createFromFormat('H:i', $horaSalida);

        if (!$entrada) {
            $errors['hora_entrada'] = 'La hora de entrada no es válida (usa formato HH:MM).';
        }
        if (!$salida) {
            $errors['hora_salida'] = 'La hora de salida no es válida (usa formato HH:MM).';
        }

        if ($entrada && $salida) {
            if ($entrada == $salida) {
                $errors['hora_salida'] = 'La hora de salida debe ser distinta a la de entrada.';
            } elseif ($entrada > $salida) {
                // Versión simple: no aceptamos turnos nocturnos
                $errors['hora_salida'] = 'La hora de salida debe ser mayor que la hora de entrada.';
            } else {
                // (Opcional) validar duración razonable, ej. 4 a 12 horas
                $diffSeconds = $salida->getTimestamp() - $entrada->getTimestamp();
                $diffMinutes = (int) round($diffSeconds / 60);

                if ($diffMinutes < 240) { // 4 h
                    $errors['hora_salida'] = 'La duración del turno es demasiado corta (mínimo 4 horas).';
                } elseif ($diffMinutes > 720) { // 12 h
                    $errors['hora_salida'] = 'La duración del turno es demasiado larga (máximo 12 horas).';
                }
            }
        }

        // ---- tolerancia_minutos ----
        $tolStr = (string)($data['tolerancia_minutos'] ?? '');

        if ($tolStr === '' || !ctype_digit($tolStr)) {
            $errors['tolerancia_minutos'] = 'La tolerancia debe ser un número entero en minutos.';
        } else {
            $tol = (int)$tolStr;
            if ($tol < 0 || $tol > 120) {
                $errors['tolerancia_minutos'] = 'La tolerancia debe estar entre 0 y 120 minutos.';
            }
        }

        // ---- dias_laborales ----
        $diasPermitidos = ['L','M','X','J','V','S','D'];

        $dias = $data['dias_laborales'] ?? [];
        if (is_string($dias)) {
            $dias = $dias === '' ? [] : explode(',', $dias);
        }

        if (!is_array($dias) || count($dias) === 0) {
            $errors['dias_laborales'] = 'Selecciona al menos un día laboral.';
        } else {
            foreach ($dias as $d) {
                if (!in_array($d, $diasPermitidos, true)) {
                    $errors['dias_laborales'] = 'Hay días laborales inválidos.';
                    break;
                }
            }
        }

        return $errors;
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
            Turno::setActive($id, $active);
            $_SESSION['flash_success'] = $active
                ? 'Turno activada correctamente.'
                : 'Turno desactivada correctamente.';
        }

        header('Location: index.php?controller=turno&action=index');
        exit;
    }
}
