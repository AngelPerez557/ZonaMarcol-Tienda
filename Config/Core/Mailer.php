<?php

/**
 * Mailer.php — Wrapper minimal sobre mail() nativo de PHP.
 *
 * Decisiones:
 *   - Sin PHPMailer porque el proyecto no usa Composer.
 *   - Para producción seria, conviene reemplazar la implementación de
 *     send() por SMTP autenticado (sendmail no firma DKIM y manda a spam).
 *   - Política fail-soft: si falla el envío, se loguea pero NO se rompe
 *     el flujo de negocio. El estado de la orden ya cambió en BD; el
 *     email es enriquecimiento.
 *   - MAIL_ENABLED=false simula envío exitoso (útil para dev/staging).
 *
 * Uso:
 *   Mailer::send(
 *       'cliente@ejemplo.com',
 *       'Tu orden está lista',
 *       Mailer::renderTemplate('orden_lista', ['codigo'=>'OS-00010', 'cliente'=>'Juan']),
 *       'Juan Pérez'
 *   );
 */
class Mailer
{
    /**
     * Envía un email HTML al destinatario.
     * Devuelve true si la cola del MTA lo aceptó, false si no.
     */
    public static function send(
        string $to,
        string $subject,
        string $bodyHtml,
        ?string $toName = null
    ): bool {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('[Mailer::send] Email destino inválido: ' . $to);
            return false;
        }

        if (!MAIL_ENABLED) {
            error_log("[Mailer:disabled] To={$to} Subject={$subject}");
            return true;   // simulación de éxito
        }

        $fromName  = MAIL_FROM_NAME;
        $fromEmail = MAIL_FROM;

        // Headers RFC 5322
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::encodeName($fromName) . " <{$fromEmail}>",
            'Reply-To: ' . $fromEmail,
            'X-Mailer: ZonaMarcol/Mailer',
        ];

        $toHeader = $toName
            ? self::encodeName($toName) . " <{$to}>"
            : $to;

        try {
            $ok = @mail(
                $toHeader,
                self::encodeName($subject),  // subject va en RFC2047 si tiene tildes
                $bodyHtml,
                implode("\r\n", $headers),
                '-f' . $fromEmail            // sender para SPF
            );
            if (!$ok) {
                error_log("[Mailer::send] mail() retornó false. To={$to}");
            }
            return (bool) $ok;
        } catch (\Throwable $e) {
            error_log('[Mailer::send] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Render minimal de un template HTML — placeholders {{clave}}.
     * Los templates viven en Config/Core/MailTemplates/{nombre}.html.
     */
    public static function renderTemplate(string $nombre, array $vars = []): string
    {
        $path = ROOT . 'Config' . DS . 'Core' . DS . 'MailTemplates' . DS . $nombre . '.html';
        if (!is_readable($path)) {
            error_log('[Mailer::renderTemplate] Template no encontrado: ' . $path);
            return '';
        }
        $tpl = file_get_contents($path);
        $vars['APP_NAME'] = $vars['APP_NAME'] ?? APP_NAME;
        $vars['APP_URL']  = $vars['APP_URL']  ?? APP_URL;
        foreach ($vars as $k => $v) {
            $tpl = str_replace('{{' . $k . '}}', (string) $v, $tpl);
        }
        return $tpl;
    }

    /**
     * Encoding RFC 2047 para headers con caracteres no-ASCII (acentos).
     */
    private static function encodeName(string $name): string
    {
        // Si es puro ASCII, no codifica.
        if (preg_match('/[^\x20-\x7E]/', $name)) {
            return '=?UTF-8?B?' . base64_encode($name) . '?=';
        }
        return $name;
    }
}
