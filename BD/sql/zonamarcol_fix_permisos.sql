-- ============================================================
--  zonamarcol_fix_permisos.sql
--  ZonaMarcol — DeskCod
-- ------------------------------------------------------------
--  PROBLEMA QUE RESUELVE
--  Los Controllers exigen permisos con slugs de grano fino
--  (categorias.crear, roles.editar, banners.ver, etc.) que
--  NUNCA se insertaron en la tabla `permissions`. El seed solo
--  creo el slug grueso `categorias.gestionar`.
--
--  Como el rol Administrador recibe permisos con:
--      INSERT INTO rol_permisos SELECT 1, id FROM permissions;
--  solo se le asignan los permisos que EXISTEN como fila. Por eso
--  ni el admin tiene `categorias.crear` -> Auth::require() -> 403.
--
--  Este script:
--   1. Inserta los 11 permisos faltantes que el codigo si exige.
--   2. Reasigna TODOS los permisos al rol Administrador.
--   3. (Opcional) Promueve un usuario concreto al rol Administrador.
--
--  IDEMPOTENTE: se puede ejecutar varias veces. INSERT IGNORE evita
--  duplicados sobre las claves unicas (permissions.slug y la PK
--  compuesta de rol_permisos).
--
--  IMPORTANTE — tras ejecutarlo, el usuario debe CERRAR SESION y
--  volver a entrar. Auth cachea los permisos en $_SESSION; el
--  refresco automatico (Auth::PERMISOS_TTL) tarda hasta 5 minutos.
-- ============================================================

USE `zonamarcol`;

START TRANSACTION;

-- ------------------------------------------------------------
-- BLOQUE 1 — Permisos faltantes
-- Cada fila es un slug que algun Controller pasa a Auth::require()
-- o Auth::can() pero que no estaba sembrado. modulo/descripcion
-- siguen la convencion del seed original (formato modulo.accion).
-- INSERT IGNORE: si el slug ya existe (uk_permissions_slug) la fila
-- se omite sin error, por eso el script es re-ejecutable.
-- ------------------------------------------------------------
INSERT IGNORE INTO `permissions` (`nombre`, `slug`, `modulo`, `descripcion`) VALUES
    ('Crear categorias',     'categorias.crear',    'categorias', 'Alta de categorias de producto'),
    ('Editar categorias',    'categorias.editar',   'categorias', 'Modificar y activar/desactivar categorias'),
    ('Eliminar categorias',  'categorias.eliminar', 'categorias', 'Baja de categorias sin productos activos'),
    ('Crear roles',          'roles.crear',         'roles',      'Alta de roles del sistema'),
    ('Editar roles',         'roles.editar',        'roles',      'Modificar roles y asignar permisos'),
    ('Eliminar roles',       'roles.eliminar',      'roles',      'Baja de roles no protegidos'),
    ('Ver banners',          'banners.ver',         'banners',    'Acceso al listado de banners de la tienda'),
    ('Gestionar banners',    'banners.gestionar',   'banners',    'Crear, editar y eliminar banners'),
    ('Ver zonas de envio',   'zonas.ver',           'zonas',      'Acceso al listado de zonas de envio'),
    ('Gestionar zonas',      'zonas.gestionar',     'zonas',      'Crear, editar y eliminar zonas de envio'),
    ('Eliminar ventas',      'ventas.eliminar',     'ventas',     'Baja de ventas registradas');

-- ------------------------------------------------------------
-- BLOQUE 2 — Asignar TODOS los permisos al rol Administrador
-- Se resuelve el rol por slug ('admin') en vez de hardcodear id=1,
-- por si el id difiere entre entornos. El CROSS JOIN genera el
-- producto rol x permisos; INSERT IGNORE descarta los pares que ya
-- existen en la PK (rol_id, permission_id). Asi quedan cubiertos
-- tanto los 11 nuevos como cualquier permiso previo.
-- ------------------------------------------------------------
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permission_id`)
SELECT r.`id`, p.`id`
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.`slug` = 'admin';

COMMIT;

-- ------------------------------------------------------------
-- BLOQUE 3 (OPCIONAL) — Promover un usuario al rol Administrador
-- Usar SOLO si el usuario que no puede crear categorias NO tiene
-- rol admin. El rol admin ya quedo con todos los permisos arriba,
-- asi que basta moverlo a ese rol. Descomenta y pon tu email/username.
-- ------------------------------------------------------------
-- UPDATE `users`
--    SET `rol_id` = (SELECT `id` FROM `roles` WHERE `slug` = 'admin' LIMIT 1)
--  WHERE `email` = 'TU_EMAIL_AQUI';

-- ------------------------------------------------------------
-- BLOQUE 4 — Verificacion (no modifica datos)
-- Ejecuta estas consultas para confirmar el resultado.
-- ------------------------------------------------------------
-- 4.1 — Total de permisos del rol admin (debe ser = total de permissions)
SELECT
    (SELECT COUNT(*) FROM `permissions`) AS total_permisos,
    (SELECT COUNT(*) FROM `rol_permisos` rp
        JOIN `roles` r ON r.`id` = rp.`rol_id`
        WHERE r.`slug` = 'admin')        AS permisos_admin;

-- 4.2 — Confirmar que los slugs criticos existen y estan asignados al admin
SELECT p.`slug`,
       IF(rp.`permission_id` IS NULL, 'FALTA', 'OK') AS estado_admin
FROM `permissions` p
LEFT JOIN `rol_permisos` rp
       ON rp.`permission_id` = p.`id`
      AND rp.`rol_id` = (SELECT `id` FROM `roles` WHERE `slug` = 'admin' LIMIT 1)
WHERE p.`slug` IN (
    'categorias.crear','categorias.editar','categorias.eliminar',
    'roles.crear','roles.editar','roles.eliminar',
    'banners.ver','banners.gestionar',
    'zonas.ver','zonas.gestionar','ventas.eliminar'
)
ORDER BY p.`slug`;

-- 4.3 — Ver el rol actual de cada usuario (para decidir si usar el BLOQUE 3)
SELECT u.`id`, u.`nombre`, u.`email`, u.`username`, r.`slug` AS rol
FROM `users` u
JOIN `roles` r ON r.`id` = u.`rol_id`
ORDER BY u.`id`;
// ------------------------------------------------------------