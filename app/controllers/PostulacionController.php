<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../models/Vacante.php';
require_once __DIR__ . '/../models/Candidato.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../../config/db.php';

class PostulacionController
{
    /**
     * Lista de postulaciones
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        global $pdo;

        $search = trim((string)($_GET['q'] ?? ''));

        $sql = "
            SELECT 
                p.id_postulacion,
                p.id_vacante,
                p.id_candidato,
                p.estado,
                DATE(p.aplicada_en) AS fecha_postulacion,
                c.nombre AS candidato,
                CONCAT(e.nombre, ' – ', a.nombre_area, ' – ', pu.nombre_puesto) AS vacante
            FROM postulaciones p
            INNER JOIN candidatos c ON c.id_candidato = p.id_candidato
            INNER JOIN vacantes v   ON v.id_vacante = p.id_vacante
            INNER JOIN areas a      ON a.id_area = v.id_area
            INNER JOIN puestos pu   ON pu.id_puesto = v.id_puesto
            INNER JOIN empresas e   ON e.id_empresa = a.id_empresa
        ";

        $params = [];
        if ($search !== '') {
            $sql .= "
                WHERE 
                    c.nombre          LIKE :q
                    OR e.nombre       LIKE :q
                    OR a.nombre_area  LIKE :q
                    OR pu.nombre_puesto LIKE :q
                    OR p.estado       LIKE :q
            ";
            $params[':q'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY p.aplicada_en DESC, p.id_postulacion DESC LIMIT 500";

        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, \PDO::PARAM_STR);
        }
        $st->execute();
        $postulaciones = $st->fetchAll();

        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/list.php';
    }

    /**
     * Formulario de nueva postulación
     */
    public function create(): void
    {
        requireLogin();
        requireRole(1);

        global $pdo;

        // Catálogo de vacantes (Empresa – Área – Puesto)
        $sqlVac = "
            SELECT
                v.id_vacante,
                CONCAT(e.nombre, ' – ', a.nombre_area, ' – ', pu.nombre_puesto) AS etiqueta
            FROM vacantes v
            INNER JOIN areas a     ON a.id_area = v.id_area
            INNER JOIN empresas e  ON e.id_empresa = a.id_empresa
            INNER JOIN puestos pu  ON pu.id_puesto = v.id_puesto
            ORDER BY e.nombre, a.nombre_area, pu.nombre_puesto
        ";
        $vacantes = $pdo->query($sqlVac)->fetchAll();

        // Catálogo de candidatos
        $sqlCand = "SELECT id_candidato, nombre FROM candidatos ORDER BY nombre";
        $candidatos = $pdo->query($sqlCand)->fetchAll();

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/create.php';
    }

    /**
     * Guarda una nueva postulación
     */
    public function store(): void
    {
        requireLogin();
        requireRole(1);

        $data = [
            'id_vacante'        => $_POST['id_vacante']        ?? null,
            'id_candidato'      => $_POST['id_candidato']      ?? null,
            'estado'            => $_POST['estado']            ?? 'POSTULADO',
            'comentarios'       => $_POST['comentarios']       ?? null,
            'fecha_postulacion' => $_POST['fecha_postulacion'] ?? null,
        ];

        $errors  = [];
        $idVac   = (int)$data['id_vacante'];
        $idCand  = (int)$data['id_candidato'];
        $estado  = strtoupper(trim((string)$data['estado']));
        $fecha   = trim((string)$data['fecha_postulacion']);

        if ($idVac <= 0) {
            $errors['id_vacante'] = 'Debes seleccionar una vacante.';
        }
        if ($idCand <= 0) {
            $errors['id_candidato'] = 'Debes seleccionar un candidato.';
        }

        $estadosValidos = ['POSTULADO', 'SCREENING', 'ENTREVISTA', 'PRUEBA', 'OFERTA', 'RECHAZADO', 'CONTRATADO'];
        if (!in_array($estado, $estadosValidos, true)) {
            $errors['estado'] = 'La etapa seleccionada no es válida.';
        }

        if ($fecha === '') {
            $errors['fecha_postulacion'] = 'Debes indicar la fecha de postulación.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $errors['fecha_postulacion'] = 'La fecha debe tener el formato AAAA-MM-DD.';
        }

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;
            header('Location: index.php?controller=postulacion&action=create');
            exit;
        }

        // Crear usando el modelo (usa NOW() por defecto en aplicada_en)
        $newId = Postulacion::create([
            'id_vacante'   => $idVac,
            'id_candidato' => $idCand,
            'estado'       => $estado,
            'comentarios'  => $data['comentarios'],
        ]);

        // Ajustar aplicada_en a la fecha elegida
        if ($newId > 0 && $fecha !== '') {
            global $pdo;
            $st = $pdo->prepare("UPDATE postulaciones SET aplicada_en = :fecha WHERE id_postulacion = :id");
            $st->execute([
                ':fecha' => $fecha . ' 00:00:00',
                ':id'    => $newId,
            ]);
        }

        header('Location: index.php?controller=postulacion&action=index');
        exit;
    }

    /**
     * Formulario de edición de postulación
     */
    public function edit(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID de postulación inválido';
            return;
        }

        $postulacion = Postulacion::findById($id);
        if (!$postulacion) {
            http_response_code(404);
            echo 'Postulación no encontrada';
            return;
        }

        global $pdo;

        // Catálogo de vacantes
        $sqlVac = "
            SELECT
                v.id_vacante,
                CONCAT(e.nombre, ' – ', a.nombre_area, ' – ', pu.nombre_puesto) AS etiqueta
            FROM vacantes v
            INNER JOIN areas a     ON a.id_area = v.id_area
            INNER JOIN empresas e  ON e.id_empresa = a.id_empresa
            INNER JOIN puestos pu  ON pu.id_puesto = v.id_puesto
            ORDER BY e.nombre, a.nombre_area, pu.nombre_puesto
        ";
        $vacantes = $pdo->query($sqlVac)->fetchAll();

        // Catálogo de candidatos
        $sqlCand = "SELECT id_candidato, nombre FROM candidatos ORDER BY nombre";
        $candidatos = $pdo->query($sqlCand)->fetchAll();

        $errors = $_SESSION['errors'] ?? [];
        $old    = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/edit.php';
    }

    /**
     * Actualiza una postulación existente
     */
    public function update(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID de postulación inválido';
            return;
        }

        $data = [
            'id_vacante'        => $_POST['id_vacante']        ?? null,
            'id_candidato'      => $_POST['id_candidato']      ?? null,
            'estado'            => $_POST['estado']            ?? null,
            'comentarios'       => $_POST['comentarios']       ?? null,
            'fecha_postulacion' => $_POST['fecha_postulacion'] ?? null,
        ];

        $errors  = [];
        $idVac   = (int)$data['id_vacante'];
        $idCand  = (int)$data['id_candidato'];
        $estado  = strtoupper(trim((string)$data['estado']));
        $fecha   = trim((string)$data['fecha_postulacion']);

        if ($idVac <= 0) {
            $errors['id_vacante'] = 'Debes seleccionar una vacante.';
        }
        if ($idCand <= 0) {
            $errors['id_candidato'] = 'Debes seleccionar un candidato.';
        }

        $estadosValidos = ['POSTULADO', 'SCREENING', 'ENTREVISTA', 'PRUEBA', 'OFERTA', 'RECHAZADO', 'CONTRATADO'];
        if (!in_array($estado, $estadosValidos, true)) {
            $errors['estado'] = 'La etapa seleccionada no es válida.';
        }

        if ($fecha === '') {
            $errors['fecha_postulacion'] = 'Debes indicar la fecha de postulación.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $errors['fecha_postulacion'] = 'La fecha debe tener el formato AAAA-MM-DD.';
        }

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $data;

            header('Location: index.php?controller=postulacion&action=edit&id=' . $id);
            exit;
        }

        // Actualizar campos permitidos por el modelo
        $updateData = [
            'id_vacante'   => $idVac,
            'id_candidato' => $idCand,
            'estado'       => $estado,
            'comentarios'  => $data['comentarios'],
        ];

        Postulacion::update($id, $updateData);

        // Actualizar también la fecha aplicada_en
        global $pdo;
        $st = $pdo->prepare("UPDATE postulaciones SET aplicada_en = :fecha WHERE id_postulacion = :id");
        $st->execute([
            ':fecha' => $fecha . ' 00:00:00',
            ':id'    => $id,
        ]);

        header('Location: index.php?controller=postulacion&action=index');
        exit;
    }

    /**
     * Elimina una postulación
     */
    public function delete(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: index.php?controller=postulacion&action=index');
            exit;
        }

        Postulacion::delete($id);

        header('Location: index.php?controller=postulacion&action=index');
        exit;
    }
}