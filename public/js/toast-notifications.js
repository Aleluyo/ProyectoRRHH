// Sistema de notificaciones flotantes (Toast Notifications)
// Agregar este código al final del <script> en show.php, antes del cierre </script>

// Función para mostrar notificaciones
function showToast(message, type = "success") {
  // Crear contenedor si no existe
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    container.className = "fixed top-20 right-4 z-50 space-y-3";
    document.body.appendChild(container);
  }

  // Crear notificación
  const toast = document.createElement("div");
  toast.className = `transform transition-all duration-300 ease-in-out translate-x-full opacity-0 
                       max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden`;

  const textColor = type === "success" ? "text-green-800" : "text-red-800";
  const iconColor = type === "success" ? "text-green-400" : "text-red-400";
  const icon =
    type === "success"
      ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
      : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />';

  toast.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 ${iconColor}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        ${icon}
                    </svg>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium ${textColor}">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button onclick="this.closest('div').parentElement.parentElement.remove()" 
                            class="inline-flex ${textColor} hover:opacity-75 focus:outline-none">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;

  container.appendChild(toast);

  // Animar entrada
  setTimeout(() => {
    toast.classList.remove("translate-x-full", "opacity-0");
    toast.classList.add("translate-x-0", "opacity-100");
  }, 100);

  // Auto-cerrar después de 5 segundos
  setTimeout(() => {
    toast.classList.add("translate-x-full", "opacity-0");
    setTimeout(() => toast.remove(), 300);
  }, 5000);
}

// Detectar parámetros de URL para mostrar notificaciones
const urlParams = new URLSearchParams(window.location.search);

if (urlParams.has("success")) {
  showToast("Los datos se actualizaron correctamente", "success");
  // Limpiar URL sin recargar la página
  const employeeId = urlParams.get("id");
  window.history.replaceState(
    {},
    document.title,
    window.location.pathname +
      "?controller=empleado&action=show&id=" +
      employeeId
  );
}

if (urlParams.has("error")) {
  const errorType = urlParams.get("error");
  let errorMessage = "La actualización no se realizó correctamente";

  if (errorType === "nombre_requerido") {
    errorMessage = "El nombre del empleado es requerido";
  } else if (errorType === "puesto_requerido") {
    errorMessage = "El puesto es requerido";
  } else if (errorType === "update_failed") {
    errorMessage = "Error al actualizar los datos";
  }

  showToast(errorMessage, "error");
  // Limpiar URL sin recargar la página
  const employeeId = urlParams.get("id");
  window.history.replaceState(
    {},
    document.title,
    window.location.pathname +
      "?controller=empleado&action=show&id=" +
      employeeId
  );
}
