-- Smith's Golf Grips Database Schema
-- Run against the target database (local: smiths_grips, production: if0_41630087_smiths_grips)

-- ============================================================
-- GRIPS
-- One row per SKU / color variant from the catalog.
-- catalog_cost is the raw catalog price.
-- Final customer price is calculated at query time using
-- pricing_config and markup_tiers (see query examples below).
-- ============================================================
--CREATE DATABASE smiths_grips;
USE if0_41630087_smiths_grips;

CREATE TABLE IF NOT EXISTS grips (
    id           INT          NOT NULL AUTO_INCREMENT,
    brand        VARCHAR(50)  NOT NULL,
    sku          VARCHAR(50)  NOT NULL,
    model        VARCHAR(100) NOT NULL,
    size         VARCHAR(30)  NOT NULL,
    color        VARCHAR(50)  NOT NULL,
    core         VARCHAR(20)  NOT NULL DEFAULT '',
    catalog_cost DECIMAL(6,2) NOT NULL,
    category     VARCHAR(50)  NOT NULL DEFAULT '',
    image_path   VARCHAR(255) NOT NULL DEFAULT '',
    active       TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sku_size_color (sku, size, color)
);

-- ============================================================
-- PRICING CONFIG
-- Single row. Admin updates labor_cost and material_cost here.
-- labor_cost   = Corey's labor charge per grip
-- material_cost = tape / solvent / supplies per grip
-- ============================================================
CREATE TABLE IF NOT EXISTS pricing_config (
    id            INT          NOT NULL AUTO_INCREMENT,
    labor_cost    DECIMAL(6,2) NOT NULL DEFAULT 5.00,
    material_cost DECIMAL(6,2) NOT NULL DEFAULT 0.50,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Seed with default values (one row only)
INSERT INTO pricing_config (labor_cost, material_cost)
SELECT 5.00, 0.50
WHERE NOT EXISTS (SELECT 1 FROM pricing_config);

-- ============================================================
-- MARKUP TIERS
-- One row per catalog_cost price range.
-- max_price NULL means "no upper limit" (open-ended top tier).
-- label is for admin display only.
-- ============================================================
CREATE TABLE IF NOT EXISTS markup_tiers (
    id        INT          NOT NULL AUTO_INCREMENT,
    min_price DECIMAL(6,2) NOT NULL,
    max_price DECIMAL(6,2)     NULL DEFAULT NULL,
    markup    DECIMAL(6,2) NOT NULL,
    label     VARCHAR(100) NOT NULL DEFAULT '',
    PRIMARY KEY (id)
);

-- Default markup tiers (adjust via admin panel later)
INSERT INTO markup_tiers (min_price, max_price, markup, label)
SELECT * FROM (
    SELECT  0.00,  9.99, 3.00, 'Under $10'     UNION ALL
    SELECT 10.00, 14.99, 4.00, '$10 – $14.99'  UNION ALL
    SELECT 15.00, 19.99, 5.00, '$15 – $19.99'  UNION ALL
    SELECT 20.00,  NULL, 6.00, '$20 and above'
) AS defaults
WHERE NOT EXISTS (SELECT 1 FROM markup_tiers);

-- ============================================================
-- ORDERS
-- One row per customer order submission.
-- total_price is stored at time of order (snapshot).
-- status: pending → confirmed → completed | cancelled
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id             INT          NOT NULL AUTO_INCREMENT,
    customer_name  VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(30)  NOT NULL DEFAULT '',
    notes          TEXT             NULL,
    status         ENUM('pending','confirmed','completed','cancelled')
                                NOT NULL DEFAULT 'pending',
    total_price    DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

ALTER TABLE orders 
ADD COLUMN clubs_num INT NOT NULL DEFAULT 0,
ADD COLUMN putters_num INT NOT NULL DEFAULT 0;

-- ============================================================
-- ORDER ITEMS
-- One row per grip line in an order.
-- unit_price is a snapshot of the final_price at order time
-- so historical orders are not affected by future price changes.
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id         INT          NOT NULL AUTO_INCREMENT,
    order_id   INT          NOT NULL,
    grip_id    INT          NOT NULL,
    quantity   INT          NOT NULL DEFAULT 1,
    unit_price DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
        REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_grip  FOREIGN KEY (grip_id)
        REFERENCES grips  (id) ON DELETE RESTRICT
);

-- ============================================================
-- Admin
-- One row per admin account
-- username is unique
-- password is saved as a hash for security
-- ============================================================
CREATE TABLE IF NOT EXISTS admin (
    id            INT          NOT NULL AUTO_INCREMENT,
    username      VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

-- ============================================================
-- EXAMPLE: query a grip's final customer price
--
-- SELECT
--     g.id,
--     g.brand,
--     g.model,
--     g.size,
--     g.color,
--     g.catalog_cost,
--     pc.labor_cost,
--     pc.material_cost,
--     mt.markup,
--     (g.catalog_cost + pc.labor_cost + pc.material_cost + mt.markup) AS final_price
-- FROM grips g
-- JOIN pricing_config pc ON pc.id = 1
-- JOIN markup_tiers mt
--   ON g.catalog_cost >= mt.min_price
--  AND (mt.max_price IS NULL OR g.catalog_cost <= mt.max_price)
-- WHERE g.active = 1;
-- ============================================================
