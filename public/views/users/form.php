<?php
declare(strict_types=1);
/**
 * Gestión de Usuarios — Formulario (AJAX, fondo opaco)
 * Ruta: /public/views/users/form.php
 */
$ROOT = dirname(__DIR__, 3);
require_once $ROOT . '/config/paths.php';
require_once $ROOT . '/app/middleware/Auth.php';
require_once $ROOT . '/app/middleware/Permissions.php';
requireLogin();
if (!isAdmin()) { http_response_code(403); exit('No autorizado'); }

require_once $ROOT . '/app/controllers/UserController.php';

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$roles  = UserController::roleOptions();
$cities = UserController::cityOptions();

if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

$listUrl = url('views/users/list.php');
$getApi  = url('api/users/get.php');
$saveApi = url('api/users/save.php');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= $id ? 'Editar' : 'Nuevo' ?> usuario | AAHN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= url('assets/css/users.css') ?>?v=1.2.0" rel="stylesheet">

  <style>
    body.users-page{background:#0f1f28;color:#eaf5f7;margin:0;min-height:100vh;}
    .form-wrap{max-width:920px;margin:48px auto;padding:0 16px;}
    .form-solid{background:#102531;border:1px solid #1c3b4a;border-radius:16px;box-shadow:0 10px 32px rgba(0,0,0,.45);}
    .form-solid .head{background:#112a36;padding:14px 18px;border-bottom:1px solid #1c3b4a;display:flex;justify-content:center;}
    .form-solid .head h1{margin:0;font-size:1.25rem;font-weight:800;color:#fff;}
    .form-solid .body{padding:20px;}
    .form-label{color:#dfe8ec;font-weight:600;}
    .form-control,.form-select{background:#0c1b24;border:1px solid #1e3a47;color:#e8f3f6;}
    .form-control::placeholder{color:#7b8a90;opacity:1;} /* placeholders más grises */
    .form-select:focus,.form-control:focus{background:#0f222d;border-color:#2f647a;box-shadow:none;color:#fff;}
    .btn-accent{background:#ffb27b;border:1px solid #e79c64;color:#1a110c;font-weight:700;}
    .btn-ghost{background:#0c1b24;border:1px solid #1e3a47;color:#cfe0e6;}
    .btn-ghost:hover{background:#123042;color:#fff;}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    @media(max-width:768px){.grid-2{grid-template-columns:1fr;}}
  </style>
</head>
<body class="users-page">
  <div class="form-wrap">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <a href="<?= h($listUrl) ?>" class="btn btn-sm btn-ghost">&larr; Volver</a>
      <h2 class="h4 m-0"><?= $id ? 'Editar' : 'Nuevo' ?> usuario</h2>
      <div style="width:84px"></div>
    </div>

    <div id="alert" class="alert d-none" role="alert"></div>

    <div class="form-solid">
      <div class="head"><h1><?= $id ? 'Editar' : 'Nuevo' ?> usuario</h1></div>
      <div class="body">
        <form id="f" autocomplete="off" novalidate>
          <input type="hidden" name="id" id="id" value="<?= $id ? (int)$id : '' ?>">

          <div class="grid-2">
            <div>
              <label class="form-label" for="username">Username</label>
              <input name="username" id="username" class="form-control" required maxlength="50" placeholder="ej. jdoe">
            </div>
            <div>
              <label class="form-label" for="first_name">Nombre(s)</label>
              <input name="first_name" id="first_name" class="form-control" required maxlength="100" placeholder="Nombre(s)">
            </div>
            <div>
              <label class="form-label" for="last_name">Apellidos</label>
              <input name="last_name" id="last_name" class="form-control" required maxlength="100" placeholder="Apellidos">
            </div>
            <div>
              <label class="form-label" for="role">Rol</label>
              <select name="role" id="role" class="form-select" required>
                <option value="">Selecciona</option>
                <?php foreach ($roles as $k=>$lbl): ?>
                  <option value="<?= h($k) ?>"><?= h($lbl) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label" for="area">Área</label>
              <input name="area" id="area" class="form-control" required maxlength="100" placeholder="Área">
            </div>
            <div>
              <label class="form-label" for="puesto">Puesto</label>
              <input name="puesto" id="puesto" class="form-control" required maxlength="100" placeholder="Puesto">
            </div>
            <div>
              <label class="form-label" for="ciudad">Ciudad</label>
              <select name="ciudad" id="ciudad" class="form-select" required>
                <option value="">Selecciona</option>
                <?php foreach ($cities as $c): ?>
                  <option value="<?= h($c) ?>"><?= h($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label" for="password">Contraseña <?= $id ? '<small class="text-muted">(dejar en blanco para no cambiar)</small>' : '' ?></label>
              <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" <?= $id ? '' : 'required' ?> minlength="6" placeholder="<?= $id ? '••••••' : 'mínimo 6 caracteres' ?>">
                <button class="btn btn-ghost" type="button" id="btnTogglePwd">👁</button>
              </div>
            </div>
            <div class="d-flex align-items-end">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                <label class="form-check-label" for="is_active">Activo</label>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button id="btnSave" class="btn btn-accent" type="submit"><span id="btnSaveTxt">Guardar</span></button>
            <a class="btn btn-ghost" href="<?= h($listUrl) ?>">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
(() => {
  const $ = s => document.querySelector(s);
  const csrf = <?= json_encode($csrf) ?>;
  const GET_API = <?= json_encode($getApi) ?>;
  const SAVE_API = <?= json_encode($saveApi) ?>;
  const LIST_URL = <?= json_encode($listUrl) ?>;
  const id = $('#id').value ? Number($('#id').value) : null;
  const alertBox = $('#alert');

  const showAlert=(t,m)=>{alertBox.className='alert alert-'+t;alertBox.textContent=m;alertBox.classList.remove('d-none');};
  const clearAlert=()=>{alertBox.className='alert d-none';alertBox.textContent='';};

  $('#btnTogglePwd').addEventListener('click',()=>{const inp=$('#password');inp.type=inp.type==='password'?'text':'password';});

  async function loadIfEditing(){
    if(!id)return;
    try{
      const r=await fetch(`${GET_API}?id=${id}`,{headers:{'Accept':'application/json'}});
      const j=await r.json();if(!r.ok||!j.ok)throw new Error(j.error||`HTTP ${r.status}`);
      const u=j.data||{};
      $('#username').value=u.username??'';$('#first_name').value=u.first_name??'';$('#last_name').value=u.last_name??'';
      $('#role').value=u.role??'';$('#area').value=u.area??'';$('#puesto').value=u.puesto??'';$('#ciudad').value=u.ciudad??'';
      $('#is_active').checked=(u.is_active===1);
    }catch(e){showAlert('danger','No se pudo cargar el usuario.');}
  }

  $('#f').addEventListener('submit',async e=>{
    e.preventDefault();clearAlert();
    const payload={id:$('#id').value||undefined,username:$('#username').value.trim(),first_name:$('#first_name').value.trim(),
      last_name:$('#last_name').value.trim(),role:$('#role').value,area:$('#area').value.trim(),puesto:$('#puesto').value.trim(),
      ciudad:$('#ciudad').value,password:$('#password').value,is_active:$('#is_active').checked?1:0};
    if(!payload.username||!payload.first_name||!payload.last_name||!payload.role||!payload.area||!payload.puesto||!payload.ciudad)
      return showAlert('warning','Completa todos los campos.');
    if(!payload.id&&(!payload.password||payload.password.length<6))
      return showAlert('warning','La contraseña debe tener al menos 6 caracteres.');
    if(payload.id&&!payload.password)delete payload.password;

    const btn=$('#btnSave'),txt=$('#btnSaveTxt');btn.disabled=true;txt.textContent='Guardando…';
    try{
      const r=await fetch(SAVE_API,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF':csrf},body:JSON.stringify(payload)});
      const j=await r.json();if(!r.ok||!j.ok)throw new Error(j.error||`HTTP ${r.status}`);
      location.href=`${LIST_URL}?msg=${encodeURIComponent(j.message||'Guardado')}`;
    }catch(err){showAlert('danger',err.message||'Error guardando');}finally{btn.disabled=false;txt.textContent='Guardar';}
  });

  loadIfEditing();
})();
</script>
</body>
</html>
