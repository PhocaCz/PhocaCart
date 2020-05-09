-- 3.5.1
-- user_id - the one who bought (orders)
-- vendor_id - the one who sold (orders)
-- owner_id - the one to which item is assigned (category, product, product sold)
ALTER TABLE `#__phocacart_categories` ADD COLUMN `owner_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_products` ADD COLUMN `owner_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_products` ADD COLUMN `owner_id` int(11) NOT NULL DEFAULT '0';
--ALTER TABLE `#__phocacart_order_products` ADD COLUMN `vendor_id` int(11) NOT NULL DEFAULT '0';


ALTER TABLE `#__phocacart_form_fields` ADD COLUMN `pattern` text;
ALTER TABLE `#__phocacart_form_fields` ADD COLUMN `maxlength` int(11) NOT NULL DEFAULT '0';

