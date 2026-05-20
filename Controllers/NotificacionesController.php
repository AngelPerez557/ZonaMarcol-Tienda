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
}