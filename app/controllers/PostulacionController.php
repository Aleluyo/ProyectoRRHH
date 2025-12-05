<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * Modelo Postulacion para RRHH_TEC
 * Une candidatos con vacantes y maneja el estado del proceso.
 */
class PostulacionController
{
    private const ALLOWED_FIELDS = [
        'id_vacante',
        'id_candidato',
        'estado',
        'comentarios',
    ];

    private const ESTADOS_VALIDOS = [
        'POSTULADO',
        'SCREENING',
        'ENTREVISTA',
        'PRUEBA',
        'OFERTA',
        'CONTRATADO',
        'RECHAZADO',
    ];

    public function index(): void
    {
        requireLogin();
        requireRole(1); // Ajusta el rol si es necesario

        $search = $_GET['q'] ?? null;

        $postulaciones = Postulacion::all(500, 0, $search);

        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/list.php';
    }

    /**
     * Devuelve una postulación por ID.
     */
    public static function findById(int $id): ?array
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $sql = "SELECT p.*, 
                       c.nombre AS candidato_nombre,
                       c.correo AS candidato_correo
                FROM postulaciones p
                LEFT JOIN candidatos c ON c.id_candidato = p.id_candidato
                WHERE p.id_postulacion = ?
                LIMIT 1";

        $st = $pdo->prepare($sql);
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    /**
     * Lista de postulaciones de una vacante, con paginado y filtro por estado.
     */
    public static function byVacante(
        int $idVacante,
        int $limit = 500,
        int $offset = 0,
        ?string $estado = null
    ): array {
        global $pdo;

        if ($idVacante <= 0) {
            throw new \InvalidArgumentException("ID de vacante inválido.");
        }

        $limit = max(1, min($limit, 1000));
        $offset = max(0, $offset);

        $where = ['p.id_vacante = :idVacante'];
        $params = [':idVacante' => $idVacante];

        if ($estado !== null && trim($estado) !== '') {
            $estado = self::normalizarEstado($estado);
            $where[] = 'p.estado = :estado';
            $params[':estado'] = $estado;
        }

        $sql = "SELECT p.*,
                       c.nombre AS candidato_nombre,
                       c.correo AS candidato_correo
                FROM postulaciones p
                LEFT JOIN candidatos c ON c.id_candidato = p.id_candidato
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.aplicada_en DESC
                LIMIT :limit OFFSET :offset";

        $st = $pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }

        $st->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $st->execute();
        return $st->fetchAll();
    }

    /**
     * Lista simplificada de postulaciones para usarse en selects de Entrevistas.
     * Devuelve: id_postulacion y una etiqueta descriptiva (label_postulacion).
     */
    public static function listaParaEntrevistas(): array
    {
        global $pdo;

        $sql = "
            SELECT
                p.id_postulacion,
                v.id_vacante,
                a.nombre_area,
                pu.nombre_puesto,
                c.nombre AS candidato_nombre,
                CONCAT(
                    'Vacante ', v.id_vacante, ' · ',
                    COALESCE(a.nombre_area, 'Sin área'), ' · ',
                    COALESCE(pu.nombre_puesto, 'Sin puesto'), ' · ',
                    COALESCE(c.nombre, 'Sin candidato')
                ) AS label_postulacion
            FROM postulaciones p
            INNER JOIN vacantes v   ON v.id_vacante   = p.id_vacante
            LEFT  JOIN areas    a   ON a.id_area      = v.id_area
            LEFT  JOIN puestos  pu  ON pu.id_puesto   = v.id_puesto
            LEFT  JOIN candidatos c ON c.id_candidato = p.id_candidato
            ORDER BY p.id_postulacion ASC
        ";

        $st = $pdo->query($sql);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Crea una postulación.
     */
    /**
     * Muestra el formulario de creación.
     */
    public function create(): void
    {
        requireLogin();
        requireRole(1);

        require_once __DIR__ . '/../models/Vacante.php';
        require_once __DIR__ . '/../models/Candidato.php';

        $vacantes = Vacante::all();
        $candidatos = Candidato::all();

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/create.php';
    }

    /**
     * Procesa el formulario de creación.
     */
    public function store(): void
    {
        requireLogin();
        requireRole(1);

        $data = [
            'id_vacante' => $_POST['id_vacante'] ?? '',
            'id_candidato' => $_POST['id_candidato'] ?? '',
            'estado' => $_POST['estado'] ?? 'POSTULADO',
            'fecha_aplicacion' => $_POST['fecha_aplicacion'] ?? date('Y-m-d'),
            'comentarios' => $_POST['comentarios'] ?? '',
        ];

        // Validar duplicados
        $idVacante = (int) ($data['id_vacante']);
        $idCandidato = (int) ($data['id_candidato']);

        if ($idVacante > 0 && $idCandidato > 0) {
            if (Postulacion::exists($idVacante, $idCandidato)) {
                $_SESSION['errors'] = ['general' => 'El candidato seleccionado ya está postulado a esta vacante.'];
                $_SESSION['old_input'] = $data;
                header('Location: index.php?controller=postulacion&action=create');
                exit;
            }
        }

        try {
            Postulacion::create($data);
        } catch (\Throwable $e) {
            $_SESSION['errors'] = ['general' => $e->getMessage()];
            $_SESSION['old_input'] = $data;
            header('Location: index.php?controller=postulacion&action=create');
            exit;
        }

        header('Location: index.php?controller=postulacion&action=index');
        exit;
    }

    /**
     * Actualiza campos de una postulación (incluyendo estado).
     */
    /**
     * Muestra el formulario de edición.
     */
    public function edit(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['errors'] = ['general' => 'ID de postulación inválido.'];
            header('Location: index.php?controller=postulacion&action=index');
            exit;
        }

        $postulacion = Postulacion::findById($id);
        if (!$postulacion) {
            $_SESSION['errors'] = ['general' => 'Postulación no encontrada.'];
            header('Location: index.php?controller=postulacion&action=index');
            exit;
        }

        require_once __DIR__ . '/../models/Vacante.php';
        require_once __DIR__ . '/../models/Candidato.php';

        $vacantes = Vacante::all();
        $candidatos = Candidato::all();

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);

        // Si no hay input viejo (primera carga), llenamos con datos de la DB
        if (empty($old)) {
            $old = [
                'id_vacante' => $postulacion['id_vacante'],
                'id_candidato' => $postulacion['id_candidato'],
                'estado' => $postulacion['estado'],
                'fecha_aplicacion' => $postulacion['aplicada_en'] ? date('Y-m-d', strtotime($postulacion['aplicada_en'])) : '',
                'comentarios' => $postulacion['comentarios'],
            ];
        }

        require __DIR__ . '/../../public/views/reclutamiento/postulaciones/edit.php';
    }

    /**
     * Procesa el formulario de edición.
     */
    public function update(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            $_SESSION['errors'] = ['general' => 'ID de postulación inválido.'];
            header('Location: index.php?controller=postulacion&action=index');
            exit;
        }

        $data = [
            'id_vacante' => $_POST['id_vacante'] ?? '',
            'id_candidato' => $_POST['id_candidato'] ?? '',
            'estado' => $_POST['estado'] ?? 'POSTULADO',
            'fecha_aplicacion' => $_POST['fecha_aplicacion'] ?? date('Y-m-d'),
            'comentarios' => $_POST['comentarios'] ?? '',
            // 'aplicada_en' se podría actualizar si el modelo lo permite, 
            // pero el create usa NOW(). Aquí usaremos fecha_aplicacion si el modelo lo soporta 
            // o asumimos que se actualiza 'aplicada_en' o similar.
            // Revisando el modelo update(), usa ALLOWED_FIELDS. 'aplicada_en' no está en ALLOWED_FIELDS por defecto en el código original,
            // pero el usuario podría querer cambiar la fecha. 
            // Por seguridad/simplicidad y respetar el código original del modelo, 
            // pasaremos los datos y el modelo filtrará.
        ];

        // NOTA: El modelo originalPostulacion::update filtra por ALLOWED_FIELDS.
        // Si 'fecha_aplicacion' o 'aplicada_en' no están allí, no se actualizará la fecha.
        // Eso es comportamiento del modelo existente.

        try {
            Postulacion::update($id, $data);
        } catch (\Throwable $e) {
            $_SESSION['errors'] = ['general' => $e->getMessage()];
            $_SESSION['old_input'] = $data + ['id' => $id];
            header('Location: index.php?controller=postulacion&action=edit&id=' . $id);
            exit;
        }

        header('Location: index.php?controller=postulacion&action=index');
        exit;
    }

    /**
     * Cambia el estado de una postulación (atajo).
     */
    public static function cambiarEstado(int $id, string $estado, ?string $comentarios = null): void
    {
        global $pdo;

        if ($id <= 0) {
            throw new \InvalidArgumentException("ID de postulación inválido.");
        }

        $estado = self::normalizarEstado($estado);
        $comentarios = $comentarios !== null ? trim($comentarios) : null;

        $sql = "UPDATE postulaciones
                SET estado = ?, comentarios = ?
                WHERE id_postulacion = ?";

        $st = $pdo->prepare($sql);
        $st->execute([
            $estado,
            $comentarios !== '' ? $comentarios : null,
            $id
        ]);
    }

    /**
     * Elimina una postulación.
     */
    public function delete(): void
    {
        requireLogin();
        requireRole(1);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            session_start();
            $_SESSION['flash_error'] = "ID de postulación inválido.";
            header('Location: index.php?controller=postulacion&action=index');
            exit;
        }

        try {
            Postulacion::delete($id);
            session_start();
            $_SESSION['flash_success'] = "Postulación eliminada correctamente.";
        } catch (\Throwable $e) {
            session_start();
            $_SESSION['flash_error'] = 'No se pudo eliminar la postulación: ' . $e->getMessage();
        }

        header('Location: index.php?controller=postulacion&action=index');
        exit;
    }

    /* ====================== Helpers internos ====================== */

    private static function normalizarId($valor, string $labelCampo): int
    {
        $id = (int) $valor;
        if ($id <= 0) {
            throw new \InvalidArgumentException("{$labelCampo} inválido.");
        }
        return $id;
    }

    private static function normalizarEstado(string $estado): string
    {
        $e = strtoupper(trim($estado));
        if (!in_array($e, self::ESTADOS_VALIDOS, true)) {
            throw new \InvalidArgumentException("Estado de postulación inválido: {$estado}");
        }
        return $e;
    }
}