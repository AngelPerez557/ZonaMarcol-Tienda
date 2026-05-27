-- ============================================================
--  zonamarcol_ronda_b_pedidos_online.sql
--  ZonaMarcol — DeskCod
-- ------------------------------------------------------------
--  PROPÓSITO — Habilitar los pedidos online desde el storefront:
--
--   1. CAMISAS: agregar `comprobante_path` a `pedidos_camiseta` para
--      guardar el comprobante de transferencia que sube el cliente al
--      hacer el pedido.
--
--   2. SERVICIO TÉCNICO: tabla nueva `solicitudes_servicio` para que el
--      cliente envíe una solicitud online (no una orden — esa se abre
--      en recepción cuando el equipo llega físicamente). Cuando se
--      atiende, queda vinculada a la `orden_servicio` que la materializó.
--
--  IDEMPOTENTE: usa IF NOT EXISTS para la tabla; el ALTER tiene un
--  guard previo. Re-ejecutable sin error.
-- ============================================================

USE `zonamarcol`;

-- ─────────────────────────────────────────────
-- 1) ALTER TABLE pedidos_camiseta — comprobante_path
-- ─────────────────────────────────────────────
-- INFORMATION_SCHEMA chequea si la columna ya existe; si no, agrega.
-- El ALTER condicional se hace con un procedure efímero porque MySQL
-- no tiene ALTER TABLE ... ADD COLUMN IF NOT EXISTS hasta 8.0.29+.
DROP PROCEDURE IF EXISTS sp_temp_add_comprobante_pc;
DELIMITER $$
CREATE PROCEDURE sp_temp_add_comprobante_pc()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'pedidos_camiseta'
          AND COLUMN_NAME  = 'comprobante_path'
    ) THEN
        ALTER TABLE `pedidos_camiseta`
            ADD COLUMN `comprobante_path` VARCHAR(255) NULL AFTER `nota`;
    END IF;
END$$
DELIMITER ;
CALL sp_temp_add_comprobante_pc();
DROP PROCEDURE sp_temp_add_comprobante_pc;

-- ─────────────────────────────────────────────
-- 2) CREATE TABLE solicitudes_servicio
-- ─────────────────────────────────────────────
-- estado:
--   Pendiente   → recién enviada por el cliente
--   Atendida    → un empleado abrió la orden_servicio real (FK)
--   Rechazada   → no procede (con motivo)
-- orden_servicio_id: NULL hasta que se atiende. ON DELETE SET NULL
--   para no perder la solicitud si después se borra la orden.
CREATE TABLE IF NOT EXISTS `solicitudes_servicio` (
    `id`                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `cliente_id`          INT UNSIGNED   NOT NULL,
    `equipo_descripcion`  VARCHAR(255)   NOT NULL,
    `falla_reportada`     TEXT               NULL,
    `telefono_contacto`   VARCHAR(30)        NULL,
    `estado`              ENUM('Pendiente','Atendida','Rechazada') NOT NULL DEFAULT 'Pendiente',
    `orden_servicio_id`   INT UNSIGNED       NULL,
    `motivo_rechazo`      VARCHAR(255)       NULL,
    `created_at`          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atendida_at`         TIMESTAMP          NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ss_cliente` (`cliente_id`),
    KEY `idx_ss_estado`  (`estado`),
    KEY `idx_ss_orden`   (`orden_servicio_id`),
    CONSTRAINT `fk_ss_cliente` FOREIGN KEY (`cliente_id`)
        REFERENCES `clientes` (`id`),
    CONSTRAINT `fk_ss_orden`   FOREIGN KEY (`orden_servicio_id`)
        REFERENCES `ordenes_servicio` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
-- 3) Permiso opcional para gestionar solicitudes desde el admin
-- ─────────────────────────────────────────────
INSERT IGNORE INTO `permissions` (`nombre`, `slug`, `modulo`, `descripcion`) VALUES
    ('Gestionar solicitudes de servicio', 'servicio.solicitudes', 'servicio',
     'Ver y atender las solicitudes online de servicio técnico');

-- Asignar al admin
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.`slug` = 'admin' AND p.`slug` = 'servicio.solicitudes';

-- ─────────────────────────────────────────────
-- Verificación
-- ─────────────────────────────────────────────
-- SHOW COLUMNS FROM `pedidos_camiseta` LIKE 'comprobante_path';
-- SHOW TABLES LIKE 'solicitudes_servicio';
