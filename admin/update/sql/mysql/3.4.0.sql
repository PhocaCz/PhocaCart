-- 3.4.0
CREATE TABLE IF NOT EXISTS `#__phocacart_product_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `download_token` char(64) NOT NULL DEFAULT '',
  `download_folder` varchar(255) NOT NULL DEFAULT '',
  `download_file` varchar(255) NOT NULL DEFAULT '',
  `download_hits` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `idx_product` (`product_id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__phocacart_product_images` ADD COLUMN `type` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_images` ADD COLUMN `ordering` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `ordering` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_downloads` ADD COLUMN `type` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_products` ADD COLUMN `public_play_file` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_products` ADD COLUMN `public_play_text` varchar(255) NOT NULL DEFAULT '';