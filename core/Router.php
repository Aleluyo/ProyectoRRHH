<?php
declare(strict_types=1);

final class Router {
  private array $routes = [];

  public function get(string $p, callable $h): void { $this->routes['GET'][$p]  = $h; }
  public function post(string $p, callable $h): void { $this->routes['POST'][$p] = $h; }

  private function normalizePath(string $uri): string {
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';

    // Detecta el base path (carpeta donde vive index.php), ej: /ProyectoRRHH
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($base && str_starts_with($path, $base)) {
      $path = substr($path, strlen($base)) ?: '/';
    }

    // Normaliza doble slash y quita trailing slash (excepto '/')
    $path = preg_replace('#//+#', '/', $path);
    if ($path !== '/' && str_ends_with($path, '/')) $path = rtrim($path, '/');

    return $path ?: '/';
  }

  public function dispatch(string $method, string $uri): void {
    $path = $this->normalizePath($uri);
    $map  = $this->routes[$method] ?? [];
    $handler = $map[$path] ?? null;

    if (!$handler) {
      http_response_code(404);
      echo '404';
      return;
    }
    $handler();
  }
}
