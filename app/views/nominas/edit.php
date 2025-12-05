<?php
require_once __DIR__ . '/../../../config/paths.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar Nómina | RRHH</title>
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
  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-white text-vc-ink font-sans relative">

  <header class="sticky top-0 z-30 border-b border-black/10 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 h-16 flex items-center">
      <a href="<?= url('index.php') ?>" class="flex items-center gap-3">
        <div class="font-display text-lg tracking-widest uppercase text-vc-ink">RRHH</div>
      </a>
      <div class="ml-auto flex items-center gap-4">
        <a href="<?= url('index.php?controller=nomina&action=show&id=' . $nomina['id_periodo']) ?>" class="text-sm font-medium text-vc-ink/70 hover:text-vc-ink transition">Volver</a>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 sm:px-6 py-8">

    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="font-display text-3xl text-vc-ink">Editar Nómina</h1>
            <p class="text-muted-ink mt-1">Periodo: <?= htmlspecialchars($nomina['fecha_inicio'] . ' - ' . $nomina['fecha_fin']); ?></p>
        </div>
    </div>

    <!-- Información del Empleado -->
    <div class="bg-white border border-black/10 rounded-xl overflow-hidden shadow-sm mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-bold uppercase text-gray-400">Empleado</label>
                    <div class="font-bold text-lg text-vc-ink"><?= htmlspecialchars($nomina['empleado_nombre']); ?></div>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-gray-400">RFC</label>
                    <div class="text-gray-700"><?= htmlspecialchars($nomina['rfc']); ?></div>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-gray-400">Puesto</label>
                    <div class="text-gray-700"><?= htmlspecialchars($nomina['nombre_puesto']); ?></div>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase text-gray-400">Salario Base Mensual</label>
                    <div class="font-mono text-xl font-bold text-emerald-600" id="salarioBase" data-valor="<?= $salarioBase; ?>">
                        $<?= number_format($salarioBase, 2); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <form action="index.php?controller=nomina&action=update" method="POST" id="formNomina">
        <input type="hidden" name="id_nomina" value="<?= $nomina['id_nomina']; ?>">
        <input type="hidden" name="id_periodo" value="<?= $nomina['id_periodo']; ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <!-- Percepciones -->
            <div class="bg-white border border-emerald-100 rounded-xl shadow-sm overflow-hidden h-full flex flex-col">
                <div class="bg-emerald-50/50 border-b border-emerald-100 px-4 py-3 flex justify-between items-center">
                    <h3 class="font-bold text-emerald-800">Percepciones</h3>
                    <button type="button" class="text-emerald-700 hover:text-emerald-900 text-sm font-bold bg-emerald-100 hover:bg-emerald-200 px-3 py-1 rounded transition" onclick="addConcepto('PERCEPCION')">
                        <i class="fas fa-plus mr-1"></i> Agregar
                    </button>
                </div>
                <div class="p-4 flex-grow bg-slate-50 relative">
                    <div id="container-percepciones" class="space-y-2">
                        <?php 
                        $idx = 0;
                        foreach ($detalles as $d): 
                            if ($d['tipo'] !== 'PERCEPCION') continue;
                        ?>
                            <div class="flex items-center gap-2 p-2 bg-white border border-emerald-200 rounded shadow-sm row-concepto" id="row-<?= $idx; ?>">
                                <select name="detalles[<?= $idx; ?>][id_concepto]" class="flex-grow rounded border border-gray-300 px-2 py-1 text-sm focus:border-emerald-500 focus:outline-none select-concepto" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($percepcionesDisp as $p): ?>
                                        <option value="<?= $p['id_concepto']; ?>" <?= $p['id_concepto'] == $d['id_concepto'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($p['clave'] . ' - ' . $p['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="relative w-32">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                    <input type="number" step="0.01" name="detalles[<?= $idx; ?>][monto]" class="w-full pl-6 pr-2 py-1 rounded border border-gray-300 text-right text-sm font-mono focus:border-emerald-500 focus:outline-none input-monto input-percepcion" value="<?= $d['monto']; ?>" required>
                                </div>
                                <button type="button" class="text-red-400 hover:text-red-600 px-2" onclick="removeRow('row-<?= $idx; ?>')"><i class="fas fa-trash"></i></button>
                            </div>
                        <?php 
                            $idx++;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <div class="bg-white border-t border-emerald-100 px-4 py-3 flex justify-between items-center font-bold text-emerald-700">
                    <span>Total Percepciones:</span>
                    <span id="totalPercepciones" class="font-mono text-lg">$0.00</span>
                </div>
            </div>

            <!-- Deducciones -->
            <div class="bg-white border border-red-100 rounded-xl shadow-sm overflow-hidden h-full flex flex-col">
                <div class="bg-red-50/50 border-b border-red-100 px-4 py-3 flex justify-between items-center">
                    <h3 class="font-bold text-red-800">Deducciones</h3>
                    <button type="button" class="text-red-700 hover:text-red-900 text-sm font-bold bg-red-100 hover:bg-red-200 px-3 py-1 rounded transition" onclick="addConcepto('DEDUCCION')">
                        <i class="fas fa-plus mr-1"></i> Agregar
                    </button>
                </div>
                <div class="p-4 flex-grow bg-slate-50 relative">
                    <div class="text-xs text-gray-500 mb-2 italic">
                        <i class="fas fa-info-circle mr-1"></i> Ingrese valores como porcentaje (ej: 5%) para calcular sobre Salario Base.
                    </div>
                    <div id="container-deducciones" class="space-y-2">
                        <?php 
                        foreach ($detalles as $d): 
                            if ($d['tipo'] !== 'DEDUCCION') continue;
                        ?>
                            <div class="flex items-center gap-2 p-2 bg-white border border-red-200 rounded shadow-sm row-concepto" id="row-<?= $idx; ?>">
                                <select name="detalles[<?= $idx; ?>][id_concepto]" class="flex-grow rounded border border-gray-300 px-2 py-1 text-sm focus:border-red-500 focus:outline-none select-concepto" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($deduccionesDisp as $ded): ?>
                                        <option value="<?= $ded['id_concepto']; ?>" <?= $ded['id_concepto'] == $d['id_concepto'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($ded['clave'] . ' - ' . $ded['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="relative w-32">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                    <input type="text" name="detalles[<?= $idx; ?>][monto]" class="w-full pl-6 pr-2 py-1 rounded border border-gray-300 text-right text-sm font-mono focus:border-red-500 focus:outline-none input-monto input-deduccion" value="<?= $d['monto']; ?>" required onchange="processMonto(this)">
                                </div>
                                <button type="button" class="text-red-400 hover:text-red-600 px-2" onclick="removeRow('row-<?= $idx; ?>')"><i class="fas fa-trash"></i></button>
                            </div>
                        <?php 
                            $idx++;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <div class="bg-white border-t border-red-100 px-4 py-3 flex justify-between items-center font-bold text-red-700">
                    <span>Total Deducciones:</span>
                    <span id="totalDeducciones" class="font-mono text-lg">$0.00</span>
                </div>
            </div>
        </div>

        <!-- Total Neto -->
        <div class="flex justify-center mb-8">
            <div class="bg-white border border-black/10 rounded-xl px-8 py-6 shadow-soft text-center min-w-[300px]">
                <div class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-1">Total Neto a Pagar</div>
                <div class="text-4xl font-mono font-bold text-vc-ink" id="totalNeto">$0.00</div>
            </div>
        </div>

        <div class="flex justify-end gap-4 pb-12">
            <a href="index.php?controller=nomina&action=show&id=<?= $nomina['id_periodo']; ?>" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" class="px-8 py-3 rounded-xl bg-vc-teal text-white font-bold shadow-lg shadow-vc-teal/30 hover:bg-teal-500 hover:scale-105 transition transform">
                <i class="fas fa-save mr-2"></i> Guardar Cambios
            </button>
        </div>
    </form>
  </main>

  <script>
    let rowIdx = <?= $idx; ?>;
    const salarioBase = <?= $salarioBase ?: 0; ?>;
    
    // Arrays de conceptos para usarlos en JS
    const percepcionesList = <?= json_encode($percepcionesDisp); ?>;
    const deduccionesList = <?= json_encode($deduccionesDisp); ?>;

    function addConcepto(tipo) {
        rowIdx++;
        const container = document.getElementById(tipo === 'PERCEPCION' ? 'container-percepciones' : 'container-deducciones');
        const list = tipo === 'PERCEPCION' ? percepcionesList : deduccionesList;
        const colorClass = tipo === 'PERCEPCION' ? 'border-emerald-200' : 'border-red-200';
        const focusClass = tipo === 'PERCEPCION' ? 'focus:border-emerald-500' : 'focus:border-red-500';
        const inputClass = tipo === 'PERCEPCION' ? 'input-percepcion' : 'input-deduccion';
        const typeInput = tipo === 'PERCEPCION' ? 'number" step="0.01' : 'text" onchange="processMonto(this)';
        
        let optionsHtml = '<option value="">Seleccione...</option>';
        list.forEach(item => {
            optionsHtml += `<option value="${item.id_concepto}">${item.clave} - ${item.nombre}</option>`;
        });

        const html = `
            <div class="flex items-center gap-2 p-2 bg-white border ${colorClass} rounded shadow-sm row-concepto" id="row-${rowIdx}">
                <select name="detalles[${rowIdx}][id_concepto]" class="flex-grow rounded border border-gray-300 px-2 py-1 text-sm ${focusClass} focus:outline-none select-concepto" required>
                    ${optionsHtml}
                </select>
                <div class="relative w-32">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                    <input type="${typeInput}" name="detalles[${rowIdx}][monto]" class="w-full pl-6 pr-2 py-1 rounded border border-gray-300 text-right text-sm font-mono ${focusClass} focus:outline-none input-monto ${inputClass}" value="0.00" required>
                </div>
                <button type="button" class="text-red-400 hover:text-red-600 px-2" onclick="removeRow('row-${rowIdx}')"><i class="fas fa-trash"></i></button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        calcTotals();
    }

    function removeRow(id) {
        const row = document.getElementById(id);
        if(row) {
            row.remove();
            calcTotals();
        }
    }

    // Procesa el input: si termina en %, calcula porcentaje sobre salario.
    function processMonto(input) {
        let val = input.value.trim();
        
        if (val.endsWith('%')) {
            let percent = parseFloat(val.replace('%', ''));
            if (!isNaN(percent)) {
                let calculated = (salarioBase * percent) / 100;
                input.value = calculated.toFixed(2);
            }
        } 
        // Si no es %, asegurarse de formatear como número
        else {
            let num = parseFloat(val);
            if (!isNaN(num)) {
                input.value = num.toFixed(2);
            } else {
                input.value = "0.00";
            }
        }
        calcTotals();
    }

    function calcTotals() {
        let totalP = 0;
        let totalD = 0;

        document.querySelectorAll('.input-percepcion').forEach(el => {
            let val = parseFloat(el.value);
            if (!isNaN(val)) totalP += val;
        });

        document.querySelectorAll('.input-deduccion').forEach(el => {
            let val = parseFloat(el.value);
            if (!isNaN(val)) totalD += val;
        });

        let totalN = totalP - totalD;

        document.getElementById('totalPercepciones').textContent = formatCurrency(totalP);
        document.getElementById('totalDeducciones').textContent = formatCurrency(totalD);
        document.getElementById('totalNeto').textContent = formatCurrency(totalN);
    }
    
    function formatCurrency(amount) {
        return '$' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Event listeners para recalcular al cambiar
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-monto')) {
           calcTotals();
        }
    });

    // Calcular al inicio
    document.addEventListener('DOMContentLoaded', calcTotals);

  </script>
</body>
</html>
