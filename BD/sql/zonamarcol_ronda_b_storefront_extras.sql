-- ============================================================
--  zonamarcol_ronda_b_storefront_extras.sql
--  ZonaMarcol — DeskCod
-- ------------------------------------------------------------
--  PROPÓSITO — Permisos adicionales y ajustes para cerrar Ronda B:
--
--   1. Permiso `camisetas.pedidos` para gestionar la bandeja de
--      pedidos online desde el admin (PedidosCamisetaController).
--      Asignado al rol admin.
--
--   Este archivo es complementario a:
--      - zonamarcol_full.sql                       (schema base)
--      - zonamarcol_fix_permisos.sql               (slugs faltantes)
--      - zonamarcol_ronda_b_pedidos_online.sql     (comprobante_path
--                                                   + solicitudes_servicio
--                                                   + servicio.solicitudes)
--
--   No hay tablas nuevas — el storefront del cliente
--   (misPedidosCamiseta / misSolicitudes / verPedidoCamiseta) usa
--   `findByCliente()` de los models existentes, sin schema nuevo.
--
--  IDEMPOTENTE: INSERT IGNORE en permissions y rol_permisos.
-- ============================================================

USE `zonamarcol`;

-- ─────────────────────────────────────────────
-- 1) Permiso camisetas.pedidos
-- ─────────────────────────────────────────────
INSERT IGNORE INTO `permissions` (`nombre`, `slug`, `modulo`, `descripcion`) VALUES
    ('Gestionar pedidos de camisetas', 'camisetas.pedidos', 'camisetas',
     'Ver y gestionar pedidos online de camisetas (workflow + comprobantes)');

-- Asignar al admin.
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.`slug` = 'admin'
  AND p.`slug` = 'camisetas.pedidos';

-- ─────────────────────────────────────────────
-- Verificación
-- ─────────────────────────────────────────────
-- SELECT * FROM permissions WHERE slug = 'camisetas.pedidos';
-- SELECT r.nombre AS rol, p.nombre AS permiso
-- FROM rol_permisos rp
-- JOIN roles r       ON r.id = rp.rol_id
-- JOIN permissions p ON p.id = rp.permission_id
-- WHERE p.slug = 'camisetas.pedidos';
