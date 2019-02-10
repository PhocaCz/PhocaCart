/*CREATE TABLE IF NOT EXISTS `#__phocacart_cart_multiple` (
  `id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `unit_id` int(11) NOT NULL DEFAULT '0',
  `section_id` int(11) NOT NULL DEFAULT '0',
  `loyalty_card_number` varchar(30) NOT NULL DEFAULT '',
  `cart` text,
  `shipping` int(11) NOT NULL DEFAULT '0',
  `coupon` int(11) NOT NULL DEFAULT '0',
  `payment` int(11) NOT NULL DEFAULT '0',
  `reward` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL default '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__phocacart_cart_multiple` (id, user_id, cart, shipping, coupon, payment, reward, date, vendor_id, ticket_id) SELECT row_number() over (ORDER BY (SELECT NULL)), user_id, cart, shipping, coupon, payment, reward, date, 0 AS vendor_id, 0 AS ticket_id FROM `#__phocacart_cart`;*/


-- 3.1.0 Alpha
CREATE TABLE IF NOT EXISTS `#__phocacart_cart_multiple` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `unit_id` int(11) NOT NULL DEFAULT '0',
  `section_id` int(11) NOT NULL DEFAULT '0',
  `loyalty_card_number` varchar(30) NOT NULL DEFAULT '',
  `cart` text,
  `shipping` int(11) NOT NULL DEFAULT '0',
  `coupon` int(11) NOT NULL DEFAULT '0',
  `payment` int(11) NOT NULL DEFAULT '0',
  `reward` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL default '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `idx_uvtus` (`user_id`, `vendor_id`, `ticket_id`, `unit_id`, `section_id`) 
) DEFAULT CHARSET=utf8;

INSERT INTO `#__phocacart_cart_multiple` (user_id, cart, shipping, coupon, payment, reward, date, vendor_id, ticket_id, unit_id, section_id) SELECT user_id, cart, shipping, coupon, payment, reward, date, 0 AS vendor_id, 1 AS ticket_id, 1 AS unit_id, 1 AS section_id FROM `#__phocacart_cart`;

CREATE TABLE IF NOT EXISTS `#__phocacart_vendors` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__phocacart_sections` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__phocacart_units` (
  `id` int(11) NOT NULL auto_increment,
  `section_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `class_name`  varchar(255) NOT NULL DEFAULT '',
  `custom_css` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) DEFAULT CHARSET=utf8;



ALTER TABLE `#__phocacart_users` ADD COLUMN `loyalty_card_number` varchar(30) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_taxes` ADD COLUMN `code` varchar(5) NOT NULL DEFAULT '';

ALTER TABLE `#__phocacart_orders` ADD COLUMN `type` tinyint(3) NOT NULL default '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `vendor_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `ticket_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `unit_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `section_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `loyalty_card_number` varchar(30) NOT NULL DEFAULT '';

ALTER TABLE `#__phocacart_payment_methods` ADD COLUMN `type` tinyint(3) NOT NULL default '0';
ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `type` tinyint(3) NOT NULL default '0';
ALTER TABLE `#__phocacart_coupons` ADD COLUMN `type` tinyint(3) NOT NULL default '0';
ALTER TABLE `#__phocacart_discounts` ADD COLUMN `type` tinyint(3) NOT NULL default '0';
ALTER TABLE `#__phocacart_products` ADD COLUMN `type` tinyint(3) NOT NULL default '0';
ALTER TABLE `#__phocacart_categories` ADD COLUMN `type` tinyint(3) NOT NULL default '0';


-- 3.1.0 Beta
ALTER TABLE `#__phocacart_orders` ADD COLUMN `reference_field1` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `reference_field2` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `reference_data` text;

ALTER TABLE `#__phocacart_orders` ADD COLUMN `order_number` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `receipt_number` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_number` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_prn` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_due_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

-- 3.1.0 Stable

ALTER TABLE `#__phocacart_form_fields` ADD COLUMN `display_docs` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_spec_top_desc` text;
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_spec_middle_desc` text;
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_spec_bottom_desc` text;
ALTER TABLE `#__phocacart_order_attributes` ADD COLUMN `option_value` text;


ALTER TABLE `#__phocacart_orders` ADD COLUMN `privacy` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__phocacart_users` ADD COLUMN `privacy` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__phocacart_questions` ADD COLUMN `privacy` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__phocacart_payment_methods` ADD COLUMN `privacy` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `privacy` tinyint(1) NOT NULL default '0';

