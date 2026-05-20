-- ============================================================
--  ZONAMARCOL — Schema completo + SPs + Seeds
--  Generado: 2026-05-19  •  Motor: MariaDB / MySQL 8+
--  Stack: PHP 8.2 puro MVC, XAMPP local
-- ============================================================
--  USO:
--    mysql -u root -p < zonamarcol_full.sql
--    o desde phpMyAdmin: Importar este archivo.
--
--  IDEMPOTENTE: puede ejecutarse las veces que sea (DROP DB + CREATE).
--  ATENCIÓN: BORRA la BD existente. No usar en producción sin backup.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

DROP DATABASE IF EXISTS `zonamarcol`;
CREATE DATABASE `zonamarcol`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE       utf8mb4_unicode_ci;

USE `zonamarcol`;

-- ============================================================
-- SECCIÓN 1 — RBAC: roles, permissions, rol_permisos, users
-- ============================================================

CREATE TABLE `roles` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(60)    NOT NULL,
    `slug`        VARCHAR(60)    NOT NULL,
    `descripcion` VARCHAR(255)       NULL,
    `activo`      TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_slug` (`slug`)
) ENGINE=InnoDB;

CREATE TABLE `permissions` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(80)    NOT NULL,
    `slug`        VARCHAR(80)    NOT NULL,
    `modulo`      VARCHAR(40)    NOT NULL,
    `descripcion` VARCHAR(255)       NULL,
    `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permissions_slug` (`slug`),
    KEY `idx_permissions_modulo` (`modulo`)
) ENGINE=InnoDB;

CREATE TABLE `rol_permisos` (
    `rol_id`        INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`rol_id`, `permission_id`),
    KEY `idx_rolperm_perm` (`permission_id`),
    CONSTRAINT `fk_rolperm_rol`  FOREIGN KEY (`rol_id`)        REFERENCES `roles`       (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rolperm_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `users` (
    `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre`           VARCHAR(120)  NOT NULL,
    `username`         VARCHAR(60)       NULL,
    `email`            VARCHAR(120)  NOT NULL,
    `password`         VARCHAR(255)  NOT NULL,
    `rol_id`           INT UNSIGNED  NOT NULL,
    `activo`           TINYINT(1)    NOT NULL DEFAULT 1,
    `foto`             VARCHAR(255)      NULL,
    `telefono`         VARCHAR(30)       NULL,
    `session_token`    VARCHAR(255)      NULL,
    `tour_completado`  TINYINT(1)    NOT NULL DEFAULT 0,
    `deleted_at`       TIMESTAMP         NULL DEFAULT NULL,
    `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email`    (`email`),
    UNIQUE KEY `uk_users_username` (`username`),
    KEY `idx_users_rol`     (`rol_id`),
    KEY `idx_users_activo`  (`activo`),
    CONSTRAINT `fk_users_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `rate_limits` (
    `ip`               VARCHAR(45)  NOT NULL,
    `intentos`         INT UNSIGNED NOT NULL DEFAULT 0,
    `ultima_falla`     TIMESTAMP        NULL,
    `bloqueado_hasta`  TIMESTAMP        NULL,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ip`)
) ENGINE=InnoDB;

-- ============================================================
-- SECCIÓN 2 — CATÁLOGO: categorías, productos, variantes
-- ============================================================

CREATE TABLE `categorias` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(80)  NOT NULL,
    `descripcion` VARCHAR(255)     NULL,
    `activo`      TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_categorias_activo` (`activo`)
) ENGINE=InnoDB;

CREATE TABLE `productos` (
    `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `categoria_id`     INT UNSIGNED   NOT NULL,
    `nombre`           VARCHAR(150)   NOT NULL,
    `descripcion`      TEXT               NULL,
    `precio_base`      DECIMAL(10,2)      NULL,
    `tiene_variantes`  TINYINT(1)     NOT NULL DEFAULT 0,
    `stock`            INT            NOT NULL DEFAULT 0,
    `codigo_barras`    VARCHAR(60)        NULL,
    `image_url`        VARCHAR(255)       NULL,
    `visible_tienda`   TINYINT(1)     NOT NULL DEFAULT 1,
    `activo`           TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_productos_categoria` (`categoria_id`),
    KEY `idx_productos_activo`    (`activo`),
    KEY `idx_productos_visible`   (`visible_tienda`),
    KEY `idx_productos_barras`    (`codigo_barras`),
    CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `producto_variantes` (
    `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `producto_id`    INT UNSIGNED   NOT NULL,
    `nombre`         VARCHAR(100)   NOT NULL,
    `precio`         DECIMAL(10,2)      NULL,
    `stock`          INT            NOT NULL DEFAULT 0,
    `codigo_barras`  VARCHAR(60)        NULL,
    `image_url`      VARCHAR(255)       NULL,
    `orden`          INT            NOT NULL DEFAULT 0,
    `activo`         TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_variantes_producto` (`producto_id`),
    KEY `idx_variantes_activo`   (`activo`),
    KEY `idx_variantes_barras`   (`codigo_barras`),
    CONSTRAINT `fk_variantes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `descuentos` (
    `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `nombre`        VARCHAR(120)   NOT NULL,
    `porcentaje`    DECIMAL(5,2)   NOT NULL,
    `aplica_a`      ENUM('todo','categoria') NOT NULL DEFAULT 'todo',
    `categoria_id`  INT UNSIGNED       NULL,
    `fecha_inicio`  DATE           NOT NULL,
    `fecha_fin`     DATE           NOT NULL,
    `activo`        TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_descuentos_activo` (`activo`),
    KEY `idx_descuentos_cat`    (`categoria_id`),
    CONSTRAINT `fk_descuentos_cat` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SECCIÓN 3 — CLIENTES & FAVORITOS
-- ============================================================

CREATE TABLE `clientes` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(120)  NOT NULL,
    `email`       VARCHAR(120)      NULL,
    `telefono`    VARCHAR(30)       NULL,
    `direccion`   VARCHAR(255)      NULL,
    `password`    VARCHAR(255)      NULL,
    `activo`      TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_clientes_email` (`email`),
    KEY `idx_clientes_activo`      (`activo`),
    KEY `idx_clientes_telefono`    (`telefono`)
) ENGINE=InnoDB;

CREATE TABLE `favoritos` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cliente_id`   INT UNSIGNED NOT NULL,
    `producto_id`  INT UNSIGNED NOT NULL,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_favoritos` (`cliente_id`, `producto_id`),
    KEY `idx_favoritos_prod`  (`producto_id`),
    CONSTRAINT `fk_fav_cliente`  FOREIGN KEY (`cliente_id`)  REFERENCES `clientes`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fav_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SECCIÓN 4 — VENTAS & FACTURACIÓN SAR
-- ============================================================

CREATE TABLE `caja_sesiones` (
    `id`                    INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`               INT UNSIGNED   NOT NULL,
    `monto_apertura`        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `monto_cierre`          DECIMAL(10,2)      NULL,
    `monto_sistema`         DECIMAL(10,2)      NULL,
    `total_ventas`          DECIMAL(10,2)      NULL,
    `total_efectivo`        DECIMAL(10,2)      NULL,
    `total_tarjeta`         DECIMAL(10,2)      NULL,
    `total_transferencia`   DECIMAL(10,2)      NULL,
    `total_anuladas`        DECIMAL(10,2)      NULL,
    `nota`                  VARCHAR(255)       NULL,
    `nota_cierre`           VARCHAR(255)       NULL,
    `estado`                ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
    `abierta_at`            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `cerrada_at`            TIMESTAMP          NULL,
    PRIMARY KEY (`id`),
    KEY `idx_caja_user`   (`user_id`),
    KEY `idx_caja_estado` (`estado`),
    CONSTRAINT `fk_caja_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `ventas` (
    `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `cliente_id`      INT UNSIGNED       NULL,
    `user_id`         INT UNSIGNED   NOT NULL,
    `caja_sesion_id`  INT UNSIGNED       NULL,
    `metodo_pago`     ENUM('Efectivo','Tarjeta','Transferencia') NOT NULL,
    `subtotal`        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `descuento`       DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `total`           DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `monto_recibido`  DECIMAL(10,2)      NULL,
    `cambio`          DECIMAL(10,2)      NULL,
    `factura_numero`  VARCHAR(40)        NULL,
    `cai`             VARCHAR(255)       NULL,
    `nota`            VARCHAR(255)       NULL,
    `anulada`         TINYINT(1)     NOT NULL DEFAULT 0,
    `motivo_anulacion` VARCHAR(255)      NULL,
    `anulada_por`     INT UNSIGNED       NULL,
    `anulada_at`      TIMESTAMP          NULL,
    `created_at`      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ventas_cliente`  (`cliente_id`),
    KEY `idx_ventas_user`     (`user_id`),
    KEY `idx_ventas_caja`     (`caja_sesion_id`),
    KEY `idx_ventas_anulada`  (`anulada`),
    KEY `idx_ventas_fecha`    (`created_at`),
    CONSTRAINT `fk_ventas_cliente` FOREIGN KEY (`cliente_id`)     REFERENCES `clientes`      (`id`),
    CONSTRAINT `fk_ventas_user`    FOREIGN KEY (`user_id`)        REFERENCES `users`         (`id`),
    CONSTRAINT `fk_ventas_caja`    FOREIGN KEY (`caja_sesion_id`) REFERENCES `caja_sesiones` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `venta_detalle` (
    `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `venta_id`         INT UNSIGNED   NOT NULL,
    `producto_id`      INT UNSIGNED       NULL,
    `variante_id`      INT UNSIGNED       NULL,
    `nombre_producto`  VARCHAR(255)   NOT NULL,
    `precio_unit`      DECIMAL(10,2)  NOT NULL,
    `cantidad`         INT            NOT NULL,
    `subtotal`         DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_vd_venta`  (`venta_id`),
    KEY `idx_vd_prod`   (`producto_id`),
    CONSTRAINT `fk_vd_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `facturacion_config` (
    `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `rtn`              VARCHAR(20)        NULL,
    `cai`              VARCHAR(255)       NULL,
    `rango_desde`      VARCHAR(40)        NULL,
    `rango_hasta`      VARCHAR(40)        NULL,
    `fecha_limite`     DATE               NULL,
    `establecimiento`  VARCHAR(10)        NULL,
    `punto_emision`    VARCHAR(10)        NULL,
    `nombre_fiscal`    VARCHAR(150)       NULL,
    `direccion_fiscal` VARCHAR(255)       NULL,
    `correlativo`      INT UNSIGNED   NOT NULL DEFAULT 0,
    `updated_at`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ============================================================
-- SECCIÓN 5 — PEDIDOS TIENDA EN LÍNEA
-- ============================================================

CREATE TABLE `zonas_envio` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(100)   NOT NULL,
    `costo`       DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `activo`      TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_zonas_activo` (`activo`)
) ENGINE=InnoDB;

CREATE TABLE `pedidos` (
    `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `codigo`          VARCHAR(20)    NOT NULL,
    `cliente_id`      INT UNSIGNED       NULL,
    `wa_numero`       VARCHAR(30)        NULL,
    `tipo_entrega`    ENUM('Retiro','Envio') NOT NULL DEFAULT 'Retiro',
    `direccion_envio` VARCHAR(255)       NULL,
    `zona_id`         INT UNSIGNED       NULL,
    `estado`          ENUM('Pendiente','Pagado','En preparacion','Listo','En camino','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
    `pagado`          TINYINT(1)     NOT NULL DEFAULT 0,
    `pagado_por`      INT UNSIGNED       NULL,
    `pagado_at`       TIMESTAMP          NULL,
    `metodo_pago`     ENUM('Efectivo','Tarjeta','Transferencia') NOT NULL DEFAULT 'Transferencia',
    `subtotal`        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `costo_envio`     DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `total`           DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `nota`            VARCHAR(500)       NULL,
    `created_at`      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pedidos_codigo` (`codigo`),
    KEY `idx_pedidos_cliente`  (`cliente_id`),
    KEY `idx_pedidos_estado`   (`estado`),
    KEY `idx_pedidos_zona`     (`zona_id`),
    KEY `idx_pedidos_fecha`    (`created_at`),
    CONSTRAINT `fk_ped_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`    (`id`),
    CONSTRAINT `fk_ped_zona`    FOREIGN KEY (`zona_id`)    REFERENCES `zonas_envio` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `pedido_detalle` (
    `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `pedido_id`        INT UNSIGNED   NOT NULL,
    `producto_id`      INT UNSIGNED       NULL,
    `variante_id`      INT UNSIGNED       NULL,
    `nombre_producto`  VARCHAR(255)   NOT NULL,
    `precio_unit`      DECIMAL(10,2)  NOT NULL,
    `cantidad`         INT            NOT NULL,
    `subtotal`         DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_pd_pedido` (`pedido_id`),
    CONSTRAINT `fk_pd_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `pedido_historial` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `pedido_id`        INT UNSIGNED NOT NULL,
    `estado_anterior`  VARCHAR(40)      NULL,
    `estado_nuevo`     VARCHAR(40)  NOT NULL,
    `user_id`          INT UNSIGNED     NULL,
    `nota`             VARCHAR(255)     NULL,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ph_pedido` (`pedido_id`),
    KEY `idx_ph_fecha`  (`created_at`),
    CONSTRAINT `fk_ph_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ph_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`)
) ENGINE=InnoDB;

-- ============================================================
-- SECCIÓN 6 — BANNERS, NOTIFICACIONES, SERVICIOS (catálogo)
-- ============================================================

CREATE TABLE `banners` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `titulo`      VARCHAR(150)      NULL,
    `imagen_url`  VARCHAR(255)  NOT NULL,
    `enlace`      VARCHAR(255)      NULL,
    `orden`       INT           NOT NULL DEFAULT 0,
    `activo`      TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_banners_activo` (`activo`),
    KEY `idx_banners_orden`  (`orden`)
) ENGINE=InnoDB;

CREATE TABLE `notificaciones` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `tipo`       VARCHAR(40)   NOT NULL,
    `titulo`     VARCHAR(150)  NOT NULL,
    `mensaje`    VARCHAR(500)  NOT NULL,
    `url`        VARCHAR(255)      NULL,
    `leida`      TINYINT(1)    NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notif_leida` (`leida`),
    KEY `idx_notif_tipo`  (`tipo`),
    KEY `idx_notif_fecha` (`created_at`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- SECCIÓN 7 — SERVICIO TÉCNICO (mantenimiento de consolas/PC)
-- ============================================================

CREATE TABLE `servicios_catalogo` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(120)   NOT NULL,
    `descripcion` VARCHAR(500)       NULL,
    `precio`      DECIMAL(10,2)  NOT NULL,
    `categoria`   ENUM('limpieza','reparacion','diagnostico','otro') NOT NULL DEFAULT 'limpieza',
    `activo`      TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_scat_activo` (`activo`)
) ENGINE=InnoDB;

CREATE TABLE `ordenes_servicio` (
    `id`                     INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `codigo`                 VARCHAR(20)    NOT NULL,
    `cliente_id`             INT UNSIGNED   NOT NULL,
    `user_recepcion_id`      INT UNSIGNED   NOT NULL,
    `tecnico_id`             INT UNSIGNED       NULL,
    `equipo_descripcion`     VARCHAR(255)   NOT NULL,
    `serial`                 VARCHAR(120)       NULL,
    `accesorios_entregados`  VARCHAR(255)       NULL,
    `diagnostico_inicial`    TEXT               NULL,
    `estado`                 ENUM('Recibido','Diagnostico','Esperando aprobacion','En reparacion','Listo','Entregado','Cancelado') NOT NULL DEFAULT 'Recibido',
    `total_actual`           DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `total_pagado`           DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `saldo`                  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `fecha_recepcion`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_entrega`          TIMESTAMP          NULL,
    `fecha_garantia_hasta`   DATE               NULL,
    `motivo_cancelacion`     VARCHAR(255)       NULL,
    `observaciones`          TEXT               NULL,
    `created_at`             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_os_codigo` (`codigo`),
    KEY `idx_os_cliente`  (`cliente_id`),
    KEY `idx_os_tecnico`  (`tecnico_id`),
    KEY `idx_os_estado`   (`estado`),
    KEY `idx_os_fecha`    (`fecha_recepcion`),
    CONSTRAINT `fk_os_cliente`   FOREIGN KEY (`cliente_id`)        REFERENCES `clientes` (`id`),
    CONSTRAINT `fk_os_recepcion` FOREIGN KEY (`user_recepcion_id`) REFERENCES `users`    (`id`),
    CONSTRAINT `fk_os_tecnico`   FOREIGN KEY (`tecnico_id`)        REFERENCES `users`    (`id`)
) ENGINE=InnoDB;

CREATE TABLE `orden_servicio_items` (
    `id`                    INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `orden_id`              INT UNSIGNED   NOT NULL,
    `tipo`                  ENUM('servicio_catalogo','repuesto_libre','mano_obra_adicional') NOT NULL,
    `servicio_catalogo_id`  INT UNSIGNED       NULL,
    `descripcion`           VARCHAR(255)   NOT NULL,
    `cantidad`              INT            NOT NULL DEFAULT 1,
    `precio_unitario`       DECIMAL(10,2)  NOT NULL,
    `subtotal`              DECIMAL(10,2)  NOT NULL,
    `aprobado_cliente`      TINYINT(1)     NOT NULL DEFAULT 0,
    `dias_garantia`         INT            NOT NULL DEFAULT 30,
    `agregado_por`          INT UNSIGNED   NOT NULL,
    `agregado_en`           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_osi_orden`   (`orden_id`),
    KEY `idx_osi_tipo`    (`tipo`),
    KEY `idx_osi_catalog` (`servicio_catalogo_id`),
    CONSTRAINT `fk_osi_orden`   FOREIGN KEY (`orden_id`)             REFERENCES `ordenes_servicio`   (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_osi_catalog` FOREIGN KEY (`servicio_catalogo_id`) REFERENCES `servicios_catalogo` (`id`),
    CONSTRAINT `fk_osi_usr`     FOREIGN KEY (`agregado_por`)         REFERENCES `users`              (`id`)
) ENGINE=InnoDB;

CREATE TABLE `orden_servicio_pagos` (
    `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `orden_id`        INT UNSIGNED   NOT NULL,
    `tipo`            ENUM('anticipo','saldo','abono') NOT NULL,
    `monto`           DECIMAL(10,2)  NOT NULL,
    `metodo`          ENUM('Efectivo','Tarjeta','Transferencia') NOT NULL,
    `caja_sesion_id`  INT UNSIGNED       NULL,
    `recibo_numero`   VARCHAR(40)        NULL,
    `user_id`         INT UNSIGNED   NOT NULL,
    `fecha`           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_osp_orden`  (`orden_id`),
    KEY `idx_osp_fecha`  (`fecha`),
    CONSTRAINT `fk_osp_orden` FOREIGN KEY (`orden_id`)       REFERENCES `ordenes_servicio` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_osp_caja`  FOREIGN KEY (`caja_sesion_id`) REFERENCES `caja_sesiones`    (`id`),
    CONSTRAINT `fk_osp_user`  FOREIGN KEY (`user_id`)        REFERENCES `users`            (`id`)
) ENGINE=InnoDB;

CREATE TABLE `servicio_historial` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `orden_id`         INT UNSIGNED NOT NULL,
    `estado_anterior`  VARCHAR(40)      NULL,
    `estado_nuevo`     VARCHAR(40)  NOT NULL,
    `motivo`           VARCHAR(255)     NULL,
    `user_id`          INT UNSIGNED NOT NULL,
    `fecha`            TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sh_orden` (`orden_id`),
    KEY `idx_sh_fecha` (`fecha`),
    CONSTRAINT `fk_sh_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_servicio` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sh_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`            (`id`)
) ENGINE=InnoDB;

-- ============================================================
-- SECCIÓN 8 — CAMISETAS DEPORTIVAS (broker / pedidos por temporada)
-- ─────────────────────────────────────────────────
-- Flujo configurador:
--   Torneo (Liga o Competición) → Equipo → Versión (H/M/N) →
--   Temporada → Competición (parche) → Equipación → Talla → Personalización
-- ─────────────────────────────────────────────────
-- Catálogos FIJOS (no CRUD frecuente):
--   - versiones: hombre, mujer, infantil  (ENUM en equipaciones)
--   - tallas_hombre, tallas_mujer, tallas_infantil  (3 tablas)
--   - tipos_equipacion: Local, Visitante, Tercera, Portero, etc.
-- Catálogos GESTIONADOS desde panel admin:
--   - torneos, equipos, temporadas, competiciones, equipaciones
-- ============================================================

-- ─────────────────────────────────────────────────
-- TORNEOS — Ligas de clubes + Competiciones de selecciones
-- Unificadas en una sola entidad. Cada una tiene su LOGO
-- que se renderiza como botón desplegable en el configurador.
-- ─────────────────────────────────────────────────
CREATE TABLE `torneos` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre`     VARCHAR(120)  NOT NULL,
    `tipo`       ENUM('liga_club','seleccion','copa_continental','otro') NOT NULL DEFAULT 'liga_club',
    `pais`       VARCHAR(80)       NULL,
    `logo_path`  VARCHAR(255)  NOT NULL,
    `orden`      INT           NOT NULL DEFAULT 0,
    `activo`     TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_torneos_tipo`   (`tipo`),
    KEY `idx_torneos_activo` (`activo`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- EQUIPOS — clubes o selecciones según el torneo padre
-- ─────────────────────────────────────────────────
CREATE TABLE `equipos` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `torneo_id`   INT UNSIGNED  NOT NULL,
    `nombre`      VARCHAR(120)  NOT NULL,
    `escudo_path` VARCHAR(255)  NOT NULL,
    `orden`       INT           NOT NULL DEFAULT 0,
    `activo`      TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_equipos_torneo` (`torneo_id`),
    KEY `idx_equipos_activo` (`activo`),
    CONSTRAINT `fk_eq_torneo` FOREIGN KEY (`torneo_id`) REFERENCES `torneos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- TEMPORADAS — globales (24/25, 25/26, Mundial 2026)
-- ─────────────────────────────────────────────────
CREATE TABLE `temporadas` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(40)   NOT NULL,
    `anio_inicio` SMALLINT      NOT NULL,
    `anio_fin`    SMALLINT      NOT NULL,
    `activo`      TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_temp_nombre` (`nombre`),
    KEY `idx_temp_activo`       (`activo`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- TIPOS DE EQUIPACIÓN — Local / Visitante / Tercera / Portero
-- ─────────────────────────────────────────────────
CREATE TABLE `tipos_equipacion` (
    `id`     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(40)   NOT NULL,
    `orden`  INT           NOT NULL DEFAULT 0,
    `activo` TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tipoeq_nombre` (`nombre`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- TALLAS — 3 tablas separadas (decisión del usuario)
-- ─────────────────────────────────────────────────
CREATE TABLE `tallas_hombre` (
    `id`     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(10)   NOT NULL,
    `orden`  INT           NOT NULL DEFAULT 0,
    `activo` TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tallasH_nombre` (`nombre`)
) ENGINE=InnoDB;

CREATE TABLE `tallas_mujer` (
    `id`     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(10)   NOT NULL,
    `orden`  INT           NOT NULL DEFAULT 0,
    `activo` TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tallasM_nombre` (`nombre`)
) ENGINE=InnoDB;

CREATE TABLE `tallas_infantil` (
    `id`     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(10)   NOT NULL,
    `orden`  INT           NOT NULL DEFAULT 0,
    `activo` TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tallasI_nombre` (`nombre`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- COMPETICIONES — catálogo de PARCHES con logo y nombre
-- Mundial, Champions, LaLiga, Premier, Copa America, Eurocopa, etc.
-- ─────────────────────────────────────────────────
CREATE TABLE `competiciones` (
    `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `nombre`        VARCHAR(120)   NOT NULL,
    `parche_path`   VARCHAR(255)   NOT NULL,
    `precio_extra`  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `activo`        TINYINT(1)     NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_comp_activo` (`activo`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- EQUIPO ↔ COMPETICIÓN (N:M)
-- Lista qué competiciones juega cada equipo.
-- En el configurador: al elegir equipo se muestran SUS competiciones.
-- ─────────────────────────────────────────────────
CREATE TABLE `equipo_competicion` (
    `equipo_id`       INT UNSIGNED NOT NULL,
    `competicion_id`  INT UNSIGNED NOT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`equipo_id`, `competicion_id`),
    KEY `idx_ec_comp` (`competicion_id`),
    CONSTRAINT `fk_ec_equipo` FOREIGN KEY (`equipo_id`)      REFERENCES `equipos`       (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ec_comp`   FOREIGN KEY (`competicion_id`) REFERENCES `competiciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- EQUIPACIONES — Real Madrid 24/25 Local Hombre, Mujer o Infantil
-- Llave única: (equipo, temporada, tipo, version)
-- ─────────────────────────────────────────────────
CREATE TABLE `equipaciones` (
    `id`                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `equipo_id`          INT UNSIGNED   NOT NULL,
    `temporada_id`       INT UNSIGNED   NOT NULL,
    `tipo_equipacion_id` INT UNSIGNED   NOT NULL,
    `version`            ENUM('hombre','mujer','infantil') NOT NULL,
    `imagen_path`        VARCHAR(255)   NOT NULL,
    `precio_base`        DECIMAL(10,2)  NOT NULL,
    `activo`             TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_equipaciones` (`equipo_id`, `temporada_id`, `tipo_equipacion_id`, `version`),
    KEY `idx_eqp_equipo`    (`equipo_id`),
    KEY `idx_eqp_temporada` (`temporada_id`),
    KEY `idx_eqp_tipo`      (`tipo_equipacion_id`),
    KEY `idx_eqp_version`   (`version`),
    KEY `idx_eqp_activo`    (`activo`),
    CONSTRAINT `fk_eqp_equipo`    FOREIGN KEY (`equipo_id`)          REFERENCES `equipos`           (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_eqp_temporada` FOREIGN KEY (`temporada_id`)       REFERENCES `temporadas`        (`id`),
    CONSTRAINT `fk_eqp_tipo`      FOREIGN KEY (`tipo_equipacion_id`) REFERENCES `tipos_equipacion` (`id`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- PRECIOS EXTRAS (personalización)
-- ─────────────────────────────────────────────────
CREATE TABLE `precios_extras_camisa` (
    `id`         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `concepto`   ENUM('nombre','numero','nombre_y_numero','parche') NOT NULL,
    `precio`     DECIMAL(10,2)  NOT NULL,
    `activo`     TINYINT(1)     NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_precios_extras` (`concepto`)
) ENGINE=InnoDB;

CREATE TABLE `config_camisetas` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `anticipo_pct`    INT           NOT NULL DEFAULT 50,
    `proveedor`       VARCHAR(120)      NULL,
    `correo_proveedor` VARCHAR(120)     NULL,
    `nota_export`     TEXT              NULL,
    `updated_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `config_temporadas` (
    `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `temporada_id`      INT UNSIGNED  NOT NULL,
    `fecha_inicio`      DATE          NOT NULL,
    `fecha_fin`         DATE          NOT NULL,
    `abierta`           TINYINT(1)    NOT NULL DEFAULT 1,
    `lote_exportado_at` TIMESTAMP         NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ct_temporada` (`temporada_id`),
    CONSTRAINT `fk_ct_temporada` FOREIGN KEY (`temporada_id`) REFERENCES `temporadas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `pedidos_camiseta` (
    `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `codigo`            VARCHAR(20)    NOT NULL,
    `cliente_id`        INT UNSIGNED   NOT NULL,
    `temporada_id`      INT UNSIGNED   NOT NULL,
    `estado`            ENUM('Pendiente_pago','Confirmado','En_proveedor','Recibido','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente_pago',
    `subtotal`          DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `total`             DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `anticipo_pagado`   DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `saldo`             DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `exportado_at`      TIMESTAMP          NULL,
    `nota`              VARCHAR(500)       NULL,
    `created_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pc_codigo` (`codigo`),
    KEY `idx_pc_cliente`   (`cliente_id`),
    KEY `idx_pc_temporada` (`temporada_id`),
    KEY `idx_pc_estado`    (`estado`),
    CONSTRAINT `fk_pc_cliente`   FOREIGN KEY (`cliente_id`)   REFERENCES `clientes`   (`id`),
    CONSTRAINT `fk_pc_temporada` FOREIGN KEY (`temporada_id`) REFERENCES `temporadas` (`id`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────
-- DETALLE DE PEDIDO — talla en 3 columnas (una se llena según versión)
-- Validación de exactamente UNA talla = trigger (más abajo).
-- ─────────────────────────────────────────────────
CREATE TABLE `pedido_camiseta_detalle` (
    `id`                    INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `pedido_id`             INT UNSIGNED   NOT NULL,
    `equipacion_id`         INT UNSIGNED   NOT NULL,
    `talla_hombre_id`       INT UNSIGNED       NULL,
    `talla_mujer_id`        INT UNSIGNED       NULL,
    `talla_infantil_id`     INT UNSIGNED       NULL,
    `nombre_personalizado`  VARCHAR(40)        NULL,
    `numero_personalizado`  VARCHAR(5)         NULL,
    `competicion_id`        INT UNSIGNED       NULL,
    `precio_unitario`       DECIMAL(10,2)  NOT NULL,
    `precio_extras`         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `cantidad`              INT            NOT NULL DEFAULT 1,
    `subtotal`              DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_pcd_pedido`      (`pedido_id`),
    KEY `idx_pcd_equipacion`  (`equipacion_id`),
    KEY `idx_pcd_tallaH`      (`talla_hombre_id`),
    KEY `idx_pcd_tallaM`      (`talla_mujer_id`),
    KEY `idx_pcd_tallaI`      (`talla_infantil_id`),
    KEY `idx_pcd_competicion` (`competicion_id`),
    CONSTRAINT `fk_pcd_pedido`      FOREIGN KEY (`pedido_id`)         REFERENCES `pedidos_camiseta` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pcd_equipacion`  FOREIGN KEY (`equipacion_id`)     REFERENCES `equipaciones`     (`id`),
    CONSTRAINT `fk_pcd_tallaH`      FOREIGN KEY (`talla_hombre_id`)   REFERENCES `tallas_hombre`    (`id`),
    CONSTRAINT `fk_pcd_tallaM`      FOREIGN KEY (`talla_mujer_id`)    REFERENCES `tallas_mujer`     (`id`),
    CONSTRAINT `fk_pcd_tallaI`      FOREIGN KEY (`talla_infantil_id`) REFERENCES `tallas_infantil`  (`id`),
    CONSTRAINT `fk_pcd_competicion` FOREIGN KEY (`competicion_id`)    REFERENCES `competiciones`    (`id`),
    -- Validación: exactamente UNA talla debe estar seteada según la versión
    CONSTRAINT `chk_pcd_una_talla` CHECK (
        (CASE WHEN talla_hombre_id   IS NOT NULL THEN 1 ELSE 0 END) +
        (CASE WHEN talla_mujer_id    IS NOT NULL THEN 1 ELSE 0 END) +
        (CASE WHEN talla_infantil_id IS NOT NULL THEN 1 ELSE 0 END) = 1
    )
) ENGINE=InnoDB;


-- ============================================================
-- ============================================================
--   STORED PROCEDURES
-- ============================================================
-- ============================================================

DELIMITER $$

-- ─────────────────────────────────────────────────
-- ROLES
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_roles_findAll$$
CREATE PROCEDURE sp_roles_findAll()
BEGIN
    SELECT id, nombre, slug, descripcion, activo
    FROM roles
    WHERE activo = 1
    ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_roles_findById$$
CREATE PROCEDURE sp_roles_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, slug, descripcion, activo
    FROM roles WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_roles_findBySlug$$
CREATE PROCEDURE sp_roles_findBySlug(IN p_slug VARCHAR(60))
BEGIN
    SELECT id, nombre, slug, descripcion, activo
    FROM roles WHERE slug = p_slug LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_roles_insert$$
CREATE PROCEDURE sp_roles_insert(
    IN p_nombre      VARCHAR(60),
    IN p_slug        VARCHAR(60),
    IN p_descripcion VARCHAR(255)
)
BEGIN
    INSERT INTO roles (nombre, slug, descripcion, activo)
    VALUES (p_nombre, p_slug, p_descripcion, 1);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_roles_update$$
CREATE PROCEDURE sp_roles_update(
    IN p_id          INT,
    IN p_nombre      VARCHAR(60),
    IN p_slug        VARCHAR(60),
    IN p_descripcion VARCHAR(255)
)
BEGIN
    UPDATE roles
    SET nombre = p_nombre, slug = p_slug, descripcion = p_descripcion
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_roles_delete$$
CREATE PROCEDURE sp_roles_delete(IN p_id INT)
BEGIN
    DELETE FROM roles WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_roles_count$$
CREATE PROCEDURE sp_roles_count()
BEGIN
    SELECT COUNT(*) AS total FROM roles WHERE activo = 1;
END$$

DROP PROCEDURE IF EXISTS sp_roles_slugExists$$
CREATE PROCEDURE sp_roles_slugExists(IN p_slug VARCHAR(60), IN p_exclude_id INT)
BEGIN
    SELECT IF(COUNT(*) > 0, 1, 0) AS existe
    FROM roles WHERE slug = p_slug AND id <> p_exclude_id;
END$$

DROP PROCEDURE IF EXISTS sp_roles_hasUsers$$
CREATE PROCEDURE sp_roles_hasUsers(IN p_rol_id INT)
BEGIN
    SELECT COUNT(*) AS total FROM users WHERE rol_id = p_rol_id AND deleted_at IS NULL;
END$$

DROP PROCEDURE IF EXISTS sp_roles_getPermissions$$
CREATE PROCEDURE sp_roles_getPermissions(IN p_rol_id INT)
BEGIN
    SELECT p.id, p.nombre, p.slug, p.modulo, p.descripcion
    FROM permissions p
    INNER JOIN rol_permisos rp ON rp.permission_id = p.id
    WHERE rp.rol_id = p_rol_id
    ORDER BY p.modulo, p.slug;
END$$

DROP PROCEDURE IF EXISTS sp_roles_assignPermission$$
CREATE PROCEDURE sp_roles_assignPermission(IN p_rol_id INT, IN p_permission_id INT)
BEGIN
    INSERT IGNORE INTO rol_permisos (rol_id, permission_id) VALUES (p_rol_id, p_permission_id);
END$$

DROP PROCEDURE IF EXISTS sp_roles_revokePermission$$
CREATE PROCEDURE sp_roles_revokePermission(IN p_rol_id INT, IN p_permission_id INT)
BEGIN
    DELETE FROM rol_permisos WHERE rol_id = p_rol_id AND permission_id = p_permission_id;
END$$

DROP PROCEDURE IF EXISTS sp_roles_revokeAllPermissions$$
CREATE PROCEDURE sp_roles_revokeAllPermissions(IN p_rol_id INT)
BEGIN
    DELETE FROM rol_permisos WHERE rol_id = p_rol_id;
END$$

-- ─────────────────────────────────────────────────
-- PERMISSIONS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_permissions_findAll$$
CREATE PROCEDURE sp_permissions_findAll()
BEGIN
    SELECT id, nombre, slug, modulo, descripcion
    FROM permissions ORDER BY modulo, slug;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_findById$$
CREATE PROCEDURE sp_permissions_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, slug, modulo, descripcion
    FROM permissions WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_findByModule$$
CREATE PROCEDURE sp_permissions_findByModule(IN p_modulo VARCHAR(40))
BEGIN
    SELECT id, nombre, slug, modulo, descripcion
    FROM permissions WHERE modulo = p_modulo ORDER BY slug;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_getModules$$
CREATE PROCEDURE sp_permissions_getModules()
BEGIN
    SELECT DISTINCT modulo FROM permissions ORDER BY modulo;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_count$$
CREATE PROCEDURE sp_permissions_count()
BEGIN
    SELECT COUNT(*) AS total FROM permissions;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_slugExists$$
CREATE PROCEDURE sp_permissions_slugExists(IN p_slug VARCHAR(80), IN p_exclude_id INT)
BEGIN
    SELECT IF(COUNT(*) > 0, 1, 0) AS existe
    FROM permissions WHERE slug = p_slug AND id <> p_exclude_id;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_isAssigned$$
CREATE PROCEDURE sp_permissions_isAssigned(IN p_id INT)
BEGIN
    SELECT COUNT(*) AS total FROM rol_permisos WHERE permission_id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_insert$$
CREATE PROCEDURE sp_permissions_insert(
    IN p_nombre      VARCHAR(80),
    IN p_slug        VARCHAR(80),
    IN p_modulo      VARCHAR(40),
    IN p_descripcion VARCHAR(255)
)
BEGIN
    INSERT INTO permissions (nombre, slug, modulo, descripcion)
    VALUES (p_nombre, p_slug, p_modulo, p_descripcion);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_update$$
CREATE PROCEDURE sp_permissions_update(
    IN p_id          INT,
    IN p_nombre      VARCHAR(80),
    IN p_slug        VARCHAR(80),
    IN p_modulo      VARCHAR(40),
    IN p_descripcion VARCHAR(255)
)
BEGIN
    UPDATE permissions
    SET nombre = p_nombre, slug = p_slug, modulo = p_modulo, descripcion = p_descripcion
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_permissions_delete$$
CREATE PROCEDURE sp_permissions_delete(IN p_id INT)
BEGIN
    DELETE FROM permissions WHERE id = p_id;
END$$

-- ─────────────────────────────────────────────────
-- USERS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_users_findAll$$
CREATE PROCEDURE sp_users_findAll()
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.password, u.rol_id, u.activo,
           u.foto, u.telefono, u.created_at, u.updated_at,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u
    INNER JOIN roles r ON r.id = u.rol_id
    WHERE u.deleted_at IS NULL
    ORDER BY u.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_users_findById$$
CREATE PROCEDURE sp_users_findById(IN p_id INT)
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.password, u.rol_id, u.activo,
           u.foto, u.telefono, u.created_at, u.updated_at,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u
    INNER JOIN roles r ON r.id = u.rol_id
    WHERE u.id = p_id AND u.deleted_at IS NULL
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_users_findByEmail$$
CREATE PROCEDURE sp_users_findByEmail(IN p_email VARCHAR(120))
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.password, u.rol_id, u.activo,
           u.foto, u.telefono, u.created_at, u.updated_at,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u
    INNER JOIN roles r ON r.id = u.rol_id
    WHERE u.email = p_email AND u.deleted_at IS NULL
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_users_findByUsername$$
CREATE PROCEDURE sp_users_findByUsername(IN p_username VARCHAR(60))
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.password, u.rol_id, u.activo,
           u.foto, u.telefono, u.created_at, u.updated_at,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u
    INNER JOIN roles r ON r.id = u.rol_id
    WHERE u.username = p_username AND u.deleted_at IS NULL
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_users_findByRol$$
CREATE PROCEDURE sp_users_findByRol(IN p_rol_id INT)
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.activo, u.foto, u.telefono,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u
    INNER JOIN roles r ON r.id = u.rol_id
    WHERE u.rol_id = p_rol_id AND u.deleted_at IS NULL;
END$$

DROP PROCEDURE IF EXISTS sp_users_emailExists$$
CREATE PROCEDURE sp_users_emailExists(IN p_email VARCHAR(120), IN p_exclude_id INT)
BEGIN
    SELECT IF(COUNT(*) > 0, 1, 0) AS existe
    FROM users WHERE email = p_email AND id <> p_exclude_id AND deleted_at IS NULL;
END$$

DROP PROCEDURE IF EXISTS sp_users_usernameExists$$
CREATE PROCEDURE sp_users_usernameExists(IN p_username VARCHAR(60), IN p_exclude_id INT)
BEGIN
    SELECT IF(COUNT(*) > 0, 1, 0) AS existe
    FROM users WHERE username = p_username AND id <> p_exclude_id AND deleted_at IS NULL;
END$$

DROP PROCEDURE IF EXISTS sp_users_count$$
CREATE PROCEDURE sp_users_count()
BEGIN
    SELECT COUNT(*) AS total FROM users WHERE deleted_at IS NULL;
END$$

DROP PROCEDURE IF EXISTS sp_users_countActivos$$
CREATE PROCEDURE sp_users_countActivos()
BEGIN
    SELECT COUNT(*) AS total FROM users WHERE activo = 1 AND deleted_at IS NULL;
END$$

DROP PROCEDURE IF EXISTS sp_users_insert$$
CREATE PROCEDURE sp_users_insert(
    IN p_nombre   VARCHAR(120),
    IN p_username VARCHAR(60),
    IN p_email    VARCHAR(120),
    IN p_password VARCHAR(255),
    IN p_rol_id   INT,
    IN p_activo   TINYINT,
    IN p_foto     VARCHAR(255),
    IN p_telefono VARCHAR(30)
)
BEGIN
    INSERT INTO users (nombre, username, email, password, rol_id, activo, foto, telefono)
    VALUES (p_nombre, p_username, p_email, p_password, p_rol_id, p_activo, p_foto, p_telefono);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_users_update$$
CREATE PROCEDURE sp_users_update(
    IN p_id       INT,
    IN p_nombre   VARCHAR(120),
    IN p_username VARCHAR(60),
    IN p_email    VARCHAR(120),
    IN p_rol_id   INT,
    IN p_activo   TINYINT,
    IN p_foto     VARCHAR(255),
    IN p_telefono VARCHAR(30)
)
BEGIN
    UPDATE users
    SET nombre = p_nombre, username = p_username, email = p_email,
        rol_id = p_rol_id, activo = p_activo, foto = p_foto, telefono = p_telefono
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_users_updatePassword$$
CREATE PROCEDURE sp_users_updatePassword(IN p_id INT, IN p_password VARCHAR(255))
BEGIN
    UPDATE users SET password = p_password WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_users_updateSessionToken$$
CREATE PROCEDURE sp_users_updateSessionToken(IN p_id INT, IN p_token VARCHAR(255))
BEGIN
    UPDATE users SET session_token = p_token WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_users_updatePerfil$$
CREATE PROCEDURE sp_users_updatePerfil(
    IN p_id       INT,
    IN p_nombre   VARCHAR(120),
    IN p_username VARCHAR(60),
    IN p_email    VARCHAR(120),
    IN p_telefono VARCHAR(30),
    IN p_foto     VARCHAR(255)
)
BEGIN
    UPDATE users
    SET nombre = p_nombre, username = p_username, email = p_email,
        telefono = p_telefono,
        foto = COALESCE(p_foto, foto)
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_users_toggleActivo$$
CREATE PROCEDURE sp_users_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE users SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_users_delete$$
CREATE PROCEDURE sp_users_delete(IN p_id INT)
BEGIN
    UPDATE users SET deleted_at = NOW(), activo = 0 WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_users_marcarTour$$
CREATE PROCEDURE sp_users_marcarTour(IN p_id INT)
BEGIN
    UPDATE users SET tour_completado = 1 WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_users_activarTour$$
CREATE PROCEDURE sp_users_activarTour(IN p_id INT)
BEGIN
    UPDATE users SET tour_completado = 0 WHERE id = p_id;
END$$

-- ─────────────────────────────────────────────────
-- RATE LIMITS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_rate_limits_check$$
CREATE PROCEDURE sp_rate_limits_check(IN p_ip VARCHAR(45))
BEGIN
    SELECT
        intentos,
        IF(bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW(), 1, 0) AS bloqueado,
        IF(bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW(),
           TIMESTAMPDIFF(MINUTE, NOW(), bloqueado_hasta), 0) AS minutos_restantes
    FROM rate_limits WHERE ip = p_ip LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_rate_limits_register_fallo$$
CREATE PROCEDURE sp_rate_limits_register_fallo(
    IN p_ip            VARCHAR(45),
    IN p_max_intentos  INT,
    IN p_bloqueo_min   INT
)
BEGIN
    INSERT INTO rate_limits (ip, intentos, ultima_falla, bloqueado_hasta)
    VALUES (p_ip, 1, NOW(), NULL)
    ON DUPLICATE KEY UPDATE
        intentos        = intentos + 1,
        ultima_falla    = NOW(),
        bloqueado_hasta = IF(intentos + 1 >= p_max_intentos,
                             DATE_ADD(NOW(), INTERVAL p_bloqueo_min MINUTE),
                             bloqueado_hasta);
END$$

DROP PROCEDURE IF EXISTS sp_rate_limits_limpiar$$
CREATE PROCEDURE sp_rate_limits_limpiar(IN p_ip VARCHAR(45))
BEGIN
    DELETE FROM rate_limits WHERE ip = p_ip;
END$$

DELIMITER ;


DELIMITER $$

-- ─────────────────────────────────────────────────
-- CATEGORÍAS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_categorias_findAll$$
CREATE PROCEDURE sp_categorias_findAll()
BEGIN
    SELECT id, nombre, descripcion, activo, created_at FROM categorias ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_categorias_findActivas$$
CREATE PROCEDURE sp_categorias_findActivas()
BEGIN
    SELECT id, nombre, descripcion, activo, created_at FROM categorias WHERE activo = 1 ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_categorias_findById$$
CREATE PROCEDURE sp_categorias_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, descripcion, activo, created_at FROM categorias WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_categorias_insert$$
CREATE PROCEDURE sp_categorias_insert(IN p_nombre VARCHAR(80), IN p_descripcion VARCHAR(255))
BEGIN
    INSERT INTO categorias (nombre, descripcion) VALUES (p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_categorias_update$$
CREATE PROCEDURE sp_categorias_update(IN p_id INT, IN p_nombre VARCHAR(80), IN p_descripcion VARCHAR(255))
BEGIN
    UPDATE categorias SET nombre = p_nombre, descripcion = p_descripcion WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_categorias_toggleActivo$$
CREATE PROCEDURE sp_categorias_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE categorias SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_categorias_delete$$
CREATE PROCEDURE sp_categorias_delete(IN p_id INT)
BEGIN
    UPDATE categorias SET activo = 0 WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_categorias_hasProductos$$
CREATE PROCEDURE sp_categorias_hasProductos(IN p_id INT)
BEGIN
    SELECT COUNT(*) AS total FROM productos WHERE categoria_id = p_id AND activo = 1;
END$$

DROP PROCEDURE IF EXISTS sp_categorias_count$$
CREATE PROCEDURE sp_categorias_count()
BEGIN
    SELECT COUNT(*) AS total FROM categorias WHERE activo = 1;
END$$

-- ─────────────────────────────────────────────────
-- PRODUCTOS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_productos_findAll$$
CREATE PROCEDURE sp_productos_findAll()
BEGIN
    SELECT p.id, p.categoria_id, c.nombre AS categoria_nombre, p.nombre,
           p.descripcion, p.precio_base, p.tiene_variantes, p.stock,
           p.codigo_barras, p.image_url, p.visible_tienda, p.activo,
           p.created_at, p.updated_at
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    ORDER BY p.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_productos_findActivos$$
CREATE PROCEDURE sp_productos_findActivos()
BEGIN
    SELECT p.id, p.categoria_id, c.nombre AS categoria_nombre, p.nombre,
           p.descripcion, p.precio_base, p.tiene_variantes, p.stock,
           p.codigo_barras, p.image_url, p.visible_tienda, p.activo,
           p.created_at, p.updated_at
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.activo = 1 AND p.visible_tienda = 1
    ORDER BY p.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_productos_findById$$
CREATE PROCEDURE sp_productos_findById(IN p_id INT)
BEGIN
    SELECT p.id, p.categoria_id, c.nombre AS categoria_nombre, p.nombre,
           p.descripcion, p.precio_base, p.tiene_variantes, p.stock,
           p.codigo_barras, p.image_url, p.visible_tienda, p.activo,
           p.created_at, p.updated_at
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_productos_findByNombre$$
CREATE PROCEDURE sp_productos_findByNombre(IN p_nombre VARCHAR(150))
BEGIN
    SELECT p.id, p.categoria_id, c.nombre AS categoria_nombre, p.nombre,
           p.descripcion, p.precio_base, p.tiene_variantes, p.stock,
           p.codigo_barras, p.image_url, p.visible_tienda, p.activo
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.activo = 1 AND p.nombre LIKE CONCAT('%', p_nombre, '%')
    ORDER BY p.nombre LIMIT 30;
END$$

DROP PROCEDURE IF EXISTS sp_productos_findByBarras$$
CREATE PROCEDURE sp_productos_findByBarras(IN p_barras VARCHAR(60))
BEGIN
    -- Busca primero en variantes, luego en productos
    SELECT v.id AS variante_id, v.producto_id, p.nombre AS producto_nombre,
           v.nombre AS variante_nombre, v.precio, v.stock, v.codigo_barras,
           COALESCE(v.image_url, p.image_url) AS image_url, p.precio_base
    FROM producto_variantes v
    INNER JOIN productos p ON p.id = v.producto_id
    WHERE v.codigo_barras = p_barras AND v.activo = 1 AND p.activo = 1
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_productos_findSimpleByBarras$$
CREATE PROCEDURE sp_productos_findSimpleByBarras(IN p_barras VARCHAR(60))
BEGIN
    SELECT id, categoria_id, nombre, descripcion, precio_base, stock, codigo_barras, image_url, activo
    FROM productos
    WHERE codigo_barras = p_barras AND tiene_variantes = 0 AND activo = 1
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_productos_findVariantes$$
CREATE PROCEDURE sp_productos_findVariantes(IN p_producto_id INT)
BEGIN
    SELECT v.id, v.producto_id, v.nombre, v.precio, v.stock, v.codigo_barras,
           v.image_url, v.orden, v.activo, p.precio_base AS precio_base_producto
    FROM producto_variantes v
    INNER JOIN productos p ON p.id = v.producto_id
    WHERE v.producto_id = p_producto_id AND v.activo = 1
    ORDER BY v.orden, v.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_productos_insert$$
CREATE PROCEDURE sp_productos_insert(
    IN p_categoria_id    INT,
    IN p_nombre          VARCHAR(150),
    IN p_descripcion     TEXT,
    IN p_precio_base     DECIMAL(10,2),
    IN p_tiene_variantes TINYINT,
    IN p_stock           INT,
    IN p_codigo_barras   VARCHAR(60),
    IN p_image_url       VARCHAR(255)
)
BEGIN
    INSERT INTO productos (categoria_id, nombre, descripcion, precio_base,
                            tiene_variantes, stock, codigo_barras, image_url)
    VALUES (p_categoria_id, p_nombre, p_descripcion, p_precio_base,
            p_tiene_variantes, p_stock, p_codigo_barras, p_image_url);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_productos_update$$
CREATE PROCEDURE sp_productos_update(
    IN p_id            INT,
    IN p_categoria_id  INT,
    IN p_nombre        VARCHAR(150),
    IN p_descripcion   TEXT,
    IN p_precio_base   DECIMAL(10,2),
    IN p_stock         INT,
    IN p_codigo_barras VARCHAR(60),
    IN p_image_url     VARCHAR(255)
)
BEGIN
    UPDATE productos
    SET categoria_id  = p_categoria_id,
        nombre        = p_nombre,
        descripcion   = p_descripcion,
        precio_base   = p_precio_base,
        stock         = p_stock,
        codigo_barras = p_codigo_barras,
        image_url     = COALESCE(p_image_url, image_url)
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_productos_toggleActivo$$
CREATE PROCEDURE sp_productos_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE productos SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_productos_toggleVisibleTienda$$
CREATE PROCEDURE sp_productos_toggleVisibleTienda(IN p_id INT, IN p_visible TINYINT)
BEGIN
    UPDATE productos SET visible_tienda = p_visible WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_productos_delete$$
CREATE PROCEDURE sp_productos_delete(IN p_id INT)
BEGIN
    UPDATE productos SET activo = 0 WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_productos_updateStock$$
CREATE PROCEDURE sp_productos_updateStock(IN p_id INT, IN p_cantidad INT)
BEGIN
    -- Descuenta stock; retorna afectado=1 si había stock, 0 si no
    UPDATE productos SET stock = stock - p_cantidad
    WHERE id = p_id AND stock >= p_cantidad;
    SELECT ROW_COUNT() AS afectado;
END$$

DROP PROCEDURE IF EXISTS sp_productos_count$$
CREATE PROCEDURE sp_productos_count()
BEGIN
    SELECT COUNT(*) AS total FROM productos;
END$$

DROP PROCEDURE IF EXISTS sp_productos_countActivos$$
CREATE PROCEDURE sp_productos_countActivos()
BEGIN
    SELECT COUNT(*) AS total FROM productos WHERE activo = 1;
END$$

-- ─────────────────────────────────────────────────
-- VARIANTES
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_variantes_findByProducto$$
CREATE PROCEDURE sp_variantes_findByProducto(IN p_producto_id INT)
BEGIN
    SELECT v.id, v.producto_id, v.nombre, v.precio, v.stock, v.codigo_barras,
           v.image_url, v.orden, v.activo, p.precio_base AS precio_base_producto
    FROM producto_variantes v
    INNER JOIN productos p ON p.id = v.producto_id
    WHERE v.producto_id = p_producto_id
    ORDER BY v.orden, v.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_variantes_findById$$
CREATE PROCEDURE sp_variantes_findById(IN p_id INT)
BEGIN
    SELECT v.id, v.producto_id, v.nombre, v.precio, v.stock, v.codigo_barras,
           v.image_url, v.orden, v.activo, p.precio_base AS precio_base_producto
    FROM producto_variantes v
    INNER JOIN productos p ON p.id = v.producto_id
    WHERE v.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_variantes_insert$$
CREATE PROCEDURE sp_variantes_insert(
    IN p_producto_id   INT,
    IN p_nombre        VARCHAR(100),
    IN p_precio        DECIMAL(10,2),
    IN p_stock         INT,
    IN p_codigo_barras VARCHAR(60),
    IN p_image_url     VARCHAR(255),
    IN p_orden         INT
)
BEGIN
    INSERT INTO producto_variantes (producto_id, nombre, precio, stock, codigo_barras, image_url, orden)
    VALUES (p_producto_id, p_nombre, p_precio, p_stock, p_codigo_barras, p_image_url, p_orden);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_variantes_update$$
CREATE PROCEDURE sp_variantes_update(
    IN p_id            INT,
    IN p_nombre        VARCHAR(100),
    IN p_precio        DECIMAL(10,2),
    IN p_stock         INT,
    IN p_codigo_barras VARCHAR(60),
    IN p_image_url     VARCHAR(255),
    IN p_orden         INT
)
BEGIN
    UPDATE producto_variantes
    SET nombre = p_nombre, precio = p_precio, stock = p_stock,
        codigo_barras = p_codigo_barras,
        image_url = COALESCE(p_image_url, image_url),
        orden = p_orden
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_variantes_toggleActivo$$
CREATE PROCEDURE sp_variantes_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE producto_variantes SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_variantes_delete$$
CREATE PROCEDURE sp_variantes_delete(IN p_id INT)
BEGIN
    DELETE FROM producto_variantes WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_variantes_updateStock$$
CREATE PROCEDURE sp_variantes_updateStock(IN p_id INT, IN p_cantidad INT)
BEGIN
    UPDATE producto_variantes SET stock = stock - p_cantidad
    WHERE id = p_id AND stock >= p_cantidad;
    SELECT ROW_COUNT() AS afectado;
END$$

-- ─────────────────────────────────────────────────
-- DESCUENTOS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_descuentos_findAll$$
CREATE PROCEDURE sp_descuentos_findAll()
BEGIN
    SELECT d.id, d.nombre, d.porcentaje, d.aplica_a, d.categoria_id,
           c.nombre AS categoria_nombre, d.fecha_inicio, d.fecha_fin, d.activo
    FROM descuentos d
    LEFT JOIN categorias c ON c.id = d.categoria_id
    ORDER BY d.created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_descuentos_findById$$
CREATE PROCEDURE sp_descuentos_findById(IN p_id INT)
BEGIN
    SELECT d.id, d.nombre, d.porcentaje, d.aplica_a, d.categoria_id,
           c.nombre AS categoria_nombre, d.fecha_inicio, d.fecha_fin, d.activo
    FROM descuentos d
    LEFT JOIN categorias c ON c.id = d.categoria_id
    WHERE d.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_descuentos_getActivo$$
CREATE PROCEDURE sp_descuentos_getActivo()
BEGIN
    SELECT d.id, d.nombre, d.porcentaje, d.aplica_a, d.categoria_id,
           c.nombre AS categoria_nombre, d.fecha_inicio, d.fecha_fin
    FROM descuentos d
    LEFT JOIN categorias c ON c.id = d.categoria_id
    WHERE d.activo = 1 AND CURDATE() BETWEEN d.fecha_inicio AND d.fecha_fin
    ORDER BY d.created_at DESC LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_descuentos_insert$$
CREATE PROCEDURE sp_descuentos_insert(
    IN p_nombre       VARCHAR(120),
    IN p_porcentaje   DECIMAL(5,2),
    IN p_aplica_a     VARCHAR(20),
    IN p_categoria_id INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin    DATE
)
BEGIN
    INSERT INTO descuentos (nombre, porcentaje, aplica_a, categoria_id, fecha_inicio, fecha_fin)
    VALUES (p_nombre, p_porcentaje, p_aplica_a, p_categoria_id, p_fecha_inicio, p_fecha_fin);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_descuentos_update$$
CREATE PROCEDURE sp_descuentos_update(
    IN p_id           INT,
    IN p_nombre       VARCHAR(120),
    IN p_porcentaje   DECIMAL(5,2),
    IN p_aplica_a     VARCHAR(20),
    IN p_categoria_id INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin    DATE,
    IN p_activo       TINYINT
)
BEGIN
    UPDATE descuentos
    SET nombre = p_nombre, porcentaje = p_porcentaje, aplica_a = p_aplica_a,
        categoria_id = p_categoria_id, fecha_inicio = p_fecha_inicio,
        fecha_fin = p_fecha_fin, activo = p_activo
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_descuentos_delete$$
CREATE PROCEDURE sp_descuentos_delete(IN p_id INT)
BEGIN
    DELETE FROM descuentos WHERE id = p_id;
END$$

-- ─────────────────────────────────────────────────
-- CLIENTES
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_clientes_findAll$$
CREATE PROCEDURE sp_clientes_findAll()
BEGIN
    SELECT id, nombre, email, telefono, direccion, activo, created_at, updated_at
    FROM clientes ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_findById$$
CREATE PROCEDURE sp_clientes_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, email, telefono, direccion, password, activo, created_at, updated_at
    FROM clientes WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_findByEmail$$
CREATE PROCEDURE sp_clientes_findByEmail(IN p_email VARCHAR(120))
BEGIN
    SELECT id, nombre, email, telefono, direccion, password, activo, created_at, updated_at
    FROM clientes WHERE email = p_email LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_search$$
CREATE PROCEDURE sp_clientes_search(IN p_q VARCHAR(120))
BEGIN
    SELECT id, nombre, email, telefono, direccion
    FROM clientes
    WHERE activo = 1
      AND (nombre LIKE CONCAT('%', p_q, '%')
        OR email    LIKE CONCAT('%', p_q, '%')
        OR telefono LIKE CONCAT('%', p_q, '%'))
    ORDER BY nombre LIMIT 30;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_insert$$
CREATE PROCEDURE sp_clientes_insert(
    IN p_nombre   VARCHAR(120),
    IN p_email    VARCHAR(120),
    IN p_telefono VARCHAR(30),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO clientes (nombre, email, telefono, password)
    VALUES (p_nombre, p_email, p_telefono, p_password);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_update$$
CREATE PROCEDURE sp_clientes_update(
    IN p_id        INT,
    IN p_nombre    VARCHAR(120),
    IN p_email     VARCHAR(120),
    IN p_telefono  VARCHAR(30),
    IN p_direccion VARCHAR(255)
)
BEGIN
    UPDATE clientes
    SET nombre = p_nombre, email = p_email, telefono = p_telefono, direccion = p_direccion
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_updatePassword$$
CREATE PROCEDURE sp_clientes_updatePassword(IN p_id INT, IN p_hash VARCHAR(255))
BEGIN
    UPDATE clientes SET password = p_hash WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_toggleActivo$$
CREATE PROCEDURE sp_clientes_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE clientes SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_count$$
CREATE PROCEDURE sp_clientes_count()
BEGIN
    SELECT COUNT(*) AS total FROM clientes WHERE activo = 1;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_emailExists$$
CREATE PROCEDURE sp_clientes_emailExists(IN p_email VARCHAR(120))
BEGIN
    SELECT IF(COUNT(*) > 0, 1, 0) AS existe FROM clientes WHERE email = p_email;
END$$

DROP PROCEDURE IF EXISTS sp_clientes_emailExistsForUpdate$$
CREATE PROCEDURE sp_clientes_emailExistsForUpdate(IN p_email VARCHAR(120), IN p_exclude_id INT)
BEGIN
    SELECT IF(COUNT(*) > 0, 1, 0) AS existe
    FROM clientes WHERE email = p_email AND id <> p_exclude_id;
END$$

-- ─────────────────────────────────────────────────
-- FAVORITOS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_favoritos_isFavorito$$
CREATE PROCEDURE sp_favoritos_isFavorito(IN p_cliente_id INT, IN p_producto_id INT)
BEGIN
    SELECT IF(COUNT(*) > 0, 1, 0) AS es_favorito
    FROM favoritos WHERE cliente_id = p_cliente_id AND producto_id = p_producto_id;
END$$

DROP PROCEDURE IF EXISTS sp_favoritos_findByCliente$$
CREATE PROCEDURE sp_favoritos_findByCliente(IN p_cliente_id INT)
BEGIN
    SELECT f.producto_id, p.nombre, p.precio_base, p.image_url, p.activo
    FROM favoritos f
    INNER JOIN productos p ON p.id = f.producto_id
    WHERE f.cliente_id = p_cliente_id AND p.activo = 1
    ORDER BY f.created_at DESC;
END$$

DELIMITER ;


DELIMITER $$

-- ─────────────────────────────────────────────────
-- CAJA SESIONES
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_caja_getSesionAbierta$$
CREATE PROCEDURE sp_caja_getSesionAbierta(IN p_user_id INT)
BEGIN
    SELECT * FROM caja_sesiones
    WHERE user_id = p_user_id AND estado = 'abierta'
    ORDER BY abierta_at DESC LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_caja_findById$$
CREATE PROCEDURE sp_caja_findById(IN p_id INT)
BEGIN
    SELECT cs.*, u.nombre AS cajero_nombre
    FROM caja_sesiones cs
    INNER JOIN users u ON u.id = cs.user_id
    WHERE cs.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_caja_abrir$$
CREATE PROCEDURE sp_caja_abrir(
    IN p_user_id  INT,
    IN p_monto    DECIMAL(10,2),
    IN p_nota     VARCHAR(255)
)
BEGIN
    INSERT INTO caja_sesiones (user_id, monto_apertura, nota, estado, abierta_at)
    VALUES (p_user_id, p_monto, p_nota, 'abierta', NOW());
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_caja_calcularTotales$$
CREATE PROCEDURE sp_caja_calcularTotales(
    IN p_sesion_id    INT,
    IN p_user_id      INT,
    IN p_abierta_at   DATETIME
)
BEGIN
    SELECT
        (SELECT COALESCE(SUM(total),0) FROM ventas
            WHERE user_id = p_user_id AND created_at >= p_abierta_at AND anulada = 0) AS total_ventas,
        (SELECT COALESCE(SUM(total),0) FROM ventas
            WHERE user_id = p_user_id AND created_at >= p_abierta_at AND anulada = 0 AND metodo_pago = 'Efectivo')      AS total_efectivo,
        (SELECT COALESCE(SUM(total),0) FROM ventas
            WHERE user_id = p_user_id AND created_at >= p_abierta_at AND anulada = 0 AND metodo_pago = 'Tarjeta')       AS total_tarjeta,
        (SELECT COALESCE(SUM(total),0) FROM ventas
            WHERE user_id = p_user_id AND created_at >= p_abierta_at AND anulada = 0 AND metodo_pago = 'Transferencia') AS total_transferencia,
        (SELECT COALESCE(SUM(total),0) FROM ventas
            WHERE user_id = p_user_id AND created_at >= p_abierta_at AND anulada = 1) AS total_anuladas;
END$$

DROP PROCEDURE IF EXISTS sp_caja_cerrar$$
CREATE PROCEDURE sp_caja_cerrar(
    IN p_sesion_id             INT,
    IN p_user_id               INT,
    IN p_monto_cierre          DECIMAL(10,2),
    IN p_monto_sistema         DECIMAL(10,2),
    IN p_total_ventas          DECIMAL(10,2),
    IN p_total_efectivo        DECIMAL(10,2),
    IN p_total_tarjeta         DECIMAL(10,2),
    IN p_total_transferencia   DECIMAL(10,2),
    IN p_total_anuladas        DECIMAL(10,2),
    IN p_nota_cierre           VARCHAR(255)
)
BEGIN
    UPDATE caja_sesiones SET
        monto_cierre        = p_monto_cierre,
        monto_sistema       = p_monto_sistema,
        total_ventas        = p_total_ventas,
        total_efectivo      = p_total_efectivo,
        total_tarjeta       = p_total_tarjeta,
        total_transferencia = p_total_transferencia,
        total_anuladas      = p_total_anuladas,
        nota_cierre         = p_nota_cierre,
        estado              = 'cerrada',
        cerrada_at          = NOW()
    WHERE id = p_sesion_id AND user_id = p_user_id AND estado = 'abierta';
    SELECT ROW_COUNT() AS afectado;
END$$

DROP PROCEDURE IF EXISTS sp_caja_historial$$
CREATE PROCEDURE sp_caja_historial()
BEGIN
    SELECT cs.*, u.nombre AS cajero_nombre
    FROM caja_sesiones cs
    INNER JOIN users u ON u.id = cs.user_id
    ORDER BY cs.abierta_at DESC LIMIT 100;
END$$

-- ─────────────────────────────────────────────────
-- VENTAS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_ventas_findAll$$
CREATE PROCEDURE sp_ventas_findAll()
BEGIN
    SELECT v.*, c.nombre AS cliente_nombre, u.nombre AS cajero_nombre
    FROM ventas v
    LEFT JOIN clientes c ON c.id = v.cliente_id
    LEFT JOIN users    u ON u.id = v.user_id
    ORDER BY v.created_at DESC LIMIT 500;
END$$

DROP PROCEDURE IF EXISTS sp_ventas_findById$$
CREATE PROCEDURE sp_ventas_findById(IN p_id INT)
BEGIN
    SELECT v.*, c.nombre AS cliente_nombre, u.nombre AS cajero_nombre
    FROM ventas v
    LEFT JOIN clientes c ON c.id = v.cliente_id
    LEFT JOIN users    u ON u.id = v.user_id
    WHERE v.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_ventas_findDetalle$$
CREATE PROCEDURE sp_ventas_findDetalle(IN p_venta_id INT)
BEGIN
    SELECT vd.*, p.image_url AS producto_imagen
    FROM venta_detalle vd
    LEFT JOIN productos p ON p.id = vd.producto_id
    WHERE vd.venta_id = p_venta_id;
END$$

DROP PROCEDURE IF EXISTS sp_ventas_countHoy$$
CREATE PROCEDURE sp_ventas_countHoy()
BEGIN
    SELECT COUNT(*) AS total FROM ventas
    WHERE DATE(created_at) = CURDATE() AND anulada = 0;
END$$

DROP PROCEDURE IF EXISTS sp_ventas_totalHoy$$
CREATE PROCEDURE sp_ventas_totalHoy()
BEGIN
    SELECT COALESCE(SUM(total),0) AS total FROM ventas
    WHERE DATE(created_at) = CURDATE() AND anulada = 0;
END$$

DROP PROCEDURE IF EXISTS sp_ventas_insert$$
CREATE PROCEDURE sp_ventas_insert(
    IN p_cliente_id     INT,
    IN p_user_id        INT,
    IN p_metodo_pago    VARCHAR(20),
    IN p_subtotal       DECIMAL(10,2),
    IN p_descuento      DECIMAL(10,2),
    IN p_total          DECIMAL(10,2),
    IN p_monto_recibido DECIMAL(10,2),
    IN p_cambio         DECIMAL(10,2),
    IN p_nota           VARCHAR(255)
)
BEGIN
    INSERT INTO ventas (cliente_id, user_id, metodo_pago, subtotal, descuento, total,
                        monto_recibido, cambio, nota)
    VALUES (p_cliente_id, p_user_id, p_metodo_pago, p_subtotal, p_descuento, p_total,
            p_monto_recibido, p_cambio, p_nota);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_ventas_insertDetalle$$
CREATE PROCEDURE sp_ventas_insertDetalle(
    IN p_venta_id        INT,
    IN p_producto_id     INT,
    IN p_variante_id     INT,
    IN p_nombre_producto VARCHAR(255),
    IN p_precio_unit     DECIMAL(10,2),
    IN p_cantidad        INT,
    IN p_subtotal        DECIMAL(10,2)
)
BEGIN
    INSERT INTO venta_detalle (venta_id, producto_id, variante_id, nombre_producto,
                                precio_unit, cantidad, subtotal)
    VALUES (p_venta_id, p_producto_id, p_variante_id, p_nombre_producto,
            p_precio_unit, p_cantidad, p_subtotal);
END$$

DROP PROCEDURE IF EXISTS sp_ventas_anular$$
CREATE PROCEDURE sp_ventas_anular(IN p_id INT, IN p_motivo VARCHAR(255), IN p_user_id INT)
BEGIN
    UPDATE ventas SET anulada = 1, motivo_anulacion = p_motivo,
                       anulada_por = p_user_id, anulada_at = NOW()
    WHERE id = p_id AND anulada = 0;
    SELECT ROW_COUNT() AS afectado;
END$$

-- ─────────────────────────────────────────────────
-- FACTURACIÓN
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_facturacion_getConfig$$
CREATE PROCEDURE sp_facturacion_getConfig()
BEGIN
    SELECT * FROM facturacion_config WHERE id = 1 LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_facturacion_updateConfig$$
CREATE PROCEDURE sp_facturacion_updateConfig(
    IN p_rtn              VARCHAR(20),
    IN p_cai              VARCHAR(255),
    IN p_rango_desde      VARCHAR(40),
    IN p_rango_hasta      VARCHAR(40),
    IN p_fecha_limite     DATE,
    IN p_establecimiento  VARCHAR(10),
    IN p_punto_emision    VARCHAR(10),
    IN p_nombre_fiscal    VARCHAR(150),
    IN p_direccion_fiscal VARCHAR(255),
    IN p_correlativo      INT
)
BEGIN
    INSERT INTO facturacion_config (id, rtn, cai, rango_desde, rango_hasta, fecha_limite,
        establecimiento, punto_emision, nombre_fiscal, direccion_fiscal, correlativo)
    VALUES (1, p_rtn, p_cai, p_rango_desde, p_rango_hasta, p_fecha_limite,
            p_establecimiento, p_punto_emision, p_nombre_fiscal, p_direccion_fiscal, p_correlativo)
    ON DUPLICATE KEY UPDATE
        rtn = p_rtn, cai = p_cai, rango_desde = p_rango_desde, rango_hasta = p_rango_hasta,
        fecha_limite = p_fecha_limite, establecimiento = p_establecimiento,
        punto_emision = p_punto_emision, nombre_fiscal = p_nombre_fiscal,
        direccion_fiscal = p_direccion_fiscal, correlativo = p_correlativo;
END$$

-- ─────────────────────────────────────────────────
-- PEDIDOS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_pedidos_findAll$$
CREATE PROCEDURE sp_pedidos_findAll()
BEGIN
    SELECT p.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.created_at DESC LIMIT 500;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_findById$$
CREATE PROCEDURE sp_pedidos_findById(IN p_id INT)
BEGIN
    SELECT p.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_findByEstado$$
CREATE PROCEDURE sp_pedidos_findByEstado(IN p_estado VARCHAR(40))
BEGIN
    SELECT p.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.estado = p_estado
    ORDER BY p.created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_findByCliente$$
CREATE PROCEDURE sp_pedidos_findByCliente(IN p_cliente_id INT)
BEGIN
    SELECT p.*, c.nombre AS cliente_nombre
    FROM pedidos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.cliente_id = p_cliente_id
    ORDER BY p.created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_findDetalle$$
CREATE PROCEDURE sp_pedidos_findDetalle(IN p_pedido_id INT)
BEGIN
    SELECT pd.*, p.image_url AS producto_imagen
    FROM pedido_detalle pd
    LEFT JOIN productos p ON p.id = pd.producto_id
    WHERE pd.pedido_id = p_pedido_id;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_findHistorial$$
CREATE PROCEDURE sp_pedidos_findHistorial(IN p_pedido_id INT)
BEGIN
    SELECT ph.*, u.nombre AS usuario_nombre
    FROM pedido_historial ph
    LEFT JOIN users u ON u.id = ph.user_id
    WHERE ph.pedido_id = p_pedido_id
    ORDER BY ph.created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_countByEstado$$
CREATE PROCEDURE sp_pedidos_countByEstado(IN p_estado VARCHAR(40))
BEGIN
    SELECT COUNT(*) AS total FROM pedidos WHERE estado = p_estado;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_countHoy$$
CREATE PROCEDURE sp_pedidos_countHoy()
BEGIN
    SELECT COUNT(*) AS total FROM pedidos WHERE DATE(created_at) = CURDATE();
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_existeCodigo$$
CREATE PROCEDURE sp_pedidos_existeCodigo(IN p_codigo VARCHAR(20))
BEGIN
    SELECT COUNT(*) AS total FROM pedidos WHERE codigo = p_codigo;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_insert$$
CREATE PROCEDURE sp_pedidos_insert(
    IN p_codigo          VARCHAR(20),
    IN p_cliente_id      INT,
    IN p_wa_numero       VARCHAR(30),
    IN p_tipo_entrega    VARCHAR(20),
    IN p_metodo_pago     VARCHAR(20),
    IN p_direccion_envio VARCHAR(255),
    IN p_zona_id         INT,
    IN p_subtotal        DECIMAL(10,2),
    IN p_costo_envio     DECIMAL(10,2),
    IN p_total           DECIMAL(10,2),
    IN p_nota            VARCHAR(500)
)
BEGIN
    INSERT INTO pedidos (codigo, cliente_id, wa_numero, tipo_entrega, metodo_pago,
                          direccion_envio, zona_id, subtotal, costo_envio, total, nota)
    VALUES (p_codigo, p_cliente_id, p_wa_numero, p_tipo_entrega, p_metodo_pago,
            p_direccion_envio, p_zona_id, p_subtotal, p_costo_envio, p_total, p_nota);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_insertDetalle$$
CREATE PROCEDURE sp_pedidos_insertDetalle(
    IN p_pedido_id       INT,
    IN p_producto_id     INT,
    IN p_variante_id     INT,
    IN p_nombre_producto VARCHAR(255),
    IN p_precio_unit     DECIMAL(10,2),
    IN p_cantidad        INT,
    IN p_subtotal        DECIMAL(10,2)
)
BEGIN
    INSERT INTO pedido_detalle (pedido_id, producto_id, variante_id, nombre_producto,
                                 precio_unit, cantidad, subtotal)
    VALUES (p_pedido_id, p_producto_id, p_variante_id, p_nombre_producto,
            p_precio_unit, p_cantidad, p_subtotal);
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_updateEstado$$
CREATE PROCEDURE sp_pedidos_updateEstado(
    IN p_id      INT,
    IN p_estado  VARCHAR(40),
    IN p_user_id INT,
    IN p_nota    VARCHAR(255)
)
BEGIN
    DECLARE v_estado_anterior VARCHAR(40);
    SELECT estado INTO v_estado_anterior FROM pedidos WHERE id = p_id LIMIT 1;

    UPDATE pedidos SET estado = p_estado WHERE id = p_id;

    INSERT INTO pedido_historial (pedido_id, estado_anterior, estado_nuevo, user_id, nota)
    VALUES (p_id, v_estado_anterior, p_estado, p_user_id, p_nota);
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_marcarPagado$$
CREATE PROCEDURE sp_pedidos_marcarPagado(IN p_id INT, IN p_user_id INT)
BEGIN
    UPDATE pedidos SET pagado = 1, pagado_por = p_user_id, pagado_at = NOW(),
                        estado = 'En preparacion'
    WHERE id = p_id;
    INSERT INTO pedido_historial (pedido_id, estado_anterior, estado_nuevo, user_id, nota)
    VALUES (p_id, 'Pendiente', 'En preparacion', p_user_id, 'Pago confirmado');
END$$

DELIMITER ;


DELIMITER $$

-- ─────────────────────────────────────────────────
-- BANNERS
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_banners_findAll$$
CREATE PROCEDURE sp_banners_findAll()
BEGIN
    SELECT id, titulo, imagen_url, enlace, orden, activo, created_at
    FROM banners ORDER BY orden, id;
END$$

DROP PROCEDURE IF EXISTS sp_banners_findActivos$$
CREATE PROCEDURE sp_banners_findActivos()
BEGIN
    SELECT id, titulo, imagen_url, enlace, orden, activo
    FROM banners WHERE activo = 1 ORDER BY orden, id;
END$$

DROP PROCEDURE IF EXISTS sp_banners_findById$$
CREATE PROCEDURE sp_banners_findById(IN p_id INT)
BEGIN
    SELECT id, titulo, imagen_url, enlace, orden, activo FROM banners WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_banners_insert$$
CREATE PROCEDURE sp_banners_insert(
    IN p_titulo     VARCHAR(150),
    IN p_imagen_url VARCHAR(255),
    IN p_enlace     VARCHAR(255),
    IN p_orden      INT
)
BEGIN
    INSERT INTO banners (titulo, imagen_url, enlace, orden)
    VALUES (p_titulo, p_imagen_url, p_enlace, p_orden);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_banners_update$$
CREATE PROCEDURE sp_banners_update(
    IN p_id         INT,
    IN p_titulo     VARCHAR(150),
    IN p_imagen_url VARCHAR(255),
    IN p_enlace     VARCHAR(255),
    IN p_orden      INT
)
BEGIN
    UPDATE banners
    SET titulo = p_titulo,
        imagen_url = COALESCE(p_imagen_url, imagen_url),
        enlace = p_enlace, orden = p_orden
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_banners_toggleActivo$$
CREATE PROCEDURE sp_banners_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE banners SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_banners_delete$$
CREATE PROCEDURE sp_banners_delete(IN p_id INT)
BEGIN
    DELETE FROM banners WHERE id = p_id;
END$$

-- ─────────────────────────────────────────────────
-- ZONAS DE ENVÍO
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_zonas_findAll$$
CREATE PROCEDURE sp_zonas_findAll()
BEGIN
    SELECT id, nombre, costo, activo, created_at FROM zonas_envio ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_zonas_findActivas$$
CREATE PROCEDURE sp_zonas_findActivas()
BEGIN
    SELECT id, nombre, costo FROM zonas_envio WHERE activo = 1 ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_zonas_findById$$
CREATE PROCEDURE sp_zonas_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, costo, activo FROM zonas_envio WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_zonas_insert$$
CREATE PROCEDURE sp_zonas_insert(IN p_nombre VARCHAR(100), IN p_costo DECIMAL(10,2))
BEGIN
    INSERT INTO zonas_envio (nombre, costo) VALUES (p_nombre, p_costo);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_zonas_update$$
CREATE PROCEDURE sp_zonas_update(IN p_id INT, IN p_nombre VARCHAR(100), IN p_costo DECIMAL(10,2))
BEGIN
    UPDATE zonas_envio SET nombre = p_nombre, costo = p_costo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_zonas_toggleActivo$$
CREATE PROCEDURE sp_zonas_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE zonas_envio SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_zonas_delete$$
CREATE PROCEDURE sp_zonas_delete(IN p_id INT)
BEGIN
    DELETE FROM zonas_envio WHERE id = p_id;
END$$

-- ─────────────────────────────────────────────────
-- NOTIFICACIONES
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_notificaciones_findAll$$
CREATE PROCEDURE sp_notificaciones_findAll()
BEGIN
    SELECT id, tipo, titulo, mensaje, url, leida, created_at
    FROM notificaciones ORDER BY created_at DESC LIMIT 50;
END$$

DROP PROCEDURE IF EXISTS sp_notificaciones_countNoLeidas$$
CREATE PROCEDURE sp_notificaciones_countNoLeidas()
BEGIN
    SELECT COUNT(*) AS total FROM notificaciones WHERE leida = 0;
END$$

DROP PROCEDURE IF EXISTS sp_notificaciones_insert$$
CREATE PROCEDURE sp_notificaciones_insert(
    IN p_tipo    VARCHAR(40),
    IN p_titulo  VARCHAR(150),
    IN p_mensaje VARCHAR(500),
    IN p_url     VARCHAR(255)
)
BEGIN
    INSERT INTO notificaciones (tipo, titulo, mensaje, url)
    VALUES (p_tipo, p_titulo, p_mensaje, p_url);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_notificaciones_marcarLeida$$
CREATE PROCEDURE sp_notificaciones_marcarLeida(IN p_id INT)
BEGIN
    UPDATE notificaciones SET leida = 1 WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_notificaciones_marcarTodasLeidas$$
CREATE PROCEDURE sp_notificaciones_marcarTodasLeidas()
BEGIN
    UPDATE notificaciones SET leida = 1 WHERE leida = 0;
END$$

DROP PROCEDURE IF EXISTS sp_notificaciones_delete$$
CREATE PROCEDURE sp_notificaciones_delete(IN p_id INT)
BEGIN
    DELETE FROM notificaciones WHERE id = p_id;
END$$

-- ─────────────────────────────────────────────────
-- REPORTES
-- ─────────────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_reportes_resumenVentas$$
CREATE PROCEDURE sp_reportes_resumenVentas()
BEGIN
    SELECT
        COUNT(*) AS total_ventas,
        COALESCE(SUM(total),0) AS total_monto,
        COALESCE(AVG(total),0) AS promedio_venta,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS ventas_hoy,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total ELSE 0 END) AS monto_hoy
    FROM ventas WHERE anulada = 0;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_ventasPorDia$$
CREATE PROCEDURE sp_reportes_ventasPorDia()
BEGIN
    SELECT DATE(created_at) AS fecha, COUNT(*) AS total, COALESCE(SUM(total),0) AS monto
    FROM ventas WHERE anulada = 0 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at) ORDER BY fecha;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_ventasPorMes$$
CREATE PROCEDURE sp_reportes_ventasPorMes()
BEGIN
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes, COUNT(*) AS total, COALESCE(SUM(total),0) AS monto
    FROM ventas WHERE anulada = 0 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY mes;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_ventasPorMetodo$$
CREATE PROCEDURE sp_reportes_ventasPorMetodo()
BEGIN
    SELECT metodo_pago, COUNT(*) AS total, COALESCE(SUM(total),0) AS monto
    FROM ventas WHERE anulada = 0
    GROUP BY metodo_pago;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_topProductos$$
CREATE PROCEDURE sp_reportes_topProductos()
BEGIN
    SELECT vd.nombre_producto AS producto, SUM(vd.cantidad) AS unidades,
           COALESCE(SUM(vd.subtotal),0) AS monto
    FROM venta_detalle vd
    INNER JOIN ventas v ON v.id = vd.venta_id
    WHERE v.anulada = 0
    GROUP BY vd.nombre_producto ORDER BY unidades DESC LIMIT 10;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_resumenPedidos$$
CREATE PROCEDURE sp_reportes_resumenPedidos()
BEGIN
    SELECT COUNT(*) AS total_pedidos,
           SUM(CASE WHEN estado='Pendiente'      THEN 1 ELSE 0 END) AS pendientes,
           SUM(CASE WHEN estado='En preparacion' THEN 1 ELSE 0 END) AS en_preparacion,
           SUM(CASE WHEN estado='Listo'          THEN 1 ELSE 0 END) AS listos,
           SUM(CASE WHEN estado='Entregado'      THEN 1 ELSE 0 END) AS entregados,
           COALESCE(SUM(total),0) AS monto_total
    FROM pedidos;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_pedidosPorEstado$$
CREATE PROCEDURE sp_reportes_pedidosPorEstado()
BEGIN
    SELECT estado, COUNT(*) AS total FROM pedidos GROUP BY estado;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_pedidosPorDia$$
CREATE PROCEDURE sp_reportes_pedidosPorDia()
BEGIN
    SELECT DATE(created_at) AS fecha, COUNT(*) AS total
    FROM pedidos WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at) ORDER BY fecha;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_resumenInventario$$
CREATE PROCEDURE sp_reportes_resumenInventario()
BEGIN
    SELECT
        (SELECT COUNT(*)              FROM productos WHERE activo = 1)               AS total_productos,
        (SELECT COUNT(*)              FROM producto_variantes WHERE activo = 1)      AS total_variantes,
        (SELECT COALESCE(SUM(stock*precio_base),0) FROM productos WHERE activo = 1)  AS valor_inventario,
        (SELECT COUNT(*)              FROM productos WHERE activo = 1 AND stock < 5) AS stock_bajo,
        (SELECT COUNT(*)              FROM productos WHERE activo = 1 AND stock = 0) AS sin_stock;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_stockBajo$$
CREATE PROCEDURE sp_reportes_stockBajo(IN p_limite INT)
BEGIN
    SELECT p.id, p.nombre, p.stock, p.precio_base, c.nombre AS categoria
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.activo = 1 AND p.tiene_variantes = 0 AND p.stock <= p_limite
    ORDER BY p.stock ASC LIMIT 100;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_variantesStockBajo$$
CREATE PROCEDURE sp_reportes_variantesStockBajo(IN p_limite INT)
BEGIN
    SELECT v.id, v.nombre, v.stock, v.precio, p.nombre AS producto_nombre
    FROM producto_variantes v
    INNER JOIN productos p ON p.id = v.producto_id
    WHERE v.activo = 1 AND v.stock <= p_limite
    ORDER BY v.stock ASC LIMIT 100;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_inventarioCompleto$$
CREATE PROCEDURE sp_reportes_inventarioCompleto()
BEGIN
    SELECT p.id, c.nombre AS categoria, p.nombre AS producto, p.codigo_barras,
           p.stock, p.precio_base,
           ROUND(p.stock * COALESCE(p.precio_base,0), 2) AS valor,
           CASE WHEN p.activo=0 THEN 'Inactivo' WHEN p.stock=0 THEN 'Sin stock'
                WHEN p.stock<5 THEN 'Bajo' ELSE 'OK' END AS estado
    FROM productos p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    ORDER BY c.nombre, p.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_reportes_inventarioPorCategoria$$
CREATE PROCEDURE sp_reportes_inventarioPorCategoria()
BEGIN
    SELECT c.id, c.nombre AS categoria, COUNT(p.id) AS productos,
           COALESCE(SUM(p.stock),0) AS stock_total,
           ROUND(COALESCE(SUM(p.stock * p.precio_base),0), 2) AS valor
    FROM categorias c
    LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = 1
    GROUP BY c.id, c.nombre ORDER BY valor DESC;
END$$

DELIMITER ;


DELIMITER $$

-- ============================================================
-- SPs SERVICIO TÉCNICO (módulo nuevo)
-- ============================================================

DROP PROCEDURE IF EXISTS sp_servicios_catalogo_findAll$$
CREATE PROCEDURE sp_servicios_catalogo_findAll()
BEGIN
    SELECT id, nombre, descripcion, precio, categoria, activo, created_at
    FROM servicios_catalogo ORDER BY categoria, nombre;
END$$

DROP PROCEDURE IF EXISTS sp_servicios_catalogo_findActivos$$
CREATE PROCEDURE sp_servicios_catalogo_findActivos()
BEGIN
    SELECT id, nombre, descripcion, precio, categoria
    FROM servicios_catalogo WHERE activo = 1 ORDER BY categoria, nombre;
END$$

DROP PROCEDURE IF EXISTS sp_servicios_catalogo_findById$$
CREATE PROCEDURE sp_servicios_catalogo_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, descripcion, precio, categoria, activo
    FROM servicios_catalogo WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_servicios_catalogo_insert$$
CREATE PROCEDURE sp_servicios_catalogo_insert(
    IN p_nombre      VARCHAR(120),
    IN p_descripcion VARCHAR(500),
    IN p_precio      DECIMAL(10,2),
    IN p_categoria   VARCHAR(20)
)
BEGIN
    INSERT INTO servicios_catalogo (nombre, descripcion, precio, categoria)
    VALUES (p_nombre, p_descripcion, p_precio, p_categoria);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_servicios_catalogo_update$$
CREATE PROCEDURE sp_servicios_catalogo_update(
    IN p_id          INT,
    IN p_nombre      VARCHAR(120),
    IN p_descripcion VARCHAR(500),
    IN p_precio      DECIMAL(10,2),
    IN p_categoria   VARCHAR(20)
)
BEGIN
    UPDATE servicios_catalogo
    SET nombre = p_nombre, descripcion = p_descripcion,
        precio = p_precio, categoria = p_categoria
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_servicios_catalogo_toggleActivo$$
CREATE PROCEDURE sp_servicios_catalogo_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE servicios_catalogo SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_ordenes_servicio_findAll$$
CREATE PROCEDURE sp_ordenes_servicio_findAll()
BEGIN
    SELECT os.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
           u.nombre AS recepcionista, t.nombre AS tecnico_nombre
    FROM ordenes_servicio os
    LEFT JOIN clientes c ON c.id = os.cliente_id
    LEFT JOIN users    u ON u.id = os.user_recepcion_id
    LEFT JOIN users    t ON t.id = os.tecnico_id
    ORDER BY os.fecha_recepcion DESC LIMIT 500;
END$$

DROP PROCEDURE IF EXISTS sp_ordenes_servicio_findById$$
CREATE PROCEDURE sp_ordenes_servicio_findById(IN p_id INT)
BEGIN
    SELECT os.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
           c.email AS cliente_email,
           u.nombre AS recepcionista, t.nombre AS tecnico_nombre
    FROM ordenes_servicio os
    LEFT JOIN clientes c ON c.id = os.cliente_id
    LEFT JOIN users    u ON u.id = os.user_recepcion_id
    LEFT JOIN users    t ON t.id = os.tecnico_id
    WHERE os.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_ordenes_servicio_findByEstado$$
CREATE PROCEDURE sp_ordenes_servicio_findByEstado(IN p_estado VARCHAR(40))
BEGIN
    SELECT os.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM ordenes_servicio os
    LEFT JOIN clientes c ON c.id = os.cliente_id
    WHERE os.estado = p_estado
    ORDER BY os.fecha_recepcion DESC;
END$$

DROP PROCEDURE IF EXISTS sp_ordenes_servicio_existeCodigo$$
CREATE PROCEDURE sp_ordenes_servicio_existeCodigo(IN p_codigo VARCHAR(20))
BEGIN
    SELECT COUNT(*) AS total FROM ordenes_servicio WHERE codigo = p_codigo;
END$$

DROP PROCEDURE IF EXISTS sp_ordenes_servicio_insert$$
CREATE PROCEDURE sp_ordenes_servicio_insert(
    IN p_codigo               VARCHAR(20),
    IN p_cliente_id           INT,
    IN p_user_recepcion_id    INT,
    IN p_equipo_descripcion   VARCHAR(255),
    IN p_serial               VARCHAR(120),
    IN p_accesorios           VARCHAR(255),
    IN p_diagnostico_inicial  TEXT
)
BEGIN
    INSERT INTO ordenes_servicio (codigo, cliente_id, user_recepcion_id,
        equipo_descripcion, serial, accesorios_entregados, diagnostico_inicial, estado)
    VALUES (p_codigo, p_cliente_id, p_user_recepcion_id,
            p_equipo_descripcion, p_serial, p_accesorios, p_diagnostico_inicial, 'Recibido');

    INSERT INTO servicio_historial (orden_id, estado_anterior, estado_nuevo, user_id, motivo)
    VALUES (LAST_INSERT_ID(), NULL, 'Recibido', p_user_recepcion_id, 'Equipo recibido en tienda');

    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_ordenes_servicio_updateEstado$$
CREATE PROCEDURE sp_ordenes_servicio_updateEstado(
    IN p_id      INT,
    IN p_estado  VARCHAR(40),
    IN p_user_id INT,
    IN p_motivo  VARCHAR(255)
)
BEGIN
    DECLARE v_estado_anterior VARCHAR(40);
    SELECT estado INTO v_estado_anterior FROM ordenes_servicio WHERE id = p_id LIMIT 1;

    UPDATE ordenes_servicio SET estado = p_estado WHERE id = p_id;
    IF p_estado = 'Entregado' THEN
        UPDATE ordenes_servicio SET fecha_entrega = NOW() WHERE id = p_id;
    END IF;

    INSERT INTO servicio_historial (orden_id, estado_anterior, estado_nuevo, user_id, motivo)
    VALUES (p_id, v_estado_anterior, p_estado, p_user_id, p_motivo);
END$$

DROP PROCEDURE IF EXISTS sp_ordenes_servicio_asignarTecnico$$
CREATE PROCEDURE sp_ordenes_servicio_asignarTecnico(IN p_id INT, IN p_tecnico_id INT)
BEGIN
    UPDATE ordenes_servicio SET tecnico_id = p_tecnico_id WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_ordenes_servicio_recalcularTotales$$
CREATE PROCEDURE sp_ordenes_servicio_recalcularTotales(IN p_id INT)
BEGIN
    UPDATE ordenes_servicio os
    SET total_actual = (SELECT COALESCE(SUM(subtotal),0) FROM orden_servicio_items
                         WHERE orden_id = p_id AND aprobado_cliente = 1),
        total_pagado = (SELECT COALESCE(SUM(monto),0) FROM orden_servicio_pagos
                         WHERE orden_id = p_id)
    WHERE os.id = p_id;
    UPDATE ordenes_servicio SET saldo = total_actual - total_pagado WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_orden_servicio_items_findByOrden$$
CREATE PROCEDURE sp_orden_servicio_items_findByOrden(IN p_orden_id INT)
BEGIN
    SELECT osi.*, sc.nombre AS servicio_nombre, sc.categoria AS servicio_categoria,
           u.nombre AS agregado_por_nombre
    FROM orden_servicio_items osi
    LEFT JOIN servicios_catalogo sc ON sc.id = osi.servicio_catalogo_id
    LEFT JOIN users u ON u.id = osi.agregado_por
    WHERE osi.orden_id = p_orden_id
    ORDER BY osi.agregado_en;
END$$

DROP PROCEDURE IF EXISTS sp_orden_servicio_items_insert$$
CREATE PROCEDURE sp_orden_servicio_items_insert(
    IN p_orden_id        INT,
    IN p_tipo            VARCHAR(30),
    IN p_servicio_cat_id INT,
    IN p_descripcion     VARCHAR(255),
    IN p_cantidad        INT,
    IN p_precio_unit     DECIMAL(10,2),
    IN p_subtotal        DECIMAL(10,2),
    IN p_dias_garantia   INT,
    IN p_agregado_por    INT
)
BEGIN
    INSERT INTO orden_servicio_items (orden_id, tipo, servicio_catalogo_id,
        descripcion, cantidad, precio_unitario, subtotal, dias_garantia, agregado_por)
    VALUES (p_orden_id, p_tipo, p_servicio_cat_id, p_descripcion, p_cantidad,
            p_precio_unit, p_subtotal, p_dias_garantia, p_agregado_por);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_orden_servicio_items_aprobar$$
CREATE PROCEDURE sp_orden_servicio_items_aprobar(IN p_id INT, IN p_aprobado TINYINT)
BEGIN
    UPDATE orden_servicio_items SET aprobado_cliente = p_aprobado WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_orden_servicio_items_delete$$
CREATE PROCEDURE sp_orden_servicio_items_delete(IN p_id INT)
BEGIN
    DELETE FROM orden_servicio_items WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_orden_servicio_pagos_insert$$
CREATE PROCEDURE sp_orden_servicio_pagos_insert(
    IN p_orden_id       INT,
    IN p_tipo           VARCHAR(20),
    IN p_monto          DECIMAL(10,2),
    IN p_metodo         VARCHAR(20),
    IN p_caja_sesion_id INT,
    IN p_recibo_numero  VARCHAR(40),
    IN p_user_id        INT
)
BEGIN
    INSERT INTO orden_servicio_pagos (orden_id, tipo, monto, metodo,
        caja_sesion_id, recibo_numero, user_id)
    VALUES (p_orden_id, p_tipo, p_monto, p_metodo, p_caja_sesion_id, p_recibo_numero, p_user_id);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_orden_servicio_pagos_findByOrden$$
CREATE PROCEDURE sp_orden_servicio_pagos_findByOrden(IN p_orden_id INT)
BEGIN
    SELECT osp.*, u.nombre AS usuario_nombre
    FROM orden_servicio_pagos osp
    LEFT JOIN users u ON u.id = osp.user_id
    WHERE osp.orden_id = p_orden_id ORDER BY osp.fecha;
END$$

DROP PROCEDURE IF EXISTS sp_servicio_historial_findByOrden$$
CREATE PROCEDURE sp_servicio_historial_findByOrden(IN p_orden_id INT)
BEGIN
    SELECT sh.*, u.nombre AS usuario_nombre
    FROM servicio_historial sh
    LEFT JOIN users u ON u.id = sh.user_id
    WHERE sh.orden_id = p_orden_id ORDER BY sh.fecha DESC;
END$$

-- ============================================================
-- SPs CAMISETAS (módulo nuevo)
-- ============================================================

-- ─── TORNEOS (ligas + competiciones unificados) ───────

DROP PROCEDURE IF EXISTS sp_torneos_findAll$$
CREATE PROCEDURE sp_torneos_findAll()
BEGIN
    SELECT id, nombre, tipo, pais, logo_path, orden, activo
    FROM torneos ORDER BY orden, nombre;
END$$

DROP PROCEDURE IF EXISTS sp_torneos_findActivos$$
CREATE PROCEDURE sp_torneos_findActivos()
BEGIN
    SELECT id, nombre, tipo, pais, logo_path
    FROM torneos WHERE activo = 1 ORDER BY orden, nombre;
END$$

DROP PROCEDURE IF EXISTS sp_torneos_findByTipo$$
CREATE PROCEDURE sp_torneos_findByTipo(IN p_tipo VARCHAR(30))
BEGIN
    SELECT id, nombre, tipo, pais, logo_path
    FROM torneos WHERE tipo = p_tipo AND activo = 1 ORDER BY orden, nombre;
END$$

DROP PROCEDURE IF EXISTS sp_torneos_findById$$
CREATE PROCEDURE sp_torneos_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, tipo, pais, logo_path, orden, activo
    FROM torneos WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_torneos_insert$$
CREATE PROCEDURE sp_torneos_insert(
    IN p_nombre VARCHAR(120),
    IN p_tipo   VARCHAR(30),
    IN p_pais   VARCHAR(80),
    IN p_logo   VARCHAR(255),
    IN p_orden  INT
)
BEGIN
    INSERT INTO torneos (nombre, tipo, pais, logo_path, orden)
    VALUES (p_nombre, p_tipo, p_pais, p_logo, p_orden);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_torneos_update$$
CREATE PROCEDURE sp_torneos_update(
    IN p_id     INT,
    IN p_nombre VARCHAR(120),
    IN p_tipo   VARCHAR(30),
    IN p_pais   VARCHAR(80),
    IN p_logo   VARCHAR(255),
    IN p_orden  INT
)
BEGIN
    UPDATE torneos
    SET nombre = p_nombre, tipo = p_tipo, pais = p_pais,
        logo_path = COALESCE(p_logo, logo_path), orden = p_orden
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_torneos_toggleActivo$$
CREATE PROCEDURE sp_torneos_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE torneos SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_torneos_delete$$
CREATE PROCEDURE sp_torneos_delete(IN p_id INT)
BEGIN
    DELETE FROM torneos WHERE id = p_id;
END$$

-- ─── EQUIPOS ──────────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_equipos_findByTorneo$$
CREATE PROCEDURE sp_equipos_findByTorneo(IN p_torneo_id INT)
BEGIN
    SELECT id, torneo_id, nombre, escudo_path, orden, activo
    FROM equipos WHERE torneo_id = p_torneo_id AND activo = 1
    ORDER BY orden, nombre;
END$$

DROP PROCEDURE IF EXISTS sp_equipos_findAll$$
CREATE PROCEDURE sp_equipos_findAll()
BEGIN
    SELECT e.id, e.torneo_id, t.nombre AS torneo_nombre, e.nombre,
           e.escudo_path, e.orden, e.activo
    FROM equipos e
    INNER JOIN torneos t ON t.id = e.torneo_id
    ORDER BY t.orden, t.nombre, e.orden, e.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_equipos_findById$$
CREATE PROCEDURE sp_equipos_findById(IN p_id INT)
BEGIN
    SELECT e.id, e.torneo_id, t.nombre AS torneo_nombre,
           e.nombre, e.escudo_path, e.orden, e.activo
    FROM equipos e
    INNER JOIN torneos t ON t.id = e.torneo_id
    WHERE e.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_equipos_insert$$
CREATE PROCEDURE sp_equipos_insert(
    IN p_torneo_id INT,
    IN p_nombre    VARCHAR(120),
    IN p_escudo    VARCHAR(255),
    IN p_orden     INT
)
BEGIN
    INSERT INTO equipos (torneo_id, nombre, escudo_path, orden)
    VALUES (p_torneo_id, p_nombre, p_escudo, p_orden);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_equipos_update$$
CREATE PROCEDURE sp_equipos_update(
    IN p_id        INT,
    IN p_torneo_id INT,
    IN p_nombre    VARCHAR(120),
    IN p_escudo    VARCHAR(255),
    IN p_orden     INT
)
BEGIN
    UPDATE equipos
    SET torneo_id = p_torneo_id, nombre = p_nombre,
        escudo_path = COALESCE(p_escudo, escudo_path), orden = p_orden
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_equipos_toggleActivo$$
CREATE PROCEDURE sp_equipos_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE equipos SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_equipos_delete$$
CREATE PROCEDURE sp_equipos_delete(IN p_id INT)
BEGIN
    DELETE FROM equipos WHERE id = p_id;
END$$

-- ─── EQUIPO ↔ COMPETICIÓN ─────────────────────────────

DROP PROCEDURE IF EXISTS sp_equipo_competicion_findByEquipo$$
CREATE PROCEDURE sp_equipo_competicion_findByEquipo(IN p_equipo_id INT)
BEGIN
    SELECT c.id, c.nombre, c.parche_path, c.precio_extra
    FROM equipo_competicion ec
    INNER JOIN competiciones c ON c.id = ec.competicion_id
    WHERE ec.equipo_id = p_equipo_id AND c.activo = 1
    ORDER BY c.nombre;
END$$

DROP PROCEDURE IF EXISTS sp_equipo_competicion_assign$$
CREATE PROCEDURE sp_equipo_competicion_assign(IN p_equipo_id INT, IN p_comp_id INT)
BEGIN
    INSERT IGNORE INTO equipo_competicion (equipo_id, competicion_id)
    VALUES (p_equipo_id, p_comp_id);
END$$

DROP PROCEDURE IF EXISTS sp_equipo_competicion_revoke$$
CREATE PROCEDURE sp_equipo_competicion_revoke(IN p_equipo_id INT, IN p_comp_id INT)
BEGIN
    DELETE FROM equipo_competicion
    WHERE equipo_id = p_equipo_id AND competicion_id = p_comp_id;
END$$

DROP PROCEDURE IF EXISTS sp_equipo_competicion_sync$$
CREATE PROCEDURE sp_equipo_competicion_sync(IN p_equipo_id INT)
BEGIN
    -- El equipo conserva su lista; este SP solo limpia para que la app
    -- inserte nuevamente la lista actualizada vía sp_equipo_competicion_assign.
    DELETE FROM equipo_competicion WHERE equipo_id = p_equipo_id;
END$$

DROP PROCEDURE IF EXISTS sp_temporadas_findAll$$
CREATE PROCEDURE sp_temporadas_findAll()
BEGIN
    SELECT t.*, ct.fecha_inicio, ct.fecha_fin, ct.abierta, ct.lote_exportado_at
    FROM temporadas t
    LEFT JOIN config_temporadas ct ON ct.temporada_id = t.id
    ORDER BY t.anio_inicio DESC;
END$$

DROP PROCEDURE IF EXISTS sp_temporadas_findActivas$$
CREATE PROCEDURE sp_temporadas_findActivas()
BEGIN
    SELECT t.*, ct.fecha_inicio, ct.fecha_fin, ct.abierta
    FROM temporadas t
    LEFT JOIN config_temporadas ct ON ct.temporada_id = t.id
    WHERE t.activo = 1 ORDER BY t.anio_inicio DESC;
END$$

DROP PROCEDURE IF EXISTS sp_temporadas_findAbiertas$$
CREATE PROCEDURE sp_temporadas_findAbiertas()
BEGIN
    SELECT t.id, t.nombre, t.anio_inicio, t.anio_fin, ct.fecha_inicio, ct.fecha_fin
    FROM temporadas t
    INNER JOIN config_temporadas ct ON ct.temporada_id = t.id
    WHERE t.activo = 1 AND ct.abierta = 1 AND CURDATE() BETWEEN ct.fecha_inicio AND ct.fecha_fin
    ORDER BY t.anio_inicio DESC;
END$$

DROP PROCEDURE IF EXISTS sp_temporadas_cerrarVencidas$$
CREATE PROCEDURE sp_temporadas_cerrarVencidas()
BEGIN
    UPDATE config_temporadas SET abierta = 0 WHERE fecha_fin < CURDATE() AND abierta = 1;
END$$

DROP PROCEDURE IF EXISTS sp_tipos_equipacion_findActivos$$
CREATE PROCEDURE sp_tipos_equipacion_findActivos()
BEGIN
    SELECT id, nombre, orden FROM tipos_equipacion WHERE activo = 1 ORDER BY orden, nombre;
END$$

-- ─── TALLAS (3 tablas separadas) ──────────────────────

DROP PROCEDURE IF EXISTS sp_tallas_hombre_findActivas$$
CREATE PROCEDURE sp_tallas_hombre_findActivas()
BEGIN
    SELECT id, nombre, orden FROM tallas_hombre WHERE activo = 1 ORDER BY orden;
END$$

DROP PROCEDURE IF EXISTS sp_tallas_mujer_findActivas$$
CREATE PROCEDURE sp_tallas_mujer_findActivas()
BEGIN
    SELECT id, nombre, orden FROM tallas_mujer WHERE activo = 1 ORDER BY orden;
END$$

DROP PROCEDURE IF EXISTS sp_tallas_infantil_findActivas$$
CREATE PROCEDURE sp_tallas_infantil_findActivas()
BEGIN
    SELECT id, nombre, orden FROM tallas_infantil WHERE activo = 1 ORDER BY orden;
END$$

-- ─── COMPETICIONES (parches) ──────────────────────────

DROP PROCEDURE IF EXISTS sp_competiciones_findActivas$$
CREATE PROCEDURE sp_competiciones_findActivas()
BEGIN
    SELECT id, nombre, parche_path, precio_extra FROM competiciones WHERE activo = 1 ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_competiciones_findAll$$
CREATE PROCEDURE sp_competiciones_findAll()
BEGIN
    SELECT id, nombre, parche_path, precio_extra, activo FROM competiciones ORDER BY nombre;
END$$

DROP PROCEDURE IF EXISTS sp_competiciones_findById$$
CREATE PROCEDURE sp_competiciones_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, parche_path, precio_extra, activo FROM competiciones WHERE id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_competiciones_insert$$
CREATE PROCEDURE sp_competiciones_insert(IN p_nombre VARCHAR(120), IN p_parche VARCHAR(255), IN p_precio DECIMAL(10,2))
BEGIN
    INSERT INTO competiciones (nombre, parche_path, precio_extra)
    VALUES (p_nombre, p_parche, p_precio);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_competiciones_update$$
CREATE PROCEDURE sp_competiciones_update(IN p_id INT, IN p_nombre VARCHAR(120), IN p_parche VARCHAR(255), IN p_precio DECIMAL(10,2))
BEGIN
    UPDATE competiciones SET nombre = p_nombre,
        parche_path = COALESCE(p_parche, parche_path),
        precio_extra = p_precio
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_competiciones_toggleActivo$$
CREATE PROCEDURE sp_competiciones_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE competiciones SET activo = p_activo WHERE id = p_id;
END$$

-- ─── EQUIPACIONES ─────────────────────────────────────

DROP PROCEDURE IF EXISTS sp_equipaciones_findByEquipoVersion$$
CREATE PROCEDURE sp_equipaciones_findByEquipoVersion(
    IN p_equipo_id    INT,
    IN p_version      VARCHAR(20),
    IN p_temporada_id INT
)
BEGIN
    -- Si p_temporada_id es NULL/0 trae todas las temporadas de ese equipo+versión.
    -- Si p_version es NULL/'' no filtra por versión.
    SELECT e.*, te.nombre AS tipo_nombre, t.nombre AS temporada_nombre
    FROM equipaciones e
    INNER JOIN tipos_equipacion te ON te.id = e.tipo_equipacion_id
    INNER JOIN temporadas       t  ON t.id  = e.temporada_id
    WHERE e.equipo_id = p_equipo_id
      AND e.activo = 1
      AND (p_version IS NULL OR p_version = '' OR e.version = p_version)
      AND (p_temporada_id IS NULL OR p_temporada_id = 0 OR e.temporada_id = p_temporada_id)
    ORDER BY t.anio_inicio DESC, te.orden;
END$$

DROP PROCEDURE IF EXISTS sp_equipaciones_findById$$
CREATE PROCEDURE sp_equipaciones_findById(IN p_id INT)
BEGIN
    SELECT e.*, te.nombre AS tipo_nombre, t.nombre AS temporada_nombre,
           eq.nombre AS equipo_nombre, eq.escudo_path,
           tn.nombre AS torneo_nombre, tn.logo_path AS torneo_logo
    FROM equipaciones e
    INNER JOIN tipos_equipacion te ON te.id = e.tipo_equipacion_id
    INNER JOIN temporadas       t  ON t.id  = e.temporada_id
    INNER JOIN equipos          eq ON eq.id = e.equipo_id
    INNER JOIN torneos          tn ON tn.id = eq.torneo_id
    WHERE e.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_equipaciones_findAll$$
CREATE PROCEDURE sp_equipaciones_findAll()
BEGIN
    SELECT e.id, e.equipo_id, eq.nombre AS equipo_nombre,
           e.temporada_id, t.nombre AS temporada_nombre,
           e.tipo_equipacion_id, te.nombre AS tipo_nombre,
           e.version, e.imagen_path, e.precio_base, e.activo
    FROM equipaciones e
    INNER JOIN equipos          eq ON eq.id = e.equipo_id
    INNER JOIN temporadas       t  ON t.id  = e.temporada_id
    INNER JOIN tipos_equipacion te ON te.id = e.tipo_equipacion_id
    ORDER BY eq.nombre, t.anio_inicio DESC, te.orden;
END$$

DROP PROCEDURE IF EXISTS sp_equipaciones_insert$$
CREATE PROCEDURE sp_equipaciones_insert(
    IN p_equipo_id    INT,
    IN p_temporada_id INT,
    IN p_tipo_id      INT,
    IN p_version      VARCHAR(20),
    IN p_imagen       VARCHAR(255),
    IN p_precio       DECIMAL(10,2)
)
BEGIN
    INSERT INTO equipaciones (equipo_id, temporada_id, tipo_equipacion_id, version, imagen_path, precio_base)
    VALUES (p_equipo_id, p_temporada_id, p_tipo_id, p_version, p_imagen, p_precio);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_equipaciones_update$$
CREATE PROCEDURE sp_equipaciones_update(
    IN p_id           INT,
    IN p_equipo_id    INT,
    IN p_temporada_id INT,
    IN p_tipo_id      INT,
    IN p_version      VARCHAR(20),
    IN p_imagen       VARCHAR(255),
    IN p_precio       DECIMAL(10,2)
)
BEGIN
    UPDATE equipaciones
    SET equipo_id = p_equipo_id, temporada_id = p_temporada_id,
        tipo_equipacion_id = p_tipo_id, version = p_version,
        imagen_path = COALESCE(p_imagen, imagen_path),
        precio_base = p_precio
    WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_equipaciones_toggleActivo$$
CREATE PROCEDURE sp_equipaciones_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE equipaciones SET activo = p_activo WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_precios_extras_camisa_findAll$$
CREATE PROCEDURE sp_precios_extras_camisa_findAll()
BEGIN
    SELECT id, concepto, precio FROM precios_extras_camisa WHERE activo = 1;
END$$

DROP PROCEDURE IF EXISTS sp_config_camisetas_get$$
CREATE PROCEDURE sp_config_camisetas_get()
BEGIN
    SELECT * FROM config_camisetas WHERE id = 1 LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_findAll$$
CREATE PROCEDURE sp_pedidos_camiseta_findAll()
BEGIN
    SELECT pc.*, c.nombre AS cliente_nombre, t.nombre AS temporada_nombre
    FROM pedidos_camiseta pc
    LEFT JOIN clientes c   ON c.id = pc.cliente_id
    LEFT JOIN temporadas t ON t.id = pc.temporada_id
    ORDER BY pc.created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_findByTemporada$$
CREATE PROCEDURE sp_pedidos_camiseta_findByTemporada(IN p_temp_id INT, IN p_estado VARCHAR(20))
BEGIN
    SELECT pc.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM pedidos_camiseta pc
    LEFT JOIN clientes c ON c.id = pc.cliente_id
    WHERE pc.temporada_id = p_temp_id
      AND (p_estado IS NULL OR pc.estado = p_estado)
    ORDER BY pc.created_at;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_findById$$
CREATE PROCEDURE sp_pedidos_camiseta_findById(IN p_id INT)
BEGIN
    SELECT pc.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
           c.email AS cliente_email, t.nombre AS temporada_nombre
    FROM pedidos_camiseta pc
    LEFT JOIN clientes c   ON c.id = pc.cliente_id
    LEFT JOIN temporadas t ON t.id = pc.temporada_id
    WHERE pc.id = p_id LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_existeCodigo$$
CREATE PROCEDURE sp_pedidos_camiseta_existeCodigo(IN p_codigo VARCHAR(20))
BEGIN
    SELECT COUNT(*) AS total FROM pedidos_camiseta WHERE codigo = p_codigo;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_insert$$
CREATE PROCEDURE sp_pedidos_camiseta_insert(
    IN p_codigo        VARCHAR(20),
    IN p_cliente_id    INT,
    IN p_temporada_id  INT,
    IN p_subtotal      DECIMAL(10,2),
    IN p_total         DECIMAL(10,2),
    IN p_nota          VARCHAR(500)
)
BEGIN
    INSERT INTO pedidos_camiseta (codigo, cliente_id, temporada_id, subtotal, total,
                                   saldo, anticipo_pagado, nota)
    VALUES (p_codigo, p_cliente_id, p_temporada_id, p_subtotal, p_total, p_total, 0, p_nota);
    SELECT LAST_INSERT_ID() AS id;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_insertDetalle$$
CREATE PROCEDURE sp_pedidos_camiseta_insertDetalle(
    IN p_pedido_id        INT,
    IN p_equipacion_id    INT,
    IN p_talla_hombre_id  INT,
    IN p_talla_mujer_id   INT,
    IN p_talla_infantil_id INT,
    IN p_nombre_pers      VARCHAR(40),
    IN p_numero_pers      VARCHAR(5),
    IN p_competicion_id   INT,
    IN p_precio_unitario  DECIMAL(10,2),
    IN p_precio_extras    DECIMAL(10,2),
    IN p_cantidad         INT,
    IN p_subtotal         DECIMAL(10,2)
)
BEGIN
    -- Sólo una de las tres tallas debe venir con valor (CHECK lo valida).
    INSERT INTO pedido_camiseta_detalle (
        pedido_id, equipacion_id,
        talla_hombre_id, talla_mujer_id, talla_infantil_id,
        nombre_personalizado, numero_personalizado, competicion_id,
        precio_unitario, precio_extras, cantidad, subtotal)
    VALUES (
        p_pedido_id, p_equipacion_id,
        p_talla_hombre_id, p_talla_mujer_id, p_talla_infantil_id,
        p_nombre_pers, p_numero_pers, p_competicion_id,
        p_precio_unitario, p_precio_extras, p_cantidad, p_subtotal);
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_findDetalle$$
CREATE PROCEDURE sp_pedidos_camiseta_findDetalle(IN p_pedido_id INT)
BEGIN
    SELECT pcd.*,
           eq.version AS version,
           e.nombre   AS equipo_nombre, e.escudo_path,
           tn.nombre  AS torneo_nombre, tn.logo_path AS torneo_logo,
           t.nombre   AS temporada_nombre,
           te.nombre  AS tipo_equipacion,
           COALESCE(th.nombre, tm.nombre, ti.nombre) AS talla_nombre,
           CASE
               WHEN pcd.talla_hombre_id   IS NOT NULL THEN 'hombre'
               WHEN pcd.talla_mujer_id    IS NOT NULL THEN 'mujer'
               WHEN pcd.talla_infantil_id IS NOT NULL THEN 'infantil'
           END AS talla_tipo,
           co.nombre  AS competicion_nombre, co.parche_path
    FROM pedido_camiseta_detalle pcd
    INNER JOIN equipaciones    eq ON eq.id = pcd.equipacion_id
    INNER JOIN equipos          e ON e.id  = eq.equipo_id
    INNER JOIN torneos         tn ON tn.id = e.torneo_id
    INNER JOIN temporadas       t ON t.id  = eq.temporada_id
    INNER JOIN tipos_equipacion te ON te.id = eq.tipo_equipacion_id
    LEFT  JOIN tallas_hombre   th ON th.id = pcd.talla_hombre_id
    LEFT  JOIN tallas_mujer    tm ON tm.id = pcd.talla_mujer_id
    LEFT  JOIN tallas_infantil ti ON ti.id = pcd.talla_infantil_id
    LEFT  JOIN competiciones   co ON co.id = pcd.competicion_id
    WHERE pcd.pedido_id = p_pedido_id;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_registrarAnticipo$$
CREATE PROCEDURE sp_pedidos_camiseta_registrarAnticipo(IN p_id INT, IN p_monto DECIMAL(10,2))
BEGIN
    UPDATE pedidos_camiseta
    SET anticipo_pagado = anticipo_pagado + p_monto,
        saldo = saldo - p_monto,
        estado = 'Confirmado'
    WHERE id = p_id AND estado = 'Pendiente_pago';
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_updateEstado$$
CREATE PROCEDURE sp_pedidos_camiseta_updateEstado(IN p_id INT, IN p_estado VARCHAR(20))
BEGIN
    UPDATE pedidos_camiseta SET estado = p_estado WHERE id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_pedidos_camiseta_exportar$$
CREATE PROCEDURE sp_pedidos_camiseta_exportar(IN p_temporada_id INT)
BEGIN
    -- Vista plana para export Excel/PDF al proveedor
    SELECT pc.codigo AS pedido_codigo,
           c.nombre AS cliente, c.telefono,
           t.nombre AS temporada,
           tn.nombre AS torneo, tn.tipo AS torneo_tipo,
           e.nombre  AS equipo,
           eq.version AS version,
           te.nombre AS equipacion,
           COALESCE(th.nombre, tm.nombre, ti.nombre) AS talla,
           CASE
               WHEN pcd.talla_hombre_id   IS NOT NULL THEN 'hombre'
               WHEN pcd.talla_mujer_id    IS NOT NULL THEN 'mujer'
               WHEN pcd.talla_infantil_id IS NOT NULL THEN 'infantil'
           END AS talla_tipo,
           pcd.nombre_personalizado AS nombre_jugador,
           pcd.numero_personalizado AS numero_jugador,
           co.nombre AS competicion,
           pcd.cantidad, pcd.subtotal
    FROM pedidos_camiseta pc
    INNER JOIN pedido_camiseta_detalle pcd ON pcd.pedido_id = pc.id
    INNER JOIN clientes c                  ON c.id  = pc.cliente_id
    INNER JOIN temporadas t                ON t.id  = pc.temporada_id
    INNER JOIN equipaciones eq             ON eq.id = pcd.equipacion_id
    INNER JOIN equipos e                   ON e.id  = eq.equipo_id
    INNER JOIN torneos tn                  ON tn.id = e.torneo_id
    INNER JOIN tipos_equipacion te         ON te.id = eq.tipo_equipacion_id
    LEFT  JOIN tallas_hombre th            ON th.id = pcd.talla_hombre_id
    LEFT  JOIN tallas_mujer  tm            ON tm.id = pcd.talla_mujer_id
    LEFT  JOIN tallas_infantil ti          ON ti.id = pcd.talla_infantil_id
    LEFT  JOIN competiciones co            ON co.id = pcd.competicion_id
    WHERE pc.temporada_id = p_temporada_id AND pc.estado = 'Confirmado'
    ORDER BY tn.nombre, e.nombre, eq.version, te.nombre;
END$$

DELIMITER ;

-- ============================================================
-- ============================================================
--   SEEDS — datos mínimos para arrancar el sistema
-- ============================================================
-- ============================================================

-- ─────────────────────────────────────────────────
-- ROLES
-- ─────────────────────────────────────────────────
INSERT INTO roles (id, nombre, slug, descripcion, activo) VALUES
    (1, 'Administrador', 'admin',    'Acceso total al sistema', 1),
    (2, 'Cajero',        'cajero',   'Operación de caja y atención al cliente', 1),
    (3, 'Técnico',       'tecnico',  'Servicio técnico de equipos', 1);

-- ─────────────────────────────────────────────────
-- PERMISOS BASE (formato modulo.accion)
-- ─────────────────────────────────────────────────
INSERT INTO permissions (nombre, slug, modulo, descripcion) VALUES
    ('Ver dashboard',          'dashboard.ver',          'dashboard',   'Acceso al panel principal'),
    ('Ver usuarios',           'usuarios.ver',           'usuarios',    NULL),
    ('Crear usuarios',         'usuarios.crear',         'usuarios',    NULL),
    ('Editar usuarios',        'usuarios.editar',        'usuarios',    NULL),
    ('Eliminar usuarios',      'usuarios.eliminar',      'usuarios',    NULL),
    ('Ver roles',              'roles.ver',              'roles',       NULL),
    ('Gestionar roles',        'roles.gestionar',        'roles',       'Crear, editar, asignar permisos'),
    ('Ver clientes',           'clientes.ver',           'clientes',    NULL),
    ('Crear clientes',         'clientes.crear',         'clientes',    NULL),
    ('Editar clientes',        'clientes.editar',        'clientes',    NULL),
    ('Ver productos',          'productos.ver',          'productos',   NULL),
    ('Crear productos',        'productos.crear',        'productos',   NULL),
    ('Editar productos',       'productos.editar',       'productos',   NULL),
    ('Eliminar productos',     'productos.eliminar',     'productos',   NULL),
    ('Ver categorías',         'categorias.ver',         'categorias',  NULL),
    ('Gestionar categorías',   'categorias.gestionar',   'categorias',  NULL),
    ('Ver ventas',             'ventas.ver',             'ventas',      NULL),
    ('Crear ventas',           'ventas.crear',           'ventas',      'Operar caja y registrar ventas'),
    ('Anular ventas',          'ventas.anular',          'ventas',      NULL),
    ('Ver pedidos',            'pedidos.ver',            'pedidos',     NULL),
    ('Gestionar pedidos',      'pedidos.gestionar',      'pedidos',     'Cambiar estado, confirmar pago'),
    ('Configurar tienda',      'tienda.configurar',      'tienda',      'Banners, zonas, descuentos'),
    ('Ver facturas',           'facturacion.ver',        'facturacion', NULL),
    ('Configurar facturación', 'facturacion.configurar', 'facturacion', 'RTN, CAI, correlativos'),
    ('Ver reportes',           'reportes.ver',           'reportes',    NULL),
    ('Ver servicio técnico',   'servicio.ver',           'servicio',    NULL),
    ('Recibir órdenes',        'servicio.recibir',       'servicio',    'Crear nueva orden de servicio'),
    ('Diagnosticar',           'servicio.diagnosticar',  'servicio',    'Crear diagnóstico y presupuesto'),
    ('Aprobar/cancelar',       'servicio.aprobar',       'servicio',    'Aprobar items, cancelar orden'),
    ('Entregar equipo',        'servicio.entregar',      'servicio',    'Marcar entregado y cobrar saldo'),
    ('Gestionar catálogo ST',  'servicio.catalogo',      'servicio',    'CRUD de servicios_catalogo'),
    ('Ver camisetas',          'camisetas.ver',          'camisetas',   NULL),
    ('Crear pedido camiseta',  'camisetas.crear',        'camisetas',   NULL),
    ('Gestionar catálogos',    'camisetas.catalogo',     'camisetas',   'Torneos, equipos, temporadas, competiciones'),
    ('Exportar lote',          'camisetas.exportar',     'camisetas',   'Generar Excel/PDF al proveedor'),
    ('Cerrar temporada',       'camisetas.cerrar',       'camisetas',   NULL);

-- ─────────────────────────────────────────────────
-- ASIGNACIÓN ROL → PERMISOS
-- ─────────────────────────────────────────────────
-- Admin: TODOS
INSERT INTO rol_permisos (rol_id, permission_id) SELECT 1, id FROM permissions;

-- Cajero
INSERT INTO rol_permisos (rol_id, permission_id)
SELECT 2, id FROM permissions
WHERE slug IN ('dashboard.ver','clientes.ver','clientes.crear',
               'productos.ver','ventas.ver','ventas.crear',
               'pedidos.ver','pedidos.gestionar',
               'facturacion.ver','servicio.ver','servicio.recibir',
               'camisetas.ver','camisetas.crear');

-- Técnico
INSERT INTO rol_permisos (rol_id, permission_id)
SELECT 3, id FROM permissions
WHERE slug IN ('dashboard.ver','clientes.ver',
               'servicio.ver','servicio.diagnosticar','servicio.aprobar','servicio.entregar');

-- ─────────────────────────────────────────────────
-- USUARIO ADMIN INICIAL
--   user:  admin    /  pass:  admin123    (cambiar después del primer login)
-- ─────────────────────────────────────────────────
INSERT INTO users (nombre, username, email, password, rol_id, activo) VALUES (
    'Administrador',
    'admin',
    'admin@zonamarcol.local',
    '$2y$10$Lr1XTgyjRO9oeFvqeKrited.K6bo7GVxgLCJmrjDSBired8ZPcBeC',
    1,
    1
);

-- ─────────────────────────────────────────────────
-- CATÁLOGOS BASE DE CAMISETAS
-- ─────────────────────────────────────────────────
INSERT INTO tipos_equipacion (nombre, orden) VALUES
    ('Local',            1),
    ('Visitante',        2),
    ('Tercera',          3),
    ('Portero',          4),
    ('Edición especial', 5);

-- Tallas HOMBRE
INSERT INTO tallas_hombre (nombre, orden) VALUES
    ('XS',  1), ('S',  2), ('M',  3), ('L',  4),
    ('XL',  5), ('XXL', 6), ('3XL', 7);

-- Tallas MUJER
INSERT INTO tallas_mujer (nombre, orden) VALUES
    ('XS', 1), ('S',  2), ('M',  3), ('L',  4), ('XL', 5);

-- Tallas INFANTIL
INSERT INTO tallas_infantil (nombre, orden) VALUES
    ('2',  1), ('4',  2), ('6',  3), ('8',  4),
    ('10', 5), ('12', 6), ('14', 7), ('16', 8);

-- Torneos base (logos se cargan luego desde panel admin)
INSERT INTO torneos (nombre, tipo, pais, logo_path, orden) VALUES
    ('LaLiga',                 'liga_club',        'España',       '', 1),
    ('Premier League',         'liga_club',        'Inglaterra',   '', 2),
    ('Bundesliga',             'liga_club',        'Alemania',     '', 3),
    ('Serie A',                'liga_club',        'Italia',       '', 4),
    ('Ligue 1',                'liga_club',        'Francia',      '', 5),
    ('Liga Nacional Honduras', 'liga_club',        'Honduras',     '', 6),
    ('Liga MX',                'liga_club',        'México',       '', 7),
    ('MLS',                    'liga_club',        'EEUU',         '', 8),
    ('Mundial FIFA',           'seleccion',        NULL,           '', 20),
    ('Copa América',           'copa_continental', NULL,           '', 21),
    ('Eurocopa',               'copa_continental', NULL,           '', 22),
    ('Copa Oro CONCACAF',      'copa_continental', NULL,           '', 23);

-- Competiciones (parches)
INSERT INTO competiciones (nombre, parche_path, precio_extra) VALUES
    ('LaLiga',               '', 100.00),
    ('Premier League',       '', 100.00),
    ('Bundesliga',           '', 100.00),
    ('Serie A',              '', 100.00),
    ('UEFA Champions League','', 150.00),
    ('UEFA Europa League',   '', 120.00),
    ('Copa del Rey',         '', 100.00),
    ('Mundial FIFA',         '', 200.00),
    ('Copa América',         '', 150.00),
    ('Eurocopa',             '', 150.00),
    ('Copa Oro',             '', 120.00),
    ('Sin parche',           '',   0.00);

-- Personalizaciones extra (nombre, número, parche)
INSERT INTO precios_extras_camisa (concepto, precio) VALUES
    ('nombre',           80.00),
    ('numero',           50.00),
    ('nombre_y_numero', 120.00),
    ('parche',          100.00);

INSERT INTO config_camisetas (id, anticipo_pct, proveedor, correo_proveedor, nota_export)
VALUES (1, 50, NULL, NULL,
        'Lote de camisetas Zona Marcol. Validar tallas y personalizaciones antes de producción.');

-- ─────────────────────────────────────────────────
-- CATÁLOGO INICIAL SERVICIO TÉCNICO
-- ─────────────────────────────────────────────────
INSERT INTO servicios_catalogo (nombre, descripcion, precio, categoria) VALUES
    ('Limpieza interna PC',           'Desempolvado, pasta térmica y mantenimiento de ventiladores', 350.00, 'limpieza'),
    ('Limpieza interna laptop',       'Desensamble, limpieza y cambio de pasta térmica',             450.00, 'limpieza'),
    ('Limpieza interna PlayStation',  'PS4/PS5: limpieza completa de disipador y ventilador',         500.00, 'limpieza'),
    ('Limpieza interna Xbox',         'Xbox One/Series: limpieza completa',                           500.00, 'limpieza'),
    ('Limpieza control de consola',   'Limpieza interna de joystick (analógicos, gatillos, botones)', 200.00, 'limpieza'),
    ('Diagnóstico general PC',        'Pruebas de hardware y software para identificar fallas',       150.00, 'diagnostico'),
    ('Diagnóstico consola',           'Pruebas HDMI, lector, ventilación',                            200.00, 'diagnostico'),
    ('Reinstalación de Windows',      'Formateo y reinstalación con drivers',                         600.00, 'reparacion'),
    ('Cambio de pasta térmica',       'PC/laptop/consola — incluye pasta térmica premium',            250.00, 'reparacion'),
    ('Reparación de control',         'Cambio de analógico/gatillo/botón en joystick',                350.00, 'reparacion');

-- ─────────────────────────────────────────────────
-- FACTURACIÓN base (editable desde panel)
-- ─────────────────────────────────────────────────
INSERT INTO facturacion_config (id, rtn, cai, rango_desde, rango_hasta, fecha_limite,
                                 establecimiento, punto_emision, nombre_fiscal, direccion_fiscal, correlativo)
VALUES (1, NULL, NULL, NULL, NULL, NULL, '000', '001',
        'ZONA MARCOL', 'Honduras', 0);

-- ─────────────────────────────────────────────────
-- ZONAS DE ENVÍO
-- ─────────────────────────────────────────────────
INSERT INTO zonas_envio (nombre, costo) VALUES
    ('Retiro en tienda',          0.00),
    ('Tegucigalpa centro',       50.00),
    ('Tegucigalpa periferia',    80.00),
    ('Comayagüela',              60.00),
    ('Valle de Ángeles',        150.00);

-- ─────────────────────────────────────────────────
-- CATEGORÍAS PRODUCTOS
-- ─────────────────────────────────────────────────
INSERT INTO categorias (nombre, descripcion) VALUES
    ('Cuidado facial masculino', 'Limpiadores, hidratantes, antiedad para hombre'),
    ('Cuidado capilar',          'Champú, gel, ceras, productos para barba'),
    ('Accesorios tecnológicos',  'Cables, cargadores, soportes, periféricos'),
    ('Audio',                    'Audífonos, parlantes, micrófonos'),
    ('Gaming',                   'Controles, headsets, alfombrillas, accesorios gamer'),
    ('Repuestos PC',             'RAM, almacenamiento, ventiladores, fuentes');

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
--  Credenciales iniciales:
--    URL:   http://localhost:9012/ZonaMarcol/
--    User:  admin    /   Pass:  admin123    (CAMBIAR EN PRIMER LOGIN)
--
--  Verificación post-instalación:
--    SELECT COUNT(*) FROM information_schema.tables   WHERE table_schema='zonamarcol';
--    SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema='zonamarcol';
-- ============================================================
