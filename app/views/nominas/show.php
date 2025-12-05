<?php
require_once __DIR__ . '/../../../config/paths.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Detalle Nómina | RRHH</title>
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-white text-vc-ink font-sans relative">

  <header class="sticky top-0 z-30 border-b border-black/10 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 h-16 flex items-center">
      <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
        <div class="font-display text-lg tracking-widest uppercase text-vc-ink">RRHH</div>
      </a>
      <div class="ml-auto flex items-center gap-4">
        <a href="<?= url('index.php?controller=nomina&action=index') ?>" class="text-sm font-medium text-vc-ink/70 hover:text-vc-ink transition">Volver</a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8">
    
    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="vice-title text-3xl text-vc-ink">Detalle de Nómina</h1>
            <p class="text-muted-ink mt-1">Periodo #<?= $periodo['id_periodo'] ?> | <span class="uppercase font-bold text-vc-teal"><?= $periodo['tipo'] ?></span></p>
            <p class="text-sm text-gray-500 capitalize"><?= fecha_es($periodo['fecha_inicio']) ?> - <?= fecha_es($periodo['fecha_fin']) ?></p>
        </div>
        <div class="text-right">
             <span class="block text-xs font-bold uppercase text-gray-400 mb-1">Estado</span>
             <?php if ($periodo['estado'] === 'ABIERTO'): ?>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm">ABIERTO</span>
            <?php else: ?>
                <span class="px-3 py-1 rounded-full bg-gray-200 text-gray-600 font-bold text-sm">CERRADO</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($periodo['estado'] === 'ABIERTO'): ?>
    <div class="flex gap-4 mb-6">
         <a href="<?= url('index.php?controller=nomina&action=createEntry&id_periodo=' . $periodo['id_periodo']) ?>" class="inline-flex items-center gap-2 bg-vc-pink text-white px-4 py-2 rounded-lg font-bold hover:bg-pink-500 transition shadow-lg shadow-vc-pink/30">
            <i class="fas fa-user-plus"></i>
            Agregar Empleado
         </a>
         <a href="<?= url('index.php?controller=nomina&action=close&id=' . $periodo['id_periodo']) ?>" class="inline-flex items-center gap-2 bg-amber-500 text-white px-4 py-2 rounded-lg font-bold hover:bg-amber-600 transition shadow-lg shadow-amber-500/30" onclick="return confirm('¿Seguro que deseas Cerrar este periodo? Pasará a histórico y no se podrá editar.');">
            <i class="fas fa-check-circle"></i>
            Cerrar Periodo
         </a>
    </div>
    <?php endif; ?>

    <!-- Feedback Flash -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-100 text-green-600 text-sm font-medium">
            <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-black/10 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black/5 bg-gray-50/50 text-xs uppercase tracking-wider text-vc-ink/50">
                        <th class="px-6 py-4">Empleado</th>
                        <th class="px-6 py-4">Puesto</th>
                        <th class="px-6 py-4 text-right">Percepciones</th>
                        <th class="px-6 py-4 text-right">Deducciones</th>
                        <th class="px-6 py-4 text-right">Neto a Pagar</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 text-sm">
                    <?php if (empty($nominas)): ?>
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No hay registros de nómina.</td></tr>
                    <?php else: ?>
                        <?php foreach ($nominas as $row): ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-vc-ink"><?= htmlspecialchars($row['empleado_nombre']) ?></div>
                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($row['rfc'] ?? 'S/RFC') ?></div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <?= htmlspecialchars($row['nombre_puesto'] ?? 'N/A') ?>
                                    <br>
                                    <span class="text-xs text-gray-400"><?= htmlspecialchars($row['nombre_area'] ?? '') ?></span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-emerald-600">
                                    + $<?= number_format((float)$row['total_percepciones'], 2) ?>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-red-500">
                                    - $<?= number_format((float)$row['total_deducciones'], 2) ?>
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-vc-ink text-base">
                                    $<?= number_format((float)$row['total_neto'], 2) ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= url('index.php?controller=nomina&action=edit&id=' . $row['id_nomina']) ?>" class="p-2 text-vc-teal hover:bg-vc-teal/10 rounded transition" title="Editar Nómina">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= url('index.php?controller=nomina&action=recibo&id=' . $row['id_nomina']) ?>" target="_blank" class="p-2 text-gray-400 hover:text-vc-ink hover:bg-gray-100 rounded transition" title="Ver Recibo">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

  </main>
</body>
</html>
