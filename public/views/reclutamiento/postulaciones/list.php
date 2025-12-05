<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../../config/paths.php';
require_once __DIR__ . '/../../../../app/middleware/Auth.php';

requireLogin();
requireRole(1);

$area   = htmlspecialchars($_SESSION['area']   ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

if (!isset($postulaciones) || !is_array($postulaciones)) {
    $postulaciones = [];
}

$search = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8');

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Postulaciones · Reclutamiento y Selección</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
          darkMode: 'class',
          theme: {
            extend: {
              colors: {
                vc: {
                  pink:'#ff78b5', peach:'#ffc9a9', teal:'#36d1cc',
                  sand:'#ffe9c7', ink:'#0a2a5e', neon:'#a7fffd'
                }
              },
              fontFamily: {
                display:['Josefin Sans','system-ui','sans-serif'],
                sans:['DM Sans','system-ui','sans-serif'],
                vice:['Rage Italic','Yellowtail','cursive']
              },
              boxShadow: { soft:'0 10px 28px rgba(10,42,94,.08)' },
              backgroundImage: {
                gridglow:'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
                ribbon:'linear-gradient(90deg, #ff78b5, #ffc9a9, #36d1cc)'
              }
            }
          }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/vice.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= asset('img/galgovc.ico') ?>">
</head>
<body class="min-h-screen bg-white text-vc-ink font-sans relative">

<div class="h-[1px] w-full bg-[image:linear-gradient(90deg,#ff78b5,#ffc9a9,#36d1cc)] opacity-70"></div>
<div class="absolute inset-0 grid-bg opacity-15 pointer-events-none"></div>

<header class="sticky top-0 z-30 border-b border-black/10 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 h-16 flex items-center">
        <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
            <img src="<?= asset('img/galgovc.png') ?>" alt="RRHH" class="h-9 w-auto">
            <div class="font-display text-lg tracking-widest uppercase text-vc-ink">RRHH</div>
        </a>
        <div class="ml-auto flex items-center gap-3 text-sm text-muted-ink">
        <span class="hidden sm:inline-block truncate max-w-[220px]">
          <?= $puesto ?><?= $area ? ' &mdash; ' . $area : '' ?><?= $ciudad ? ' &mdash; ' . $ciudad : '' ?>
        </span>
            <a href="<?= url('logout.php') ?>" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
                Cerrar sesión
            </a>
        </div>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 sm:px-6 py-8 relative">

    <!-- Breadcrumb -->
    <div class="mb-5">
        <nav class="flex items-center gap-3 text-sm">
            <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
            <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="<?= url('views/reclutamiento/index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">
                Reclutamiento y Selección
            </a>
            <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-medium text-vc-pink">Postulaciones</span>
        </nav>
    </div>

    <!-- Título + acciones -->
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="vice-title text-[36px] leading-tight text-vc-ink">Postulaciones</h1>
            <p class="mt-1 text-sm sm:text-base text-muted-ink">
                Seguimiento de candidatos por vacante y etapa del proceso.
            </p>
        </div>

        <form method="get" class="flex flex-col sm:flex-row gap-3 sm:items-center">
            <input type="hidden" name="controller" value="postulacion">
            <input type="hidden" name="action" value="index">

            <div class="relative">
                <input
                    type="text"
                    name="q"
                    value="<?= $search ?>"
                    class="w-full sm:w-80 rounded-lg border border-black/10 bg-white/80 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                    placeholder="Buscar por vacante, candidato, etapa…"
                />
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-ink">Buscar</span>
            </div>

            <a
                href="<?= url('index.php?controller=postulacion&action=create') ?>"
                class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
            >
                <span class="mr-2 text-lg leading-none">+</span>
                Nueva postulación
            </a>
        </form>
    </section>

    <!-- Tabla -->
    <section class="mt-6">
        <div class="overflow-x-auto rounded-xl border border-black/10 bg-white/90 shadow-soft">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100/80 text-xs uppercase tracking-wide text-muted-ink">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Vacante</th>
                    <th class="px-3 py-2 text-left">Candidato</th>
                    <th class="px-3 py-2 text-left">Etapa</th>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-center">Acciones</th>
                </tr>
                </thead>
                <tbody class="bg-white">
                <?php if (empty($postulaciones)): ?>
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-sm text-muted-ink">
                            No se encontraron postulaciones.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($postulaciones as $p): ?>
                        <?php
                        $id        = (int)($p['id_postulacion'] ?? 0);
                        $empresa   = $p['empresa_nombre']   ?? '';
                        $areaNom   = $p['nombre_area']      ?? '';
                        $puestoNom = $p['nombre_puesto']    ?? '';
                        $candidato = $p['candidato_nombre'] ?? '';
                        $estado    = strtoupper((string)($p['estado'] ?? ''));
                        $fechaRaw  = $p['aplicada_en'] ?? null;
                        $fecha     = $fechaRaw ? substr($fechaRaw, 0, 10) : '';
                        ?>
                        <tr class="border-t border-slate-200 hover:bg-slate-50">
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-muted-ink"><?= $id ?></td>

                            <td class="px-3 py-2 text-xs">
                                <div class="text-[13px] font-medium text-vc-ink">
                                    <?= h($empresa ?: '—') ?>
                                </div>
                                <div class="text-[11px] text-muted-ink">
                                    <?= h(trim($areaNom . ' · ' . $puestoNom) ?: '—') ?>
                                </div>
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                <?= h($candidato ?: '—') ?>
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                <?php
                                $badgeClass = 'bg-slate-100 text-slate-700';
                                if ($estado === 'ENTREVISTA')      $badgeClass = 'bg-sky-100 text-sky-800';
                                elseif ($estado === 'SCREENING')   $badgeClass = 'bg-amber-100 text-amber-800';
                                elseif ($estado === 'POSTULADO')   $badgeClass = 'bg-slate-100 text-slate-700';
                                elseif ($estado === 'OFERTA')      $badgeClass = 'bg-emerald-100 text-emerald-800';
                                elseif ($estado === 'CONTRATADO')  $badgeClass = 'bg-emerald-200 text-emerald-900';
                                elseif ($estado === 'RECHAZADO')   $badgeClass = 'bg-rose-100 text-rose-800';
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium <?= $badgeClass ?>">
                                  <?= h($estado) ?>
                                </span>
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                <?= h($fecha ?: '—') ?>
                            </td>

                            <td class="px-3 py-2 whitespace-nowrap">
                                <div class="flex gap-2 justify-center">
                                    <a
                                        href="<?= url('index.php?controller=postulacion&action=edit&id=' . $id) ?>"
                                        class="rounded-md border border-black/10 bg-white px-2 py-1 text-xs hover:bg-vc-sand/60"
                                    >
                                        Editar
                                    </a>
                                    <a
                                        href="<?= url('index.php?controller=postulacion&action=delete&id=' . $id) ?>"
                                        class="rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-xs text-rose-700 hover:bg-rose-100"
                                        onclick="return confirm('¿Eliminar la postulación #<?= $id ?>?');"
                                    >
                                        Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-sm text-muted-ink">
            Total: <?= count($postulaciones) ?> · Página 1 / 1
        </p>
    </section>
</main>
</body>
</html>