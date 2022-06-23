-- 3.1.6
ALTER TABLE `#__phocacart_products` ADD COLUMN `condition` tinyint(2) NOT NULL DEFAULT 0;
ALTER TABLE `#__phocacart_products` ADD COLUMN `delivery_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__phocacart_products` ADD COLUMN `type_feed` text;
ALTER TABLE `#__phocacart_products` ADD COLUMN `type_category_feed` text;

ALTER TABLE `#__phocacart_categories` ADD COLUMN `type_feed` text;


-- country and region can have different tax
ALTER TABLE `#__phocacart_order_total` ADD COLUMN `item_id_c` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_total` ADD COLUMN `item_id_r` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_order_products` ADD COLUMN `default_tax_id_c` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_products` ADD COLUMN `default_tax_id_r` int(11) NOT NULL DEFAULT '0';


CREATE TABLE IF NOT EXISTS `#__phocacart_order_tax_recapitulation` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`order_id` int(11) NOT NULL DEFAULT '0',
	`item_id` int(11) NOT NULL DEFAULT '0',
	`item_id_c` int(11) NOT NULL DEFAULT '0',
	`item_id_r` int(11) NOT NULL DEFAULT '0',
	`title` varchar(255) NOT NULL DEFAULT '',
	`type` varchar(50) NOT NULL DEFAULT '',
	`amount_netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`amount_tax` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`amount_brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`amount_brutto_currency` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`checked_out` int(11) unsigned NOT NULL DEFAULT '0',
	`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`ordering` int(11) NOT NULL DEFAULT '0',
	`published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) DEFAULT CHARSET=utf8;
