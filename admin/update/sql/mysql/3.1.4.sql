-- 3.1.4
CREATE TABLE IF NOT EXISTS `#__phocacart_opening_times` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `day` tinyint(1) NOT NULL default '0',
  `hour_from` int(2) NOT NULL default '0',
  `minute_from` int(2) NOT NULL default '0',
  `hour_to` int(2) NOT NULL default '0',
  `minute_to` int(2) NOT NULL default '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__phocacart_orders` ADD COLUMN `required_delivery_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

