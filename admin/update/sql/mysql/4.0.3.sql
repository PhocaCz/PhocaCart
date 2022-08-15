ALTER TABLE `#__phocacart_products` ADD COLUMN `special_parameter` text;
ALTER TABLE `#__phocacart_products` ADD COLUMN `special_image` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_categories` ADD COLUMN `special_parameter` text;
ALTER TABLE `#__phocacart_categories` ADD COLUMN `special_image` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_categories` ADD COLUMN `params_feed` text;
