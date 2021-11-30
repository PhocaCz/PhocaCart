-- 3.5.4
ALTER TABLE `#__phocacart_product_stock` ADD COLUMN `sku` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_product_stock` ADD COLUMN `ean` varchar(15) NOT NULL DEFAULT '';

-- Maria DB
-- ALTER TABLE `#__phocacart_product_stock` ADD COLUMN IF NOT EXISTS `sku` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE `#__phocacart_product_stock` ADD COLUMN IF NOT EXISTS `ean` varchar(15) NOT NULL DEFAULT '';

-- MySQL
-- SET @dbname = DATABASE();
-- SET @tablename = `#__phocacart_product_stock`;
-- SET @columnname = `sku`;
-- SET @preparedStatement = (SELECT IF(
--   (
--     SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
--     WHERE
--       (table_name = @tablename)
--       AND (table_schema = @dbname)
--       AND (column_name = @columnname)
--   ) > 0,
--   "SELECT 1",
--   CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " varchar(255) NOT NULL DEFAULT '';")
-- ));
-- PREPARE alterIfNotExists FROM @preparedStatement;
-- EXECUTE alterIfNotExists;
-- DEALLOCATE PREPARE alterIfNotExists;



-- DROP PROCEDURE IF EXISTS `acs`;
-- DELIMITER // CREATE PROCEDURE `acs`() BEGIN DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END; ALTER TABLE `#__phocacart_product_stock` ADD COLUMN `sku` varchar(255) NOT NULL DEFAULT ''; END //
-- DELIMITER ;
-- CALL `acs`();
-- DROP PROCEDURE `acs`;

-- delimiter ;;
--DROP PROCEDURE IF EXISTS foo;;
--create procedure foo ()
--begin
--   declare continue handler for 1060 begin end;
--    alter table `#__phocacart_product_stock` add `sku` varchar(255) NOT NULL DEFAULT '';
--end;;
--call foo();;

ALTER TABLE `#__phocacart_products` ADD COLUMN `featured_background_image` varchar(255) NOT NULL DEFAULT '';

