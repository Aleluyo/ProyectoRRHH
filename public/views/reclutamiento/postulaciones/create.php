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

/** @var array $errors */
/** @var array $old */
/** @var array $vacantes */
/** @var array $candidatos */

$errors    = $errors    ?? [];
$old       = $old       ?? [];
$vacantes  = $vacantes  ?? [];
$candidatos = $candidatos ?? [];

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function fieldError(array $errors, string $key): ?string {
    return $errors[$key] ?? null;
}

function hasError(array $errors, string $key): bool {
    return isset($errors[$key]);
}

$today = date('Y-m-d');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Nueva postulación · Reclutamiento y Selección</title>
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
            <a href="<?= url('index.php?controller=postulacion&action=index') ?>" class="text-muted-ink hover:text-vc-ink transition">
                Postulaciones
            </a>
            <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-medium text-vc-pink">Nueva postulación</span>
        </nav>
    </div>

    <section class="mb-6">
        <h1 class="vice-title text-[32px] leading-tight text-vc-ink">Nueva postulación</h1>
        <p class="mt-1 text-sm sm:text-base text-muted-ink">
            Relaciona un candidato con una vacante y define la etapa actual del proceso.
        </p>
    </section>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <strong class="font-semibold">Revisa los campos:</strong>
            <ul class="mt-1 list-disc list-inside">
                <?php foreach ($errors as $msg): ?>
                    <li><?= h($msg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="rounded-2xl border border-black/10 bg-white/90 p-6 shadow-soft">
        <form method="post" action="<?= url('index.php?controller=postulacion&action=store') ?>" class="space-y-6">

            <!-- Fila Vacante / Candidato -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Vacante -->
                <div>
                    <label class="block text-sm font-medium mb-1" for="id_vacante">
                        Vacante <span class="text-rose-600">*</span>
                    </label>
                    <select
                        id="id_vacante"
                        name="id_vacante"
                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60 <?= hasError($errors,'id_vacante') ? 'border-rose-400' : 'border-black/10' ?>"
                        required
                    >
                        <option value="">Selecciona una vacante…</option>
                        <?php foreach ($vacantes as $v): ?>
                            <?php
                            $idVac = (int)($v['id_vacante'] ?? 0);
                            $empresa = $v['empresa_nombre'] ?? '';
                            $areaNom = $v['area_nombre']     ?? '';
                            $puestoNom = $v['puesto_nombre'] ?? '';
                            $label = trim($empresa . ' · ' . $areaNom . ' · ' . $puestoNom);
                            $selected = ((int)($old['id_vacante'] ?? 0) === $idVac) ? 'selected' : '';
                            ?>
                            <option value="<?= $idVac ?>" <?= $selected ?>>
                                <?= h($label ?: ('Vacante #' . $idVac)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($msg = fieldError($errors, 'id_vacante')): ?>
                        <p class="mt-1 text-xs text-rose-600"><?= h($msg) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Candidato -->
                <div>
                    <label class="block text-sm font-medium mb-1" for="id_candidato">
                        Candidato <span class="text-rose-600">*</span>
                    </label>
                    <select
                        id="id_candidato"
                        name="id_candidato"
                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60 <?= hasError($errors,'id_candidato') ? 'border-rose-400' : 'border-black/10' ?>"
                        required
                    >
                        <option value="">Selecciona un candidato…</option>
                        <?php foreach ($candidatos as $c): ?>
                            <?php
                            $idCand = (int)($c['id_candidato'] ?? 0);
                            $label  = $c['nombre'] ?? ('Candidato #' . $idCand);
                            $selected = ((int)($old['id_candidato'] ?? 0) === $idCand) ? 'selected' : '';
                            ?>
                            <option value="<?= $idCand ?>" <?= $selected ?>>
                                <?= h($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($msg = fieldError($errors, 'id_candidato')): ?>
                        <p class="mt-1 text-xs text-rose-600"><?= h($msg) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Fila Etapa / Fecha -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Etapa -->
                <div>
                    <label class="block text-sm font-medium mb-1" for="estado">
                        Etapa <span class="text-rose-600">*</span>
                    </label>
                    <?php
                    $estadoOld = strtoupper(trim($old['estado'] ?? 'SCREENING'));
                    $opcionesEtapa = [
                        'POSTULADO'  => 'Postulado',
                        'SCREENING'  => 'Screening',
                        'ENTREVISTA' => 'Entrevista',
                        'PRUEBA'     => 'Prueba',
                        'OFERTA'     => 'Oferta',
                        'CONTRATADO' => 'Contratado',
                        'RECHAZADO'  => 'Rechazado',
                    ];
                    ?>
                    <select
                        id="estado"
                        name="estado"
                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60 <?= hasError($errors,'estado') ? 'border-rose-400' : 'border-black/10' ?>"
                        required
                    >
                        <option value="">Selecciona etapa…</option>
                        <?php foreach ($opcionesEtapa as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $estadoOld === $val ? 'selected' : '' ?>>
                                <?= h($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($msg = fieldError($errors, 'estado')): ?>
                        <p class="mt-1 text-xs text-rose-600"><?= h($msg) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Fecha de postulación -->
                <div>
                    <label class="block text-sm font-medium mb-1" for="fecha_aplicacion">
                        Fecha de postulación <span class="text-rose-600">*</span>
                    </label>
                    <input
                        type="date"
                        id="fecha_aplicacion"
                        name="fecha_aplicacion"
                        value="<?= h($old['fecha_aplicacion'] ?? $today) ?>"
                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60 <?= hasError($errors,'fecha_aplicacion') ? 'border-rose-400' : 'border-black/10' ?>"
                        required
                    />
                    <?php if ($msg = fieldError($errors, 'fecha_aplicacion')): ?>
                        <p class="mt-1 text-xs text-rose-600"><?= h($msg) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comentarios -->
            <div>
                <label class="block text-sm font-medium mb-1" for="comentarios">
                    Comentarios <span class="text-xs text-muted-ink">(opcional)</span>
                </label>
                <textarea
                    id="comentarios"
                    name="comentarios"
                    rows="4"
                    class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
                ><?= h($old['comentarios'] ?? '') ?></textarea>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-2">
                <a
                    href="<?= url('index.php?controller=postulacion&action=index') ?>"
                    class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm hover:bg-slate-50"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-5 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition"
                >
                    Guardar postulación
                </button>
            </div>
        </form>
    </section>
</main>
</body>
</html>