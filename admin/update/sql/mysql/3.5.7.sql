-- 3.5.7
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_send_format` int(2) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `bulk_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `price_original` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `current_price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `current_price_original` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_product_price_history` ADD COLUMN `type` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `active_zip` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `zip` text;
ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `description_info` text;

ALTER TABLE `#__phocacart_payment_methods` ADD COLUMN `description_info` text;
ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `required` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_products` ADD COLUMN `gift_types` text;

ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_description` text;
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_image` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_recipient_name` varchar(100) NOT NULL default '';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_recipient_email` varchar(50) NOT NULL default '';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_sender_name` varchar(100) NOT NULL default '';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_sender_message` text;
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_type` tinyint(1) NOT NULL DEFAULT '-1';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_order_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_product_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_order_product_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `coupon_type` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `gift_class_name` varchar(50) NOT NULL default '';

ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `activate_gift` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_gift` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_subject_gift_sender` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_text_gift_sender` text;
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_subject_gift_recipient` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_text_gift_recipient` text;
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_gift_format` tinyint(1) NOT NULL DEFAULT '0';

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