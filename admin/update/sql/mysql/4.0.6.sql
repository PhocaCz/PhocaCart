ALTER TABLE `#__phocacart_categories` ADD COLUMN `title_long` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_products` ADD COLUMN `title_long` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `title_long` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_products` ADD COLUMN `internal_comment` text;
