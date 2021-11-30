-- 3.5.0 RC
ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `email_attachments` text;
ALTER TABLE `#__phocacart_orders` ADD COLUMN `terms` tinyint(1) NOT NULL default '0';

-- 3.5.0 Beta

ALTER TABLE `#__phocacart_products` ADD COLUMN `date_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__phocacart_products` ADD COLUMN `external_link2` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_products` ADD COLUMN `external_text2` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__phocacart_products` ADD COLUMN `features` text;

ALTER TABLE `#__phocacart_categories` ADD COLUMN `count_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__phocacart_categories` ADD COLUMN `count_products` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_tags` ADD COLUMN `count_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__phocacart_tags` ADD COLUMN `count_products` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `count_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__phocacart_manufacturers` ADD COLUMN `count_products` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_categories` ADD COLUMN `icon_class` varchar(64) NOT NULL DEFAULT '';

ALTER TABLE `#__phocacart_products` ADD COLUMN `user_id` int(11) NOT NULL;
ALTER TABLE `#__phocacart_products` ADD COLUMN `vendor_id` int(11) NOT NULL;
ALTER TABLE `#__phocacart_categories` ADD COLUMN `user_id` int(11) NOT NULL;
ALTER TABLE `#__phocacart_categories` ADD COLUMN `vendor_id` int(11) NOT NULL;

ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `orders_view_display` text;

ALTER TABLE `#__phocacart_products` ADD COLUMN `download_days` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `#__phocacart_product_files` ADD COLUMN `download_days` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `#__phocacart_order_downloads` ADD COLUMN `download_days` int(11) NOT NULL DEFAULT '-1';


ALTER TABLE `#__phocacart_products` ADD INDEX `idx_price` (`price`);

CREATE TABLE IF NOT EXISTS `#__phocacart_parameters` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `title_header` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL default '0',
  `link_type` tinyint(1) NOT NULL DEFAULT '0',
  `limit_count` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__phocacart_parameter_values` (
  `id` int(11) NOT NULL auto_increment,
  `parameter_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL default '0',
  `display_format` tinyint(1) NOT NULL default '0',
  `icon_class` varchar(64) NOT NULL DEFAULT '',
  `link_ext` varchar(255) NOT NULL DEFAULT '',
  `link_cat` int(11) unsigned NOT NULL DEFAULT '0',
  `description` text,
  `count_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `count_products` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

-- `id` SERIAL,
CREATE TABLE IF NOT EXISTS `#__phocacart_parameter_values_related` (
  `item_id` int(11) NOT NULL DEFAULT '0',
  `parameter_value_id` int(11) NOT NULL DEFAULT '0',
  `parameter_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `i_parameter_id` (`item_id`,`parameter_value_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__phocacart_submit_items` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `ip` varchar(20) NOT NULL default '',
  `items_item` text,
  `items_contact` text,
  `items_parameter` text,
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `upload_token` char(64) NOT NULL DEFAULT '',
  `upload_folder` varchar(255) NOT NULL DEFAULT '',
  `date_submit` datetime NOT NULL default '0000-00-00 00:00:00',
  `privacy` tinyint(1) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `published` (`published`)
) DEFAULT CHARSET=utf8;