<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../middleware/Auth.php';

class ConfiguracionController
{
    /**
     * Dashboard principal de configuración
     * GET: ?controller=configuracion&action=index
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1); // 1 = Admin

        $this->usuarios();
    }

    /**
     * Listado de usuarios (Pestaña Usuarios)
     * GET: ?controller=configuracion&action=usuarios
     */
    public function usuarios(): void
    {
        requireLogin();
        requireRole(1);

        $search = $_GET['q'] ?? null;
        $users = Usuario::all(100, 0, $search);

        $activeTab = 'usuarios';
        require __DIR__ . '/../../public/views/configuracion/index.php';
    }

    /**
     * Guardar nuevo usuario
     * POST: ?controller=configuracion&action=storeUsuario
     */
    public function storeUsuario(): void
    {
        requireLogin();
        requireRole(1);
        session_start();

        try {
            $username = trim($_POST['username'] ?? '');
            $correo   = trim($_POST['correo'] ?? '');
            $rol      = (int)($_POST['role'] ?? 2);
            
            $passwordRaw = Usuario::generatePassword(12);

            $data = [
                'username' => $username,
                'correo'   => $correo,
                'id_rol'   => $rol,
                'password' => $passwordRaw
            ];

            Usuario::create($data);

            $_SESSION['flash_success'] = "Usuario creado correctamente. <br><strong>Contraseña: " . $passwordRaw . "</strong><br>Cópiala, no se volverá a mostrar.";
            
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al crear usuario: " . $e->getMessage();
        }

        header('Location: index.php?controller=configuracion&action=usuarios');
        exit;
    }

    /**
     * Regenerar contraseña
     * POST: ?controller=configuracion&action=resetPassword&id=1
     */
    public function resetPassword(): void
    {
        requireLogin();
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['flash_error'] = "ID de usuario inválido.";
            header('Location: index.php?controller=configuracion&action=usuarios');
            exit;
        }

        try {
            $newPassword = Usuario::generatePassword(12);
            Usuario::changePassword($id, $newPassword);

            $_SESSION['flash_success'] = "Contraseña regenerada correctamente. <br><strong>Nueva Contraseña: " . $newPassword . "</strong><br>Cópiala, no se volverá a mostrar.";

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Error al regenerar contraseña: " . $e->getMessage();
        }

        header('Location: index.php?controller=configuracion&action=usuarios');
        exit;
    }

    /**
     * Listado de roles (Pestaña Roles)
     * GET: ?controller=configuracion&action=roles
     */
    public function roles(): void
    {
        requireLogin();
        requireRole(1);

        require_once __DIR__ . '/../models/Rol.php';
        $roles = Rol::all();
        
        // Enriquecer roles con sus permisos
        foreach ($roles as &$rol) {
            $rol['permisos'] = Rol::getPermissions((int)$rol['id_rol']);
        }
        unset($rol);

        $activeTab = 'roles';
        require __DIR__ . '/../../public/views/configuracion/index.php';
    }

    /**
     * Configuración General (Pestaña General)
     * GET: ?controller=configuracion&action=general
     */
    public function general(): void
    {
        requireLogin();
        requireRole(1);

        // Aquí podrías cargar reglas de asistencia, políticas, etc.
        // Por ahora simulamos datos o cargamos de una tabla si existiera
        
        $activeTab = 'general';
        require __DIR__ . '/../../public/views/configuracion/index.php';
    }

    /**
     * Activar / Desactivar usuario
     * GET: ?controller=configuracion&action=toggleUsuario&id=1&active=0
     */
    public function toggleUsuario(): void
    {
        requireLogin();
        requireRole(1);
        session_start();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $estado = isset($_GET['active']) && $_GET['active'] == '1' ? 'ACTIVO' : 'INACTIVO';

        if ($id > 0) {
            Usuario::setEstado($id, $estado);
            $_SESSION['flash_success'] = "Estado de usuario actualizado a $estado.";
        }

        header('Location: index.php?controller=configuracion&action=usuarios');
        exit;
    }
}
