-- ============================================================
--  zonamarcol_rate_limit_scopes.sql
--  ZonaMarcol — DeskCod
-- ------------------------------------------------------------
--  PROPÓSITO — Extender el rate limiter para soportar scopes
--  independientes por endpoint sensible. Hasta ahora todos los
--  fallos de una IP se acumulaban en un solo contador — un brute
--  force de login admin bloqueaba también el login de tienda.
--
--  Diseño:
--    - Columna `scope` VARCHAR(40) NOT NULL.
--    - PRIMARY KEY ahora es (ip, scope).
--    - Default 'global' para filas viejas (compatibilidad).
--    - SPs aceptan p_scope con default 'global' simulado por wrapper
--      en PHP (MySQL no soporta argumentos opcionales en stored procs).
--
--  Scopes usados por la app (Etapa B):
--    'login_admin'        — AuthController::login
--    'login_tienda'       — TiendaController::procesarLogin
--    'registro_tienda'    — TiendaController::guardarRegistro
--    'solicitud_servicio' — TiendaController::guardarSolicitudServicio
--    'pedido_camiseta'    — TiendaController::configuradorSave
--
--  IDEMPOTENTE: ALTER condicional + DROP/CREATE de SPs.
-- ============================================================

USE `zonamarcol`;

-- ─────────────────────────────────────────────
-- 1) ALTER TABLE — agregar columna scope y reconstruir PRIMARY KEY
-- ─────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_temp_add_rate_limit_scope;
DELIMITER $$
CREATE PROCEDURE sp_temp_add_rate_limit_scope()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'rate_limits'
          AND COLUMN_NAME  = 'scope'
    ) THEN
        ALTER TABLE `rate_limits`
            ADD COLUMN `scope` VARCHAR(40) NOT NULL DEFAULT 'global' AFTER `ip`,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (`ip`, `scope`);
    END IF;
END$$
DELIMITER ;
CALL sp_temp_add_rate_limit_scope();
DROP PROCEDURE sp_temp_add_rate_limit_scope;

-- ─────────────────────────────────────────────
-- 2) Re-crear SPs con parámetro scope
-- ─────────────────────────────────────────────
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_rate_limits_check$$
CREATE PROCEDURE sp_rate_limits_check(
    IN p_ip    VARCHAR(45),
    IN p_scope VARCHAR(40)
)
BEGIN
    SELECT
        intentos,
        IF(bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW(), 1, 0) AS bloqueado,
        IF(bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW(),
           TIMESTAMPDIFF(MINUTE, NOW(), bloqueado_hasta), 0) AS minutos_restantes
    FROM rate_limits
    WHERE ip = p_ip AND scope = p_scope
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_rate_limits_register_fallo$$
CREATE PROCEDURE sp_rate_limits_register_fallo(
    IN p_ip            VARCHAR(45),
    IN p_scope         VARCHAR(40),
    IN p_max_intentos  INT,
    IN p_bloqueo_min   INT
)
BEGIN
    INSERT INTO rate_limits (ip, scope, intentos, ultima_falla, bloqueado_hasta)
    VALUES (p_ip, p_scope, 1, NOW(), NULL)
    ON DUPLICATE KEY UPDATE
        intentos        = intentos + 1,
        ultima_falla    = NOW(),
        bloqueado_hasta = IF(intentos + 1 >= p_max_intentos,
                             DATE_ADD(NOW(), INTERVAL p_bloqueo_min MINUTE),
                             bloqueado_hasta);
END$$

DROP PROCEDURE IF EXISTS sp_rate_limits_limpiar$$
CREATE PROCEDURE sp_rate_limits_limpiar(
    IN p_ip    VARCHAR(45),
    IN p_scope VARCHAR(40)
)
BEGIN
    DELETE FROM rate_limits WHERE ip = p_ip AND scope = p_scope;
END$$

DELIMITER ;

-- ─────────────────────────────────────────────
-- Verificación
-- ─────────────────────────────────────────────
-- SHOW COLUMNS FROM rate_limits LIKE 'scope';
-- SHOW CREATE PROCEDURE sp_rate_limits_check\G
