<?php
declare(strict_types=1);
/**
 * Gestión de Usuarios — Listado (acciones con íconos)
 * Ruta: /public/views/users/list.php
 */
$ROOT = dirname(__DIR__, 3);
require_once $ROOT . '/config/paths.php';
require_once $ROOT . '/app/middleware/Auth.php';
require_once $ROOT . '/app/middleware/Permissions.php';
requireLogin();
if (!isAdmin()) { http_response_code(403); exit('No autorizado'); }

require_once $ROOT . '/app/controllers/UserController.php';
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$roles  = UserController::roleOptions();
$cities = UserController::cityOptions();

if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

$q      = trim((string)($_GET['q'] ?? ''));
$role   = $_GET['role']   ?? '';
$ciudad = $_GET['ciudad'] ?? '';
$status = isset($_GET['status']) ? (string)$_GET['status'] : '';

$listApi   = url('api/users/list.php');
$toggleApi = url('api/users/toggle.php');
$formUrl   = url('views/users/form.php');
$deleteUrl = url('actions/users/delete.php');

$first  = $_SESSION['first_name'] ?? '';
$last   = $_SESSION['last_name']  ?? '';
$name   = h(trim($first . ' ' . $last));
$roleLbl = h(ucfirst($_SESSION['role'] ?? ''));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Usuarios | AAHN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="<?= url('assets/css/users.css') ?>?v=1.2.0" rel="stylesheet">
</head>
<body class="users-page">

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg dashboard-navbar">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2 text-white" href="<?= url('index.php') ?>">
        <img src="<?= asset('img/otl.png') ?>" alt="OTL Logo" style="height:50px;">
        <span class="fw-bold">AAHN</span>
      </a>
      <div class="ms-auto d-flex align-items-center gap-3">
        <span class="small text-white-50"><?= $name ?> (<?= $roleLbl ?>)</span>
        <a href="<?= url('logout.php') ?>" class="btn btn-sm btn-light fw-semibold">Cerrar sesión</a>
      </div>
    </div>
  </nav>

  <div class="container-xxl py-4">

    <div class="page-head d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4 mb-0">Gestión de Usuarios</h1>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="<?= url('index.php') ?>">
          <i class="bi bi-house"></i> Menú Principal
        </a>
        <a class="btn btn-accent" href="<?= h($formUrl) ?>">
          <i class="bi bi-plus-lg"></i> Nuevo usuario
        </a>
      </div>
    </div>

    <form id="filters" class="form-glass mb-3" autocomplete="off">
      <div class="row g-2">
        <div class="col-md-4">
          <label for="q" class="form-label">Buscar</label>
          <input class="form-control" name="q" id="q" value="<?= h($q) ?>" placeholder="Nombre o username">
        </div>
        <div class="col-md-2">
          <label for="role" class="form-label">Rol</label>
          <select class="form-select" name="role" id="role">
            <option value="">Todos</option>
            <?php foreach ($roles as $k=>$lbl): ?>
              <option value="<?= h($k) ?>" <?= $role===$k?'selected':'' ?>><?= h($lbl) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label for="ciudad" class="form-label">Ciudad</label>
          <select class="form-select" name="ciudad" id="ciudad">
            <option value="">Todas</option>
            <?php foreach ($cities as $c): ?>
              <option value="<?= h($c) ?>" <?= $ciudad===$c?'selected':'' ?>><?= h($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label for="status" class="form-label">Estado</label>
          <select class="form-select" name="status" id="status">
            <option value="">Todos</option>
            <option value="1" <?= $status==='1'?'selected':'' ?>>Activos</option>
            <option value="0" <?= $status==='0'?'selected':'' ?>>Inactivos</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label d-none d-md-block">&nbsp;</label>
          <button class="btn btn-ghost w-100 btn-apply" type="submit">Aplicar</button>
        </div>
      </div>
    </form>

    <div class="table-glass-wrap">
      <div class="table-responsive">
        <table class="table table-glass table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Username</th>
              <th>Nombre</th>
              <th>Rol</th>
              <th>Área / Puesto</th>
              <th>Ciudad</th>
              <th>Estado</th>
              <th>Último acceso</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody id="rows">
            <tr><td colspan="9" class="py-4 text-center">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

<script>
(() => {
  const $ = sel => document.querySelector(sel);
  const rows = $('#rows');

  const csrf       = <?= json_encode($csrf) ?>;
  const LIST_API   = <?= json_encode($listApi) ?>;
  const TOGGLE_API = <?= json_encode($toggleApi) ?>;
  const FORM_URL   = <?= json_encode($formUrl) ?>;
  const DELETE_URL = <?= json_encode($deleteUrl) ?>;

  let reqSeq = 0, currentAbort = null, lastQuery = '', ignoreEventsUntil = 0;

  function params(){
    const usp = new URLSearchParams();
    const q = $('#q').value.trim();
    const role = $('#role').value;
    const ciudad = $('#ciudad').value;
    const status = $('#status').value;
    if (q) usp.set('q', q);
    if (role) usp.set('role', role);
    if (ciudad) usp.set('ciudad', ciudad);
    if (status !== '') usp.set('status', status);
    return usp;
  }

  async function load(force=false){
    const usp = params();
    const qs = usp.toString();
    if (!force && qs === lastQuery) return;
    lastQuery = qs;

    const mySeq = ++reqSeq;
    if (currentAbort) currentAbort.abort();
    currentAbort = new AbortController();

    rows.innerHTML = `<tr><td colspan="9" class="py-4 text-center">Cargando...</td></tr>`;

    try{
      const url = LIST_API + (qs ? ('?' + qs) : '');
      const r = await fetch(url, {
        credentials: 'same-origin',
        cache: 'no-store',
        signal: currentAbort.signal,
        headers: { 'Accept': 'application/json' }
      });
      if (mySeq !== reqSeq) return;

      const j = await r.json();
      if (!r.ok || !j.ok) throw new Error(j.error || `HTTP ${r.status}`);

      if (j.count === 0){
        rows.innerHTML = `<tr><td colspan="9" class="py-4 text-center">Sin resultados</td></tr>`;
        return;
      }

      rows.innerHTML = j.data.map(u => `
        <tr data-id="${u.id}">
          <td data-label="#">${u.id}</td>
          <td data-label="Username">${escapeHtml(u.username)}</td>
          <td data-label="Nombre">${escapeHtml(u.name)}</td>
          <td data-label="Rol"><span class="badge">${escapeHtml(u.role_label)}</span></td>
          <td data-label="Área / Puesto">${escapeHtml(u.area)} / ${escapeHtml(u.puesto)}</td>
          <td data-label="Ciudad">${escapeHtml(u.ciudad)}</td>
          <td data-label="Estado">
            <button class="btn btn-sm ${u.is_active ? 'btn-success' : 'btn-secondary'} toggle"
                    data-active="${u.is_active}">
              ${u.is_active ? 'Activo' : 'Inactivo'}
            </button>
          </td>
          <td data-label="Último acceso">${u.last_login_at ? escapeHtml(u.last_login_at) : '—'}</td>
          <td data-label="Acciones" class="text-end table-actions">
            <a class="btn-icon is-edit" href="${FORM_URL}?id=${u.id}" aria-label="Editar" title="Editar">
              <i class="bi bi-pencil"></i>
            </a>
            <form class="d-inline" method="post" action="${DELETE_URL}" onsubmit="return confirm('¿Eliminar este usuario?');">
              <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
              <input type="hidden" name="id" value="${u.id}">
              <button class="btn-icon is-delete" type="submit" aria-label="Eliminar" title="Eliminar">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </td>
        </tr>
      `).join('');
    }catch(e){
      if (e.name === 'AbortError') return;
      if (mySeq !== reqSeq) return;
      rows.innerHTML = `<tr><td colspan="9" class="py-4 text-center text-danger">Error cargando datos</td></tr>`;
      console.error('Users list load error:', e);
    }
  }

  const escapeHtml = s => (s ?? '').toString().replace(/[&<>"']/g, m => (
    {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]
  ));

  function debounce(fn, ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms);} }
  const debouncedLoad = debounce(() => load(false), 350);

  document.querySelectorAll('#filters input, #filters select').forEach(el => {
    el.addEventListener('input', ()=>debouncedLoad());
    el.addEventListener('change', ()=>debouncedLoad());
  });
  document.querySelector('#filters').addEventListener('submit', (e)=>{ e.preventDefault(); load(true); });

  document.querySelector('#rows').addEventListener('click', async (e) => {
    const btn = e.target.closest('.toggle');
    if (!btn) return;
    const tr = btn.closest('tr');
    const id = Number(tr.dataset.id);
    const active = btn.dataset.active === '1' ? 0 : 1;
    btn.disabled = true;
    try{
      const form = new FormData();
      form.append('id', String(id));
      form.append('active', String(active));
      const r = await fetch(TOGGLE_API, {
        method: 'POST',
        body: form,
        headers: {'X-CSRF': csrf, 'Accept':'application/json'},
        credentials: 'same-origin',
        cache: 'no-store'
      });
      const j = await r.json();
      if (!r.ok || !j.ok) throw new Error(j.error || `HTTP ${r.status}`);
      btn.dataset.active = String(j.is_active);
      btn.classList.toggle('btn-success', j.is_active === 1);
      btn.classList.toggle('btn-secondary', j.is_active !== 1);
      btn.textContent = j.is_active === 1 ? 'Activo' : 'Inactivo';
    }catch(err){
      if (err.name !== 'AbortError'){
        alert('No se pudo actualizar el estado.');
        console.error('Users toggle error:', err);
      }
    }finally{ btn.disabled = false; }
  });

  setTimeout(() => load(true), 200);
})();
</script>
</body>
</html>
