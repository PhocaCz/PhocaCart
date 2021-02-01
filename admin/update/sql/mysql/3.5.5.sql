-- 3.5.5
ALTER TABLE `#__phocacart_form_fields` ADD COLUMN `predefined_values` text;
ALTER TABLE `#__phocacart_form_fields` ADD COLUMN `predefined_values_first_option` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE `#__phocacart_orders` ADD COLUMN `params_shipping` text;
ALTER TABLE `#__phocacart_orders` ADD COLUMN `params_payment` text;