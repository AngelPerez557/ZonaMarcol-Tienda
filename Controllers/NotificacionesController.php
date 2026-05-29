<?php

class NotificacionesController
{
    private NotificacionModel $model;

    public function __construct()
    {
        Auth::check();
        $this->model = new NotificacionModel();
    }

    // ─────────────────────────────────────────────
    // OBTENER — todas las notificaciones (JSON)
    // URL: /Notificaciones/obtener
    // ─────────────────────────────────────────────
    public function obtener(): void
    {
        $notificaciones = $this->model->findAll();
        $noLeidas       = $this->model->countNoLeidas();

        header('Content-Type: application/json');
        echo json_encode([
            'notificaciones' => $notificaciones,
            'no_leidas'      => $noLeidas,
        ]);
        exit();
    }

    // ─────────────────────────────────────────────
    // MARCAR LEÍDA (POST — JSON)
    // URL: /Notificaciones/marcarLeida
    // ─────────────────────────────────────────────
    public function marcarLeida(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!Csrf::validate()) {
            http_response_code(403); exit();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $this->model->marcarLeida($id);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    // ─────────────────────────────────────────────
    // MARCAR TODAS LEÍDAS (POST — JSON)
    // URL: /Notificaciones/marcarTodas
    // ─────────────────────────────────────────────
    public function marcarTodas(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!Csrf::validate()) {
            http_response_code(403); exit();
        }

        $this->model->marcarTodasLeidas();

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    // ─────────────────────────────────────────────
    // ELIMINAR (POST — JSON)
    // URL: /Notificaciones/eliminar
    // ─────────────────────────────────────────────
    public function eliminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!Csrf::validate()) {
            http_response_code(403); exit();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $this->model->delete($id);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    // ─────────────────────────────────────────────
    // STREAM (SSE — Server-Sent Events)
    // URL: /Notificaciones/stream
    //
    // Mantiene la conexión HTTP abierta y empuja eventos cuando hay
    // notificaciones nuevas. Reemplaza el polling cada 30s.
    //
    // Protocolo:
    //   - Inicializa enviando count actual.
    //   - Cada 3s: si hay notificaciones con id > lastId, las manda como
    //     evento "notif" + refresca el "count".
    //   - Sale del loop si el cliente desconecta (connection_aborted)
    //     o al alcanzar el timeout máximo (60s) — EventSource reconecta
    //     solo del lado client.
    //
    // IMPORTANTE: el endpoint mantiene la conexión PHP abierta. Verificar
    // que el server no esté detrás de FastCGI con buffer agresivo
    // (Nginx: proxy_buffering off + X-Accel-Buffering: no header).
    // ─────────────────────────────────────────────
    public function stream(): void
    {
        // Auth::check() del constructor ya redirige si no hay sesión, pero
        // SSE no tolera redirects — el browser nunca recibiría el stream.
        // Verificamos explícitamente y cortamos con 403 si falta sesión.
        if (!Auth::isLoggedIn()) {
            http_response_code(403); exit();
        }

        // Headers SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');   // nginx

        // Romper buffers de PHP/web server
        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', 'off');
        @ini_set('implicit_flush', '1');
        while (ob_get_level() > 0) @ob_end_clean();
        ob_implicit_flush(true);

        // Sin límite del tiempo de ejecución para mantener el stream.
        @set_time_limit(0);

        $lastId    = (int) ($_SERVER['HTTP_LAST_EVENT_ID'] ?? $this->model->maxId());
        $maxTicks  = 20;    // 20 * 3s = 60s de vida del stream
        $intervalo = 3;     // segundos entre chequeos

        // Tick inicial: mandar count actual.
        $this->emitirEvento('count', (string) $this->model->countNoLeidas());

        for ($i = 0; $i < $maxTicks; $i++) {
            if (connection_aborted()) {
                break;
            }

            $nuevas = $this->model->findRecent($lastId);
            if (!empty($nuevas)) {
                foreach ($nuevas as $n) {
                    $lastId = max($lastId, (int) $n['id']);
                    $this->emitirEvento('notif', json_encode([
                        'id'      => (int) $n['id'],
                        'tipo'    => $n['tipo'],
                        'titulo'  => $n['titulo'],
                        'mensaje' => $n['mensaje'],
                        'url'     => $n['url'],
                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
                    (int) $n['id']);
                }
                $this->emitirEvento('count', (string) $this->model->countNoLeidas());
            }

            // Comentario keep-alive cada tick — algunos proxies cierran
            // conexiones sin tráfico.
            echo ": keep-alive\n\n";
            @flush();

            sleep($intervalo);
        }

        exit();   // el cliente reconectará
    }

    /** Helper de formato SSE. */
    private function emitirEvento(string $event, string $data, ?int $id = null): void
    {
        if ($id !== null) echo "id: {$id}\n";
        echo "event: {$event}\n";
        // Datos multilínea — cada línea con prefix "data: "
        foreach (explode("\n", $data) as $linea) {
            echo "data: {$linea}\n";
        }
        echo "\n";
        @flush();
    }
}