-- 3.1.8
ALTER TABLE `#__phocacart_orders` ADD COLUMN `user_lang` char(7) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `default_lang` char(7) NOT NULL DEFAULT '';

ALTER TABLE `#__phocacart_orders` ADD COLUMN `oidn_spec_billing_desc` text;
ALTER TABLE `#__phocacart_orders` ADD COLUMN `oidn_spec_shipping_desc` text;

ALTER TABLE `#__phocacart_order_total` ADD COLUMN `title_lang` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_order_total` ADD COLUMN `title_lang_suffix` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_order_total` ADD COLUMN `title_lang_suffix2` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `#__phocacart_order_tax_recapitulation` ADD COLUMN `title_lang` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_order_tax_recapitulation` ADD COLUMN `title_lang_suffix` varchar(100) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_order_tax_recapitulation` ADD COLUMN `title_lang_suffix2` varchar(100) NOT NULL DEFAULT '';



