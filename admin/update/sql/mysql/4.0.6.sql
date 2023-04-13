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

ALTER TABLE `#__phocacart_users` ADD COLUMN `params_user` text;
ALTER TABLE `#__phocacart_orders` ADD COLUMN `params_user` text;

ALTER TABLE `#__phocacart_taxes` ADD COLUMN `tax_hide` text;
ALTER TABLE `#__phocacart_order_total` ADD COLUMN `item_id_p` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_tax_recapitulation` ADD COLUMN `item_id_p` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_products` ADD COLUMN `default_tax_id_p` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_groups` CHANGE `valid_from` `valid_from` datetime NULL DEFAULT NULL;
ALTER TABLE `#__phocacart_groups` CHANGE `valid_to` `valid_to` datetime NULL DEFAULT NULL;

ALTER TABLE `#__phocacart_products` ADD COLUMN `aidata` text;

ALTER TABLE `#__phocacart_payment_methods` ADD COLUMN `active_currency` tinyint(1) NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_currencies` (
  `payment_id` int(11) NOT NULL DEFAULT '0',
  `currency_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_payment` (`payment_id`, `currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

