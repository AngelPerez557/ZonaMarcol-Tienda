<?php

/**
 * Csrf.php — Helper centralizado de validación CSRF
 *
 * F-22 / F-29 — Antes 17 controllers comparaban el token con `!==`,
 * vulnerable (en teoría) a timing attacks. Esta clase centraliza
 * la validación con `hash_equals()` (constant-time) y deja un único
 * punto que se puede auditar y endurecer.
 *
 * Uso típico en un controller:
 *
 *   if (!Csrf::validate()) {
 *       http_response_code(403);
 *       exit();
 *   }
 *
 * El token se genera en index.php al iniciar la sesión:
 *   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
 *
 * Y se incluye en cada formulario con:
 *   <input type="hidden" name="csrf_token" value="<?= Csrf::token() ?>">
 */
class Csrf
{
    // Nombre del campo en formularios y sesión
    private const SESSION_KEY = 'csrf_token';
    private const FIELD_NAME  = 'csrf_token';

    /**
     * Retorna el token CSRF actual de la sesión.
     * Si no existe, genera uno nuevo (útil para AJAX donde
     * el header del index.php no se haya ejecutado).
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Valida el token CSRF recibido contra el de la sesión.
     *
     * @param string|null $token Token a validar. Si es null, lo busca en $_POST['csrf_token'].
     * @return bool true si el token es válido, false si no.
     *
     * Usa hash_equals() para comparación constant-time, evitando
     * timing attacks que podrían filtrar el token byte a byte.
     */
    public static function validate(?string $token = null): bool
    {
        $sessionToken = $_SESSION[self::SESSION_KEY] ?? '';

        if (empty($sessionToken)) {
            return false;
        }

        // Si no se pasó token explícito, leerlo de POST
        $received = $token ?? ($_POST[self::FIELD_NAME] ?? '');

        if (!is_string($received) || $received === '') {
            return false;
        }

        return hash_equals($sessionToken, $received);
    }

    /**
     * Valida CSRF y si falla retorna 403 + JSON, cortando ejecución.
     * Atajo para endpoints AJAX que solo necesitan rechazar y salir.
     */
    public static function validateOrFail(): void
    {
        if (!self::validate()) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error'   => 'Token CSRF inválido o ausente.',
            ]);
            exit();
        }
    }

    /**
     * Genera el HTML de un input hidden con el token actual.
     * Para usar dentro de formularios:
     *
     *   <form method="post">
     *     <?= Csrf::field() ?>
     *     ...
     *   </form>
     */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD_NAME
            . '" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }

    /**
     * Regenera el token (útil después de login para evitar
     * que un atacante reutilice un token previo a la autenticación).
     */
    public static function regenerate(): string
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        return $_SESSION[self::SESSION_KEY];
    }
}
