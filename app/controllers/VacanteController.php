<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Vacante.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Area.php';
require_once __DIR__ . '/../models/Puesto.php';
require_once __DIR__ . '/../models/Ubicacion.php';
require_once __DIR__ . '/../middleware/Auth.php';

class VacanteController
{
    /**
     * Lista de vacantes
     * GET: ?controller=vacante&action=index&q=texto
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search = $_GET['q'] ?? null;

        // Vacante::all ya debe traer joins a empresa, área, puesto, ubicación
        $vacantes = Vacante::all(500, 0, $search);

        if (!is_array($vacantes)) {
            $vacantes = [];
        }

        require __DIR__ . '/../../public/views/reclutamiento/vacantes/list.php';
    }

    /**
     * Mostrar formulario de nueva vacante
     * GET: ?controller=vacante&action=create
     *
     * Aquí precargamos catálogos (opción A) para hacer
     * los selects dependientes en el navegador.
     */
    public function create(): void
    {
        requireLogin();
        requireRole(1);

        // Errores y valores anteriores (si venimos de un POST fallido)
        $errors = $_SESSION['errors']    ?? [];
        $old    = $_SESSION['old_input'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old_input']);

        // ---------- Catálogos para los selects dependientes ----------
        // Se usa un límite grande porque son catálogos pequeños
        $empresas    = Empresa::all(1000, 0, null);
        $areas       = Area::all(1000, 0, null);
        $puestos     = Puesto::all(1000, 0, null);
        $ubicaciones = Ubicacion::all(1000, 0, null);

        // En la vista estarán disponibles:
        // $errors, $old, $empresas, $areas, $puestos, $ubicaciones
        require __DIR__ . '/../../public/views/reclutamiento/vacantes/create.php';
    }

    /**
     * Guardar nueva vacante
     * POST: ?controller=vacante&action=store
     */
    public function store(): void
    {
        requireLogin();
        requireRole(1);
        session_start();

        // Datos crudos del formulario
        $data = [
            // id_empresa lo usamos solo para lógica / validación, NO se guarda en la tabla
            'id_empresa'        => $_POST['id_empresa']   ?? '',
            'id_area'           => $_POST['id_area']      ?? '',
            'id_puesto'         => $_POST['id_puesto']    ?? '',
            'id_ubicacion'      => $_POST['id_ubicacion'] ?? '',
            'solicitada_por'    => $_POST['solicitada_por'] ?? '',
            'estatus'           => $_POST['estatus']      ?? 'EN_APROBACION',
            'requisitos'        => $_POST['requisitos']   ?? '',
            'fecha_publicacion' => $_POST['fecha_publicacion'] ?? '',
        ];

        $errors = $this->validarVacante($data, null);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=vacante&action=create');
            exit;
        }

        try {
            // Vacante::create solo utiliza los campos que le corresponden
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
        requireLogin();
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

        // Catálogos para selects dependientes también en edición
        $empresas    = Empresa::all(1000, 0, null);
        $areas       = Area::all(1000, 0, null);
        $puestos     = Puesto::all(1000, 0, null);
        $ubicaciones = Ubicacion::all(1000, 0, null);

        require __DIR__ . '/../../public/views/reclutamiento/vacantes/edit.php';
    }

    /**
     * Actualizar vacante
     * POST: ?controller=vacante&action=update&id=1
     */
    public function update(): void
    {
        requireLogin();
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: index.php?controller=vacante&action=index');
            exit;
        }

        $data = [
            'id_empresa'        => $_POST['id_empresa']   ?? '',
            'id_area'           => $_POST['id_area']      ?? '',
            'id_puesto'         => $_POST['id_puesto']    ?? '',
            'id_ubicacion'      => $_POST['id_ubicacion'] ?? '',
            'solicitada_por'    => $_POST['solicitada_por'] ?? '',
            'estatus'           => $_POST['estatus']      ?? 'EN_APROBACION',
            'requisitos'        => $_POST['requisitos']   ?? '',
            'fecha_publicacion' => $_POST['fecha_publicacion'] ?? '',
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
        requireLogin();
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
     * Validación común para crear / editar
     */
    private function validarVacante(array $data, ?int $idVacante = null): array
    {
        $errors = [];

        // id_empresa (solo para consistencia de combos)
        $idEmpresa = (int)($data['id_empresa'] ?? 0);
        if ($idEmpresa <= 0) {
            $errors['id_empresa'] = 'Selecciona una empresa.';
        }

        // id_area
        $idArea = (int)($data['id_area'] ?? 0);
        if ($idArea <= 0) {
            $errors['id_area'] = 'Selecciona un área.';
        }

        // id_puesto
        $idPuesto = (int)($data['id_puesto'] ?? 0);
        if ($idPuesto <= 0) {
            $errors['id_puesto'] = 'Selecciona un puesto.';
        }

        // id_ubicacion (puede ser null en tu tabla, pero aquí pedimos uno)
        $idUbicacion = (int)($data['id_ubicacion'] ?? 0);
        if ($idUbicacion <= 0) {
            $errors['id_ubicacion'] = 'Selecciona una ubicación.';
        }

        // solicitada_por
        $sol = (int)($data['solicitada_por'] ?? 0);
        if ($sol <= 0) {
            $errors['solicitada_por'] = 'Indica el ID del usuario solicitante.';
        }

        // estatus
        $estatusPermitidos = ['EN_APROBACION','APROBADA','ABIERTA','EN_PROCESO','CERRADA'];
        $estatus = strtoupper(trim((string)($data['estatus'] ?? '')));
        if (!in_array($estatus, $estatusPermitidos, true)) {
            $errors['estatus'] = 'Estatus inválido.';
        }

        // fecha_publicacion (opcional)
        $fecha = trim((string)($data['fecha_publicacion'] ?? ''));
        if ($fecha !== '') {
            $dt = \DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$dt) {
                $errors['fecha_publicacion'] = 'La fecha de publicación no es válida.';
            }
        }

        // requisitos (opcional, solo límite de tamaño)
        $req = trim((string)($data['requisitos'] ?? ''));
        if (strlen($req) > 1000) {
            $errors['requisitos'] = 'Los requisitos/comentarios son demasiado largos.';
        }

        // Aquí podrías agregar validaciones cruzadas:
        // - que el área pertenezca a la empresa seleccionada
        // - que el puesto pertenezca al área
        // - que la ubicación sea de esa empresa
        // (Para simplificar, las omitimos.)

        return $errors;
    }
}