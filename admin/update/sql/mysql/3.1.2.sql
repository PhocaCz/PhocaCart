ALTER TABLE `#__phocacart_tags`  ADD COLUMN `type` tinyint(3) NOT NULL default '0';
ALTER TABLE `#__phocacart_tags`  ADD COLUMN `display_format` tinyint(3) NOT NULL default '0';
ALTER TABLE `#__phocacart_tags`  ADD COLUMN `icon_class` varchar(64) NOT NULL DEFAULT '';

CREATE TABLE IF NOT EXISTS `#__phocacart_taglabels_related` (
  `id` SERIAL,
  `item_id` int(11) NOT NULL DEFAULT '0',
  `tag_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `i_file_id` (`item_id`,`tag_id`)
) DEFAULT CHARSET=utf8;

