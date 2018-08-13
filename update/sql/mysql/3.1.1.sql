ALTER TABLE `#__phocacart_product_stock` ADD COLUMN `sku` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_product_stock` ADD COLUMN `ean` varchar(15) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_payment_methods` ADD COLUMN `cost_additional` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `cost_additional` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders`  ADD COLUMN `amount_pay` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders`  ADD COLUMN `amount_tendered` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders`  ADD COLUMN `amount_change` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
