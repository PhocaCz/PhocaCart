-- 3.5.3
ALTER TABLE `#__phocacart_orders` ADD COLUMN `queue_number` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `queue_number_id` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_product_discounts` ADD COLUMN `image` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_product_discounts` ADD COLUMN `background_image` varchar(255) NOT NULL DEFAULT '';
