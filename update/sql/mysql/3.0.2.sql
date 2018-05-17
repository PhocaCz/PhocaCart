ALTER TABLE `#__phocacart_product_stock` ADD COLUMN `operator` char(1) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_product_stock` ADD COLUMN `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_stock` ADD COLUMN `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
