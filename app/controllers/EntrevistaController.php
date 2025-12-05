<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Entrevista.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../middleware/Auth.php';

class EntrevistaController
{
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $search = $_GET['q'] ?? null;

        $entrevistas = Entrevista::all(500, 0, $search);

        require __DIR__ . '/../../public/views/reclutamiento/entrevistas/list.php';
    }

    public function create(): void
    {
        requireLogin();
        requireRole(1);

        // Postulaciones disponibles para el <select>
        $postulaciones = Postulacion::listaParaEntrevistas();

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/entrevistas/create.php';
    }

    public function store(): void
    {
        requireLogin();
        requireRole(1);

        $data = [
            'id_postulacion' => $_POST['id_postulacion'] ?? '',
            'programada_para' => $_POST['programada_para'] ?? '',
            'resultado' => $_POST['resultado'] ?? 'PENDIENTE',
            'notas' => $_POST['notas'] ?? '',
        ];

        $errors = $this->validarEntrevista($data);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: index.php?controller=entrevista&action=create');
            exit;
        }

        try {
            Entrevista::create($data);
        } catch (\Throwable $e) {
            $_SESSION['errors'] = ['general' => $e->getMessage()];
            $_SESSION['old_input'] = $data;
            header('Location: index.php?controller=entrevista&action=create');
            exit;
        }

        header('Location: index.php?controller=entrevista&action=index');
        exit;
    }
    public function edit(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['errors'] = ['general' => 'ID de entrevista inválido (ID: ' . htmlspecialchars((string) ($_GET['id'] ?? 'NULL')) . ').'];
            header('Location: index.php?controller=entrevista&action=index');
            exit;
        }

        $entrevista = Entrevista::findById($id);
        if (!$entrevista) {
            $_SESSION['errors'] = ['general' => "Entrevista no encontrada en base de datos (ID: $id)."];
            header('Location: index.php?controller=entrevista&action=index');
            exit;
        }

        $postulaciones = Postulacion::listaParaEntrevistas();

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/entrevistas/edit.php';
    }

    public function update(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            echo "Entrevista no encontrada.";
            return;
        }

        $data = [
            'id_postulacion' => $_POST['id_postulacion'] ?? '',
            'programada_para' => $_POST['programada_para'] ?? '',
            'resultado' => $_POST['resultado'] ?? 'PENDIENTE',
            'notas' => $_POST['notas'] ?? '',
        ];

        $errors = $this->validarEntrevista($data);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data + ['id' => $id];
            header('Location: index.php?controller=entrevista&action=edit&id=' . $id);
            exit;
        }

        try {
            Entrevista::update($id, $data);
        } catch (\Throwable $e) {
            $_SESSION['errors'] = ['general' => $e->getMessage()];
            $_SESSION['old_input'] = $data + ['id' => $id];
            header('Location: index.php?controller=entrevista&action=edit&id=' . $id);
            exit;
        }

        header('Location: index.php?controller=entrevista&action=index');
        exit;
    }

    public function delete(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id > 0) {
            Entrevista::delete($id);
        }

        header('Location: index.php?controller=entrevista&action=index');
        exit;
    }

    private function validarEntrevista(array $data): array
    {
        $errors = [];

        $idPost = (int) ($data['id_postulacion'] ?? 0);
        if ($idPost <= 0) {
            $errors['id_postulacion'] = 'Debes seleccionar una postulación.';
        } elseif (!Postulacion::findById($idPost)) {
            $errors['id_postulacion'] = 'La postulación seleccionada no existe.';
        }

        $fechaRaw = trim((string) ($data['programada_para'] ?? ''));
        if ($fechaRaw === '') {
            $errors['programada_para'] = 'La fecha y hora son obligatorias.';
        } else {
            // Aceptamos formatos de datetime-local y similares
            $ok = \DateTime::createFromFormat('Y-m-d\TH:i', $fechaRaw)
                ?: \DateTime::createFromFormat('Y-m-d H:i', $fechaRaw)
                ?: \DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);

            if (!$ok) {
                $errors['programada_para'] = 'Formato de fecha y hora inválido.';
            }
        }

        $resultado = strtoupper(trim((string) ($data['resultado'] ?? '')));
        if ($resultado === '') {
            $errors['resultado'] = 'Debes seleccionar un resultado.';
        } elseif (!in_array($resultado, ['PENDIENTE', 'APROBADO', 'RECHAZADO'], true)) {
            $errors['resultado'] = 'Resultado inválido.';
        }

        // notas es opcional

        return $errors;
    }
}