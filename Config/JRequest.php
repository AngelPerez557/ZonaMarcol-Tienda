<?php

/**
 * JRequest.php — Encapsula la petición HTTP entrante
 *
 * Responsabilidad única (SRP):
 *   1. Parsear la URL recibida por el Front Controller en segmentos
 *   2. Exponer el controlador / método / segmentos para el JRouter
 *   3. Detectar el tipo de petición (POST / GET / AJAX)
 *
 * NO sanea inputs. Política del sistema: escape on output (en Views),
 * NO sanitize on input. El saneo automático rompía passwords con caracteres
 * especiales, JSON, comparaciones de strings, y forzaba doble encoding
 * cuando la View también escapa. (Hallazgo F-03 / F-35).
 *
 * Para leer parámetros, los Controllers acceden directo a $_POST / $_GET
 * y la View aplica htmlspecialchars al imprimir.
 */
class JRequest
{
    // Segmentos parseados de la URL.
    // Ej: "Ejemplo/index/5" → ['Ejemplo', 'index', '5']
    private array $segments = [];

    public function __construct()
    {
        $this->parseUrl();
    }

    // ─────────────────────────────────────────────
    // PARSEO DE URL
    // ─────────────────────────────────────────────

    // Extrae los segmentos de la URL recibida por el Front Controller.
    // FILTER_SANITIZE_URL solo se aplica al path de la URL, NUNCA a los
    // parámetros GET/POST.
    private function parseUrl(): void
    {
        $url = $_GET['url'] ?? '';
        $url = filter_var(trim($url, '/'), FILTER_SANITIZE_URL);
        $this->segments = $url !== '' ? explode('/', $url) : [];
    }

    // ─────────────────────────────────────────────
    // ACCESO A SEGMENTOS — usado por JRouter
    // ─────────────────────────────────────────────

    // Primer segmento — nombre del controlador
    public function getController(): string
    {
        return $this->segments[0] ?? 'Dashboard';
    }

    // Segundo segmento — nombre del método
    public function getMethod(): string
    {
        return $this->segments[1] ?? 'index';
    }

    // Acceso a un segmento puntual por posición (base 0)
    public function getSegment(int $index): ?string
    {
        return $this->segments[$index] ?? null;
    }

    // Todos los segmentos
    public function getSegments(): array
    {
        return $this->segments;
    }

    // ─────────────────────────────────────────────
    // DETECCIÓN DE TIPO DE PETICIÓN
    // ─────────────────────────────────────────────

    public function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    public function isGet(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET';
    }

    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
