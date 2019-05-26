-- 3.2.0
ALTER TABLE `#__phocacart_specifications` ADD COLUMN `image` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_specifications` ADD COLUMN `image_medium` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_specifications` ADD COLUMN `image_small` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_specifications` ADD COLUMN `color` varchar(50) NOT NULL DEFAULT '';

ALTER TABLE `#__phocacart_groups` ADD COLUMN `display_addtocart` tinyint(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_groups` ADD COLUMN `display_attributes` tinyint(1) unsigned NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_categories` ADD COLUMN `created_by` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_categories` ADD COLUMN `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__phocacart_categories` ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_categories` ADD COLUMN `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `#__phocacart_products` ADD COLUMN `created_by` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_products` ADD COLUMN `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__phocacart_products` ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_products` ADD COLUMN `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
