<?php
require_once __DIR__ . '/../../../config/paths.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Generar Nómina | RRHH</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
          extend: {
          colors: {
            vc: {
              pink: '#ff78b5', peach: '#ffc9a9', teal: '#36d1cc',
              sand: '#ffe9c7', ink: '#0a2a5e', neon: '#a7fffd'
            }
          },
          fontFamily: {
            display: ['Josefin Sans', 'system-ui', 'sans-serif'],
            sans: ['DM Sans', 'system-ui', 'sans-serif'],
            vice: ['Rage Italic', 'Yellowtail', 'cursive']
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
</head>
<body class="min-h-screen bg-gray-50 text-vc-ink font-sans relative">

  <header class="sticky top-0 z-30 border-b border-black/10 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 h-16 flex items-center">
      <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
        <div class="font-display text-lg tracking-widest uppercase text-vc-ink">RRHH</div>
      </a>
    </div>
  </header>

  <main class="mx-auto max-w-2xl px-4 sm:px-6 py-12">
    
    <div class="mb-6">
        <a href="<?= url('index.php?controller=nomina&action=index') ?>" class="text-sm text-gray-500 hover:text-vc-ink flex items-center gap-1 transition">
            &larr; Volver a Nóminas
        </a>
    </div>

    <div class="bg-white border border-black/10 rounded-xl p-8 shadow-sm">
        <h1 class="font-display text-3xl text-vc-ink mb-2">Generar Nómina</h1>
        <p class="text-gray-500 mb-8">Seleccione las fechas para procesar el pago de los empleados activos.</p>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-100 text-red-600 text-sm font-medium">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('index.php?controller=nomina&action=store') ?>" method="POST" class="space-y-6">
            
            <div>
                <label class="block text-sm font-bold text-vc-ink mb-2">Tipo de Periodo</label>
                <select name="tipo" class="w-full h-11 rounded-lg border border-gray-300 px-3 focus:outline-none focus:border-vc-pink focus:ring-1 focus:ring-vc-pink transition">
                    <option value="QUINCENAL">Quincenal</option>
                    <option value="MENSUAL">Mensual</option>
                    <option value="SEMANAL">Semanal</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-vc-ink mb-2">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" required class="w-full h-11 rounded-lg border border-gray-300 px-3 focus:outline-none focus:border-vc-pink focus:ring-1 focus:ring-vc-pink transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-vc-ink mb-2">Fecha Fin</label>
                    <input type="date" name="fecha_fin" required class="w-full h-11 rounded-lg border border-gray-300 px-3 focus:outline-none focus:border-vc-pink focus:ring-1 focus:ring-vc-pink transition">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 mt-8">
                <button type="submit" class="w-full bg-vc-pink text-white font-bold py-3 rounded-lg hover:bg-pink-500 transition shadow-lg shadow-vc-pink/30">
                    Generar Nómina
                </button>
            </div>

        </form>
    </div>

  </main>
</body>
</html>
