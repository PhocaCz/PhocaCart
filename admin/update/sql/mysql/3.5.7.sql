-- 3.5.7
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_send_format` int(2) NOT NULL DEFAULT '0';


ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `bulk_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `price_original` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `current_price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `current_price_original` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `type` tinyint(1) NOT NULL DEFAULT '0';


CREATE TABLE IF NOT EXISTS `#__phocacart_bulk_prices` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL default '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;
