<?php
// views/configuracion/index.php
// Variables esperadas: $activeTab (string)

$tabs = [
    'usuarios' => ['label' => 'Usuarios', 'icon' => 'i-users'],
    'roles'    => ['label' => 'Roles y Permisos', 'icon' => 'i-badge'],
    'general'  => ['label' => 'General', 'icon' => 'i-gear'],
];

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - RRHH</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Tailwind & Fonts (Mismo head que index.php) -->
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
          },
          boxShadow: { soft: '0 10px 28px rgba(10,42,94,.08)' },
          backgroundImage: {
            gridglow: 'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
            ribbon: 'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
          }
        }
      }
    }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&family=DM+Sans:wght@300;400;500;700&family=Yellowtail&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
  <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
</head>
<body class="min-h-screen bg-gray-50 text-vc-ink font-sans">

    <!-- Navbar Simple -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="<?= url('index.php') ?>" class="text-vc-ink hover:text-vc-pink transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1 class="font-display text-xl font-bold tracking-tight uppercase">Configuración</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 hidden sm:inline"><?= $_SESSION['username'] ?? 'Usuario' ?></span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div><?= $_SESSION['flash_success'] ?></div>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div><?= $_SESSION['flash_error'] ?></div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 mb-8">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                <?php foreach ($tabs as $key => $tab): ?>
                    <?php 
                        $isActive = ($activeTab === $key);
                        $classes = $isActive 
                            ? 'border-vc-pink text-vc-pink' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                    ?>
                    <a href="<?= url('index.php?controller=configuracion&action=' . $key) ?>" 
                       class="<?= $classes ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                        <!-- Icono placeholder -->
                        <span><?= $tab['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Content Area -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[400px]">
            <?php 
            if ($activeTab === 'usuarios') {
                require __DIR__ . '/usuarios/list.php';
            } elseif ($activeTab === 'roles') {
                require __DIR__ . '/roles/list.php';
            } else {
                require __DIR__ . '/general/index.php';
            }
            ?>
        </div>

    </main>

</body>
</html>
