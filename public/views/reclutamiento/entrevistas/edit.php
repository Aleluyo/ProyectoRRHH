<?php
declare(strict_types=1);

// Se asume que el controlador ya cargó la configuración, sesión y Auth.
// require_once ... (redundante)

$area = htmlspecialchars($_SESSION['area'] ?? '', ENT_QUOTES, 'UTF-8');
$puesto = htmlspecialchars($_SESSION['puesto'] ?? '', ENT_QUOTES, 'UTF-8');
$ciudad = htmlspecialchars($_SESSION['ciudad'] ?? '', ENT_QUOTES, 'UTF-8');

// $entrevista viene desde EntrevistaController::edit()
if (!isset($entrevista) || !is_array($entrevista)) {
  $entrevista = [];
}

$errors = $errors ?? ($_SESSION['errors'] ?? []);
$old = $old ?? ($_SESSION['old_input'] ?? []);

function v_old_ent(string $k, array $old, array $ent): string
{
  if (array_key_exists($k, $old))
    return (string) $old[$k];
  return isset($ent[$k]) ? (string) $ent[$k] : '';
}

$idEntrevista = (int) ($entrevista['id_entrevista'] ?? 0);
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Editar entrevista · Reclutamiento</title>
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
          },
          boxShadow: { soft: '0 10px 28px rgba(10,42,94,.08)' },
          backgroundImage: {
            gridglow: 'radial-gradient(circle at 1px 1px, rgba(0,0,0,.06) 1px, transparent 1px)',
            ribbon: 'linear-gradient(90deg, #ff78b5,#ffc9a9,#36d1cc)'
          }
        }
      }
    }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Yellowtail&display=swap"
    rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <a href="<?= url('logout.php') ?>"
          class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm hover:bg-vc-pink/10 text-vc-ink">
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-4xl px-4 sm:px-6 py-8 relative">
    <div class="mb-5">
      <nav class="flex items-center gap-3 text-sm">
        <a href="<?= url('index.php') ?>" class="text-muted-ink hover:text-vc-ink transition">Inicio</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('views/reclutamiento/index.php') ?>"
          class="text-muted-ink hover:text-vc-ink transition">Reclutamiento y Selección</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="<?= url('index.php?controller=entrevista&action=index') ?>"
          class="text-muted-ink hover:text-vc-ink transition">Entrevistas</a>
        <svg class="w-4 h-4 text-vc-peach" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-medium text-vc-pink">Editar entrevista #<?= $idEntrevista ?></span>
      </nav>
    </div>

    <section class="mb-6">
      <h1 class="vice-title text-[32px] leading-tight text-vc-ink">Editar entrevista</h1>
      <p class="mt-1 text-sm sm:text-base text-muted-ink">
        Ajusta fecha, tipo o resultado de la entrevista seleccionada.
      </p>
    </section>

    <section class="rounded-xl border border-black/10 bg-white/90 p-5 shadow-soft">
      <?php if (!empty($errors)): ?>
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 border border-red-200">
          <p class="font-bold">Por favor corrige los siguientes errores:</p>
          <ul class="list-disc list-inside mt-1">
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= url('index.php?controller=entrevista&action=update&id=' . $idEntrevista) ?>"
        class="space-y-4">
        <input type="hidden" name="id" value="<?= $idEntrevista ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="id_postulacion" class="block text-sm font-medium text-vc-ink mb-1">Postulación</label>
            <select name="id_postulacion" id="id_postulacion"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              required>
              <option value="">Selecciona una postulación...</option>
              <?php
              $currentId = (int) v_old_ent('id_postulacion', $old, $entrevista);
              foreach ($postulaciones as $p):
                $sel = ($p['id'] === $currentId) ? 'selected' : '';
                ?>
                <option value="<?= $p['id'] ?>" <?= $sel ?>><?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="programada_para" class="block text-sm font-medium text-vc-ink mb-1">Fecha y hora</label>
            <?php
            // Ajuste de formato para datetime-local (Y-m-d\TH:i)
            $valFecha = v_old_ent('programada_para', $old, $entrevista);
            $valFecha = str_replace(' ', 'T', trim($valFecha));
            if (strlen($valFecha) > 16) {
              $valFecha = substr($valFecha, 0, 16);
            }
            ?>
            <input type="datetime-local" name="programada_para" id="programada_para"
              value="<?= htmlspecialchars($valFecha, ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"
              required>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="tipo" class="block text-sm font-medium text-vc-ink mb-1">Tipo</label>
            <input type="text" name="tipo" id="tipo"
              value="<?= htmlspecialchars(v_old_ent('tipo', $old, $entrevista), ENT_QUOTES, 'UTF-8') ?>"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
          </div>

          <div>
            <label for="resultado" class="block text-sm font-medium text-vc-ink mb-1">Resultado</label>
            <?php $resSel = strtoupper(v_old_ent('resultado', $old, $entrevista)); ?>
            <select name="resultado" id="resultado"
              class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60">
              <option value="PENDIENTE" <?= $resSel === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
              <option value="APROBADO" <?= $resSel === 'APROBADO' ? 'selected' : '' ?>>Aprobado</option>
              <option value="RECHAZADO" <?= $resSel === 'RECHAZADO' ? 'selected' : '' ?>>Rechazado</option>
            </select>
          </div>
        </div>

        <div>
          <label for="notas" class="block text-sm font-medium text-vc-ink mb-1">Notas</label>
          <textarea name="notas" id="notas" rows="4"
            class="w-full rounded-lg border border-black/10 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-vc-teal/60"><?= htmlspecialchars(v_old_ent('notas', $old, $entrevista), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row gap-3 sm:justify-between items-center">
          <button type="button" id="btnDelete"
            class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-700 hover:bg-rose-100 transition">
            Eliminar
          </button>

          <div class="flex gap-3 w-full sm:w-auto justify-end">
            <a href="<?= url('index.php?controller=entrevista&action=index') ?>"
              class="inline-flex items-center justify-center rounded-lg border border-black/10 bg-white px-4 py-2 text-sm text-muted-ink hover:bg-slate-50">
              Cancelar
            </a>
            <button type="submit"
              class="inline-flex items-center justify-center rounded-lg bg-vc-teal px-4 py-2 text-sm font-medium text-vc-ink shadow-soft hover:bg-vc-neon/80 transition">
              Guardar cambios
            </button>
          </div>
        </div>

        <script>
          document.getElementById('btnDelete').addEventListener('click', () => {
            Swal.fire({
              title: '¿Eliminar entrevista?',
              text: "Esta acción no se puede deshacer.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#3085d6',
              confirmButtonText: 'Sí, eliminar',
              cancelButtonText: 'Cancelar'
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = "<?= url('index.php?controller=entrevista&action=delete&id=' . $idEntrevista) ?>";
              }
            });
          });
        </script>
      </form>
    </section>
  </main>
</body>

</html>