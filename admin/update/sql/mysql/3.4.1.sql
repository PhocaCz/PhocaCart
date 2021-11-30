-- 3.4.1

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


  
CREATE TABLE IF NOT EXISTS `#__phocacart_parameters` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL default '0',
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