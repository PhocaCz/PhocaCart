-- 3.1.9
ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `download_token` char(64) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `download_folder` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `download_file` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `download_hits` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_order_downloads` ADD COLUMN `attribute_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_downloads` ADD COLUMN `option_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_downloads` ADD COLUMN `order_attribute_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_downloads` ADD COLUMN `order_option_id` int(11) NOT NULL DEFAULT '0';


ALTER TABLE `#__phocacart_stock_statuses` ADD COLUMN `link` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_stock_statuses` ADD COLUMN `link_target` varchar(10) NOT NULL DEFAULT '';