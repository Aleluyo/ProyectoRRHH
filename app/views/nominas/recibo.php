<?php
require_once __DIR__ . '/../../../config/paths.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Recibo de Nómina</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
      @media print {
          .no-print { display: none; }
          body { background: white; }
          .shadow-sm { box-shadow: none; }
          .border { border: 1px solid #ddd; }
      }
  </style>
</head>
<body class="bg-gray-100 p-8 font-sans text-gray-800">

  <div class="max-w-3xl mx-auto mb-6 no-print">
      <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
          Imprimir / Guardar PDF
      </button>
  </div>

  <div class="max-w-3xl mx-auto bg-white p-8 border border-gray-200 shadow-sm relative">
    
    <!-- Encabezado Recibo -->
    <div class="flex justify-between items-start mb-8 border-b border-gray-200 pb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-widest">Recibo de Nómina</h1>
            <p class="text-sm text-gray-500 mt-1 uppercase font-bold"><?= htmlspecialchars($empresa['nombre'] ?? 'Empresa') ?></p>
            <p class="text-xs text-gray-400">RFC: <?= htmlspecialchars($empresa['rfc'] ?? '') ?></p>
            <p class="text-xs text-gray-400 max-w-xs"><?= htmlspecialchars($empresa['direccion'] ?? '') ?></p>
        </div>
        <div class="text-right">
            <div class="text-sm font-bold text-gray-600">Periodo</div>
            <div class="text-lg font-mono capitalize">
                <?php require_once __DIR__ . '/../../helpers/dates.php'; ?>
                <?= fecha_es($nomina['fecha_inicio']) ?> - <?= fecha_es($nomina['fecha_fin']) ?>
            </div>
            <div class="text-xs text-blue-600 uppercase font-bold mt-1"><?= $nomina['periodo_tipo'] ?></div>
        </div>
    </div>

    <!-- Info Empleado -->
    <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
        <div>
            <h3 class="font-bold text-gray-400 text-xs uppercase mb-2">Empleado</h3>
            <p class="font-bold text-lg text-gray-900"><?= $nomina['empleado_nombre'] ?></p>
            <p>RFC: <?= $nomina['rfc'] ?? '-' ?></p>
            <p>CURP: <?= $nomina['curp'] ?? '-' ?></p>
            <p>NSS: <?= $nomina['nss'] ?? '-' ?></p>
        </div>
        <div class="text-right">
            <h3 class="font-bold text-gray-400 text-xs uppercase mb-2">Datos Laborales</h3>
            <p><span class="font-bold">Puesto:</span> <?= $nomina['nombre_puesto'] ?></p>
            <p><span class="font-bold">Área:</span> <?= $nomina['nombre_area'] ?></p>
            <p><span class="font-bold">Ingreso:</span> <?= date('d/m/Y', strtotime($nomina['fecha_ingreso'])) ?></p>
        </div>
    </div>

    <!-- Detalles -->
    <div class="mb-8">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-100 text-gray-500 uppercase text-xs">
                    <th class="text-left py-2 w-1/2">Concepto</th>
                    <th class="text-right py-2 w-1/4">Percepción</th>
                    <th class="text-right py-2 w-1/4">Deducción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td class="py-2">
                        <span class="font-bold text-gray-700"><?= $d['clave'] ?></span>
                        <span class="text-gray-500 ml-2 text-xs"><?= $d['observacion'] ?? '-' ?></span>
                    </td>
                    <?php if ($d['tipo'] === 'PERCEPCION'): ?>
                        <td class="text-right py-2 text-gray-800">$<?= number_format((float)$d['monto'], 2) ?></td>
                        <td class="text-right py-2"></td>
                    <?php else: ?>
                        <td class="text-right py-2"></td>
                        <td class="text-right py-2 text-red-600">$<?= number_format((float)$d['monto'], 2) ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-800 font-bold text-base">
                    <td class="py-3 text-right pr-4 uppercase text-xs tracking-wider">Totales</td>
                    <td class="py-3 text-right bg-gray-50">$<?= number_format((float)$nomina['total_percepciones'], 2) ?></td>
                    <td class="py-3 text-right bg-gray-50 text-red-600">$<?= number_format((float)$nomina['total_deducciones'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Neto -->
    <div class="flex justify-end mb-12">
        <div class="bg-gray-900 text-white px-6 py-4 rounded-lg shadow-lg">
            <div class="text-xs text-gray-400 uppercase tracking-widest mb-1 text-right">Neto a Pagar</div>
            <div class="text-3xl font-mono font-bold">$<?= number_format((float)$nomina['total_neto'], 2) ?> <span class="text-sm font-sans text-gray-400">MXN</span></div>
        </div>
    </div>

    <div class="border-t border-dashed border-gray-300 pt-8 text-center text-xs text-gray-400">
        <p>Recibí de conformidad la cantidad neta a pagar por concepto de mi salario y prestaciones.</p>
        <div class="mt-12 w-64 mx-auto border-t border-gray-400 pt-2">Firma del Empleado</div>
    </div>

  </div>
</body>
</html>
