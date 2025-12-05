<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../models/Vacante.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../controllers/PostulacionController.php'; // Reuse some logic if needed

class TableroController {
    
    /**
     * Kanban Board View
     * GET ?controller=tablero&action=index&id_vacante=X
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        $idVacante = isset($_GET['id_vacante']) ? (int)$_GET['id_vacante'] : 0;
        
        $vacantes = Vacante::all(); // For the dropdown selector
        
        // Define columns matching Postulacion::ESTADOS_VALIDOS
        $columnas = [
            'POSTULADO'  => [],
            'SCREENING'  => [],
            'ENTREVISTA' => [],
            'PRUEBA'     => [],
            'OFERTA'     => [],
            'CONTRATADO' => [],
            'RECHAZADO'  => [],
        ];

        // If a vacancy is selected, fetch its postulations
        if ($idVacante > 0) {
            // We can fetch all postulations for this vacancy and group them
            // PostulacionController::byVacante fetches by specific status, 
            // but we want ALL statuses to populate the board.
            // Let's use a raw fetch or helper in the model.
            // Since PostulacionController::byVacante filters by status optionally, 
            // we can call it with null status to get all.
            
            $allPostulaciones = PostulacionController::byVacante($idVacante, 1000, 0, null);
            
            foreach ($allPostulaciones as $p) {
                $estado = strtoupper($p['estado']);
                if (isset($columnas[$estado])) {
                    $columnas[$estado][] = $p;
                } else {
                    // Fallback for unknown states or just ignore
                    $columnas['POSTULADO'][] = $p; 
                }
            }
        }

        require __DIR__ . '/../views/reclutamiento/tablero.php';
    }

    /**
     * AJAX endpoint to move candidate
     * POST ?controller=tablero&action=move
     */
    public function move(): void
    {
        requireLogin();
        requireRole(1); // Security check

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $idPostulacion = (int)($input['id_postulacion'] ?? 0);
        $nuevoEstado   = strtoupper($input['nuevo_estado'] ?? '');

        if ($idPostulacion <= 0 || empty($nuevoEstado)) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit;
        }

        try {
            // Update state using PostulacionController helper or Model directly
            PostulacionController::cambiarEstado($idPostulacion, $nuevoEstado);
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
