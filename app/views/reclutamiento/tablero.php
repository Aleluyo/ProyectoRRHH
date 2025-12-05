<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tablero Kanban - Reclutamiento</title>
    <!-- Tailwind CSS (via CDN for simplicity, assuming global config in layout) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        vc: {
                            pink: '#ff78b5', peach: '#ffc9a9', teal: '#36d1cc',
                            sand: '#ffe9c7', ink: '#0a2a5e', neon: '#a7fffd'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .kanban-col { min-height: 500px; }
        .dragging { opacity: 0.5; }
    </style>
</head>
<body class="bg-gray-50 text-vc-ink font-sans">

    <!-- Navbar / Header would go here (simplified for this view) -->
    <div class="max-w-[1920px] mx-auto p-6">
        
        <!-- Header & Filters -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-vc-ink">Tablero de Reclutamiento</h1>
                <p class="text-gray-500">Gestiona el flujo de candidatos por vacante.</p>
            </div>
            
            <form action="index.php" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="controller" value="tablero">
                <input type="hidden" name="action" value="index">
                
                <label for="id_vacante" class="font-bold text-sm">Vacante:</label>
                <select name="id_vacante" id="id_vacante" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-vc-teal">
                    <option value="">-- Selecciona una vacante --</option>
                    <?php foreach ($vacantes as $v): ?>
                        <option value="<?= $v['id_vacante'] ?>" <?= ($idVacante == $v['id_vacante']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['puesto_nombre'] ?? 'Vacante ' . $v['id_vacante']) ?> 
                            (<?= htmlspecialchars($v['area_nombre'] ?? '-') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if($idVacante > 0): ?>
                    <a href="<?= url('index.php?controller=tablero&action=index') ?>" class="text-xs text-red-500 hover:underline">Limpiar</a>
                <?php endif; ?>
                <a href="<?= url('index.php') ?>" class="ml-4 text-sm text-gray-500 hover:text-vc-ink">&larr; Volver al Inicio</a>
            </form>
        </div>

        <!-- Kanban Board -->
        <?php if ($idVacante > 0): ?>
            <div class="flex overflow-x-auto gap-4 pb-4 items-start" id="kanban-container">
                
                <?php foreach ($columnas as $estado => $postulaciones): ?>
                    <?php 
                        // Color styling per column
                        $headerColor = match($estado) {
                            'POSTULADO' => 'border-gray-400 text-gray-700',
                            'SCREENING' => 'border-blue-400 text-blue-700',
                            'ENTREVISTA' => 'border-vc-peach text-orange-700',
                            'PRUEBA' => 'border-purple-400 text-purple-700',
                            'OFERTA' => 'border-yellow-400 text-yellow-700',
                            'CONTRATADO' => 'border-vc-teal text-teal-700',
                            'RECHAZADO' => 'border-red-400 text-red-700',
                            default => 'border-gray-200'
                        };
                    ?>
                    
                    <div class="min-w-[300px] w-[300px] flex-shrink-0 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full max-h-[80vh]">
                        <!-- Column Header -->
                        <div class="p-4 border-b-2 <?= $headerColor ?> flex justify-between items-center bg-gray-50 rounded-t-xl">
                            <h3 class="font-bold text-sm uppercase tracking-wide"><?= $estado ?></h3>
                            <span class="bg-white px-2 py-0.5 rounded-full text-xs font-bold shadow-sm border border-gray-100">
                                <?= count($postulaciones) ?>
                            </span>
                        </div>
                        
                        <!-- Draggable Area -->
                        <div class="p-3 bg-gray-50/50 flex-1 overflow-y-auto kanban-col space-y-3" 
                             data-estado="<?= $estado ?>"
                             ondragover="allowDrop(event)" 
                             ondrop="drop(event)">
                            
                            <?php foreach ($postulaciones as $p): ?>
                                <div class="bg-white p-4 rounded-lg shadow border border-gray-200 cursor-grab hover:shadow-md transition group relative"
                                     draggable="true" 
                                     ondragstart="drag(event)" 
                                     id="card-<?= $p['id_postulacion'] ?>"
                                     data-id="<?= $p['id_postulacion'] ?>">
                                    
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-vc-ink text-sm">
                                            <?= htmlspecialchars($p['candidato_nombre'] ?? 'Candidato sin nombre') ?>
                                        </h4>
                                        <a href="<?= url('index.php?controller=postulacion&action=edit&id=' . $p['id_postulacion']) ?>" 
                                           class="text-gray-300 hover:text-vc-teal" title="Ver detalle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                    </div>
                                    
                                    <div class="text-xs text-gray-500 mb-2">
                                        <p><?= htmlspecialchars($p['candidato_correo'] ?? '') ?></p>
                                        <p class="mt-1">Aplicado: <?= date('d M', strtotime($p['aplicada_en'])) ?></p>
                                    </div>

                                    <?php if(!empty($p['comentarios'])): ?>
                                        <div class="bg-yellow-50 text-[10px] p-2 rounded text-yellow-800 border border-yellow-100 mb-2">
                                            "<?= htmlspecialchars(substr($p['comentarios'], 0, 50)) ?>..."
                                        </div>
                                    <?php endif; ?>

                                    <!-- Quick Actions (optional) -->
                                    <div class="mt-2 pt-2 border-t border-gray-100 flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <!-- Could add mini buttons here -->
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (empty($postulaciones)): ?>
                                <div class="text-center py-8 text-gray-300 text-xs italic pointer-events-none select-none">
                                    Vacío
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center p-20 bg-white rounded-xl shadow-sm border border-dashed border-gray-300">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-600">Selecciona una Vacante</h2>
                <p class="text-gray-400 text-sm mt-1">Para ver el tablero, primero elige una vacante del menú superior.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- Drag & Drop Logic -->
    <script>
        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.target.classList.add('dragging');
        }

        function allowDrop(ev) {
            ev.preventDefault();
        }

        function drop(ev) {
            ev.preventDefault();
            var data = ev.dataTransfer.getData("text");
            var card = document.getElementById(data);
            card.classList.remove('dragging');
            
            // Find drop target (must be the container div with data-estado)
            let targetCol = ev.target;
            while (!targetCol.hasAttribute('data-estado') && targetCol.parentElement) {
                targetCol = targetCol.parentElement;
            }
            
            if (targetCol && targetCol.hasAttribute('data-estado')) {
                // Move element in DOM
                targetCol.appendChild(card);
                
                // Remove 'Empty' placeholder if exists
                // (Optional refinement)

                // Call API to update status
                const idPostulacion = card.getAttribute('data-id');
                const nuevoEstado = targetCol.getAttribute('data-estado');
                
                updateStatus(idPostulacion, nuevoEstado);
            }
        }

        function updateStatus(id, estado) {
            fetch('index.php?controller=tablero&action=move', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_postulacion: id, nuevo_estado: estado })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    // Success visual feedback?
                    console.log('Moved to ' + estado);
                } else {
                    alert('Error al mover: ' + (data.error || 'Desconocido'));
                    location.reload(); // Revert
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error de red');
                location.reload();
            });
        }
    </script>
</body>
</html>
