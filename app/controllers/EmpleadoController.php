<?php

declare(strict_types=1);

require_once __DIR__ . '/../middleware/Auth.php';

class EmpleadoController
{
    /**
     * Pantalla principal: lista de empleados
     * GET: ?controller=empleado&action=index
     */
    public function index(): void
    {
        requireLogin();
        requireRole(1);

        // Más adelante: aquí llamaremos al modelo Empleado::all()
        $empleados = [];

        require __DIR__ . '/../../public/views/empleados/list.php';
    }

    /**
     * Mostrar formulario de alta
     * GET: ?controller=empleado&action=create
     */
    public function create(): void
    {
        requireLogin();
        requireRole(1);

        $errors = [];
        $old = [];

        require __DIR__ . '/../../public/views/empleados/create.php';
    }

    // Dejamos preparadas las acciones, las llenamos en la siguiente fase
    public function store(): void
    {
    }
    public function edit(): void
    {
    }
    public function update(): void
    {
    }
    public function show(): void
    {
    }
}
