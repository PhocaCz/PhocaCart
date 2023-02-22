ALTER TABLE `#__phocacart_categories` ADD COLUMN `title_long` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_categories` ADD COLUMN `featured` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_products` ADD COLUMN `title_long` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `title_long` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_products` ADD COLUMN `internal_comment` text;
ALTER TABLE `#__phocacart_groups` ADD COLUMN `activate_registration` int(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `metatitle` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `metakey` text;
ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `metadesc` text;
ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `metadata` text;
ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `featured` tinyint(1) NOT NULL DEFAULT '0';



ALTER TABLE `#__phocacart_groups` CHANGE `valid_from` `valid_from` datetime NULL DEFAULT NULL;
ALTER TABLE `#__phocacart_groups` CHANGE `valid_to` `valid_to` datetime NULL DEFAULT NULL;
