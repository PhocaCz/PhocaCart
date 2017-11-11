ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `active_quantity` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `minimal_quantity` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `maximal_quantity` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_text_others` text;
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_subject_others` varchar(255) NOT NULL DEFAULT '';
