-- ============================================================
--  zonamarcol_fix_codigo_barras.sql
--  ZonaMarcol — DeskCod
-- ------------------------------------------------------------
--  PROPÓSITO — Re-CREATE de los SPs de productos y variantes
--  para garantizar que la BD de producción acepte el parámetro
--  `codigo_barras`. Si en algún despliegue se aplicaron versiones
--  más viejas de los SPs (sin ese parámetro), las llamadas del
--  Model fallan silenciosamente y el campo queda NULL en la
--  tabla aunque el form lo envíe.
--
--  IDEMPOTENTE: DROP + CREATE de cada SP. Las columnas de tabla
--  ya existen en el schema base, no las tocamos.
-- ============================================================

USE `zonamarcol`;

DELIMITER $$

-- ─────────────────────────────────────────────
-- 1) PRODUCTOS (simple, sin variantes)
-- ─────────────────────────────────────────────
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

-- ─────────────────────────────────────────────
-- 2) VARIANTES
-- ─────────────────────────────────────────────
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
    INSERT INTO producto_variantes
        (producto_id, nombre, precio, stock, codigo_barras, image_url, orden)
    VALUES
        (p_producto_id, p_nombre, p_precio, p_stock,
         p_codigo_barras, p_image_url, p_orden);
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
    SET nombre        = p_nombre,
        precio        = p_precio,
        stock         = p_stock,
        codigo_barras = p_codigo_barras,
        image_url     = COALESCE(p_image_url, image_url),
        orden         = p_orden
    WHERE id = p_id;
END$$

DELIMITER ;

-- ─────────────────────────────────────────────
-- Verificación rápida — debe devolver 8 parámetros para productos_insert
-- y 7 para variantes_insert.
-- ─────────────────────────────────────────────
-- SHOW CREATE PROCEDURE sp_productos_insert\G
-- SHOW CREATE PROCEDURE sp_variantes_insert\G
