-- -------------------------------------------------------------------- --
-- Phoca Cart manual installation                                       --
-- -------------------------------------------------------------------- --
-- See documentation on https://www.phoca.cz/                            --
--                                                                      --
-- Change all prefixes #__ to prefix which is set in your Joomla! site  --
-- (e.g. from #__phocacart to #__phocacart)                            --
-- Run this SQL queries in your database tool, e.g. in phpMyAdmin       --
-- If you have questions, just ask in Phoca Forum                       --
-- https://www.phoca.cz/forum/                                           --
-- -------------------------------------------------------------------- --

CREATE TABLE IF NOT EXISTS `#__phocacart_categories` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `category_type` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_long` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `title_feed` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(3) NOT NULL default '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `icon_class` varchar(64) NOT NULL DEFAULT '',
  `type_feed` text,
  `description` text,
  `description_bottom` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `special_parameter` text,
  `special_image` varchar(255) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `count_date` datetime NOT NULL,
  `count_products` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `params_feed` text,
  `metatitle` varchar(255) NOT NULL DEFAULT '',
  `metakey` text,
  `metadesc` text,
  `metadata` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catid` int(11) NOT NULL DEFAULT '0',
  `tax_id` int(11) NOT NULL DEFAULT '0',
  `manufacturer_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_long` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(3) NOT NULL default '0',
  `type_feed` text,
  `type_category_feed` text,
  `description` text,
  `description_long` text,
  `features` text,
  `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `price_original` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `discount_percent` DECIMAL(15,2) AS (if(price_original <> 0, (price_original - price) / price_original * 100, 0)) VIRTUAL,
  `length` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `width` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `height` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `unit_size` int(2) NOT NULL DEFAULT '0',
  `weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `unit_weight` int(2) NOT NULL DEFAULT '0',
  `volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `unit_volume` int(2) NOT NULL DEFAULT '0',
  `stock` int(11) NOT NULL DEFAULT '0',
  `stock_calculation` int(11) NOT NULL DEFAULT '0',
  `unit_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `unit_unit` varchar(64) NOT NULL DEFAULT '',
  `min_quantity` int(11) NOT NULL DEFAULT '0',
  `min_multiple_quantity` int(11) NOT NULL DEFAULT '0',
  `min_quantity_calculation` int(11) NOT NULL DEFAULT '0',
  `max_quantity` int(11) NOT NULL DEFAULT '0',
  `max_quantity_calculation` int(11) NOT NULL DEFAULT '0',
  `stockstatus_a_id` int(11) NOT NULL DEFAULT '0',
  `stockstatus_n_id` int(11) NOT NULL DEFAULT '0',
  `availability` text,
  `sku` varchar(255) NOT NULL DEFAULT '',
  `upc` varchar(15) NOT NULL DEFAULT '',
  `ean` varchar(15) NOT NULL DEFAULT '',
  `jan` varchar(15) NOT NULL DEFAULT '',
  `isbn` varchar(20) NOT NULL DEFAULT '',
  `mpn` varchar(255) NOT NULL DEFAULT '',
  `serial_number` varchar(255) NOT NULL DEFAULT '',
  `registration_key` varchar(255) NOT NULL DEFAULT '',
  `external_id` varchar(255) NOT NULL DEFAULT '',
  `external_key` varchar(255) NOT NULL DEFAULT '',
  `external_link` varchar(255) NOT NULL DEFAULT '',
  `external_text` varchar(255) NOT NULL DEFAULT '',
  `external_link2` varchar(255) NOT NULL DEFAULT '',
  `external_text2` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `video` varchar(255) NOT NULL DEFAULT '',
  `public_download_file` varchar(255) NOT NULL DEFAULT '',
  `public_download_text` varchar(255) NOT NULL DEFAULT '',
  `public_play_file` varchar(255) NOT NULL DEFAULT '',
  `public_play_text` varchar(255) NOT NULL DEFAULT '',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `featured_background_image` varchar(255) NOT NULL DEFAULT '',
  `special_parameter` text,
  `special_image` varchar(255) NOT NULL DEFAULT '',
  `allow_upload` tinyint(1) NOT NULL DEFAULT '0',
  `custom_text` text,
  `download_token` char(64) NOT NULL DEFAULT '',
  `download_folder` varchar(255) NOT NULL DEFAULT '',
  `download_file` varchar(255) NOT NULL DEFAULT '',
  `download_hits` int(11) NOT NULL DEFAULT '0',
  `download_days` int(11) NOT NULL DEFAULT '-1',
  `condition` tinyint(2) NOT NULL DEFAULT 0,
  `delivery_date` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `date` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0',
  `sales` int(11) NOT NULL DEFAULT '0',
  `points_received` int(11) NOT NULL DEFAULT '0',
  `points_needed` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `params_feed` text,
  `gift_types` text,
  `internal_comment` text,
  `metatitle` varchar(255) NOT NULL DEFAULT '',
  `metakey` text,
  `metadesc` text,
  `metadata` text,
  `aidata` text,
  `language` char(7) NOT NULL DEFAULT '',
  `subscription_period` smallint(5) unsigned DEFAULT NULL,
  `subscription_unit` TINYINT(3) UNSIGNED DEFAULT NULL,
  `subscription_signup_fee` decimal(15,4) DEFAULT NULL,
  `subscription_renewal_discount` decimal(15,4) DEFAULT NULL,
  `subscription_renewal_discount_calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `subscription_usergroup_add` text,
  `subscription_usergroup_remove` text,
  `subscription_trial_enabled` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `subscription_trial_period` smallint(5) unsigned DEFAULT NULL,
  `subscription_trial_unit` TINYINT(3) UNSIGNED DEFAULT NULL,
  `subscription_grace_period_days` smallint(5) unsigned DEFAULT 0,
  `subscription_max_renewals` int(11) DEFAULT NULL,
  `redirect_product_id` int(11),
  `redirect_url` varchar(255),
  PRIMARY KEY  (`id`),
  KEY `product_idx` (`published`,`access`),
  KEY `idx_price` (`price`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_alias` (`alias`),
  KEY `idx_manufacturer` (`manufacturer_id`),
  KEY `idx_tax` (`tax_id`),
  KEY `stockstatus_a_id` (`stockstatus_a_id`),
  KEY `stockstatus_n_id` (`stockstatus_n_id`),
  KEY `idx_language` (`language`),
  KEY `sales` ( `sales` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_categories` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_category` (`product_id`,`category_id`),
  KEY `ordering` ( `ordering` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_featured` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `download_token` char(64) NOT NULL DEFAULT '',
  `download_folder` varchar(255) NOT NULL DEFAULT '',
  `download_file` varchar(255) NOT NULL DEFAULT '',
  `download_hits` int(11) NOT NULL DEFAULT '0',
  `download_days` int(11) NOT NULL DEFAULT '-1',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_discounts` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `discount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `total_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '0',
  `quantity_from` int(11) NOT NULL DEFAULT '0',
  `quantity_to` int(11) NOT NULL DEFAULT '0',
  `available_quantity` int(11) NOT NULL DEFAULT '0',
  `available_quantity_user` int(11) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  `background_image` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_price_groups` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_point_groups` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `points_received` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_stock` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_key` text,
  `attributes` text,
  `sku` varchar(255) NOT NULL DEFAULT '',
  `ean` varchar(15) NOT NULL DEFAULT '',
  `stock` int(11) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_small` varchar(255) NOT NULL DEFAULT '',
  `image_medium` varchar(255) NOT NULL DEFAULT '',
  `operator` char(1) NOT NULL DEFAULT '',
  `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `active_price` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_price_history` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `bulk_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `price_original` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `current_price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `current_price_original` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_bulk_prices` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `date` datetime NOT NULL,
  `description` text,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL default '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `attribute_template` int(11),
  `is_filter` int(11) NOT NULL DEFAULT 1,
  `published` int(1) NOT NULL DEFAULT '1',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `operator` char(1) NOT NULL DEFAULT '',
  `stock` int(11) NOT NULL DEFAULT '0',
  `weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `operator_weight` char(1) NOT NULL DEFAULT '0',
  `volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `operator_volume` char(1) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL DEFAULT '',
  `image_small` varchar(255) NOT NULL DEFAULT '',
  `image_medium` varchar(255) NOT NULL DEFAULT '',
  `download_token` char(64) NOT NULL DEFAULT '',
  `download_folder` varchar(255) NOT NULL DEFAULT '',
  `download_file` varchar(255) NOT NULL DEFAULT '',
  `download_hits` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL DEFAULT '',
  `color` varchar(50) NOT NULL DEFAULT '',
  `sku` varchar(255) NOT NULL DEFAULT '',
  `ean` varchar(15) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `default_value` tinyint(1) NOT NULL DEFAULT '0',
  `params` text,
  PRIMARY KEY (`id`),
  KEY `idx_attribute` (`attribute_id`) ,
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_attributes` (
--  `product_id` int(11) NOT NULL,
--   `attribute_id` int(11) NOT NULL,
-- KEY `idx_product` (`product_id`, `attribute_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_specifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `value` text,
  `alias_value` varchar(255) NOT NULL DEFAULT '',

  `image` varchar(255) NOT NULL DEFAULT '',
  `image_medium` varchar(255) NOT NULL DEFAULT '',
  `image_small` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(50) NOT NULL DEFAULT '',

  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`) ,
  KEY `idx_group` (`group_id`) ,
  KEY `idx_alias` (`alias`),
  KEY `idx_alias_value` (`alias_value`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_specification_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_product_related` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `related_type` int(11) NOT NULL,
  `product_a` int(11) NOT NULL DEFAULT '0',
  `product_b` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_a` (`product_a`),
  KEY `product_b` (`product_b`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `code2` varchar(20) NOT NULL DEFAULT '',
  `code3` varchar(20) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `code2` varchar(20) NOT NULL DEFAULT '',
  `code3` varchar(20) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `code2` varchar(20) NOT NULL DEFAULT '',
  `code3` varchar(20) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_zone_countries` (
  `zone_id` int(11) NOT NULL DEFAULT '0',
  `country_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_zonecountry` (`zone_id`, `country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_zone_regions` (
  `zone_id` int(11) NOT NULL DEFAULT '0',
  `region_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_zoneregion` (`zone_id`, `region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_shipping_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_id` int(11) NOT NULL DEFAULT '0',
  `cost` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `cost_additional` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(100) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(3) NOT NULL default '0',
  `change_tax` tinyint(1) NOT NULL DEFAULT '0',
  `description` text,
  `description_info` text,
  `tracking_title` varchar(255) NOT NULL DEFAULT '',
  `tracking_description` text,
  `tracking_link` varchar(255) NOT NULL DEFAULT '',
  `lowest_weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `highest_weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `active_volume` tinyint(1) NOT NULL DEFAULT '0',
  `lowest_volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `highest_volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `active_weight` tinyint(1) NOT NULL DEFAULT '0',
  `lowest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `highest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--  `lowest_width` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--  `largest_width` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--  `lowest_height` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--  `highest_height` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--  `longest_length` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--  `shortest_length` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `active_size` tinyint(1) NOT NULL DEFAULT '0',
  `maximal_width` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `maximal_height` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `maximal_length` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `minimal_width` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `minimal_height` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `minimal_length` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `active_quantity` tinyint(1) NOT NULL DEFAULT '0',
  `minimal_quantity` int(11) NOT NULL DEFAULT '0',
  `maximal_quantity` int(11) NOT NULL DEFAULT '0',
  `active_amount` tinyint(1) NOT NULL DEFAULT '0',
  `active_country` tinyint(1) NOT NULL DEFAULT '0',
  `active_region` tinyint(1) NOT NULL DEFAULT '0',
  `active_zone` tinyint(1) NOT NULL DEFAULT '0',
  `zip` text,
  `active_zip` tinyint(1) NOT NULL DEFAULT '0',
  `method` varchar(100) NOT NULL DEFAULT '',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL default '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  INDEX (`published`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_shipping_method_countries` (
  `shipping_id` int(11) NOT NULL DEFAULT '0',
  `country_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_shipping` (`shipping_id`, `country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_shipping_method_regions` (
  `shipping_id` int(11) NOT NULL DEFAULT '0',
  `region_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_shipping` (`shipping_id`, `region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_shipping_method_zones` (
  `shipping_id` int(11) NOT NULL DEFAULT '0',
  `zone_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_shipping` (`shipping_id`, `zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_long` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `description` text,
  `count_date` datetime NOT NULL,
  `count_products` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  `metatitle` varchar(255) NOT NULL DEFAULT '',
  `metakey` text,
  `metadesc` text,
  `metadata` text,
  PRIMARY KEY (`id`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_stock_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `title_feed` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `link_target` varchar(10) NOT NULL DEFAULT '',
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_tags` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL default '0',
  `display_format` tinyint(1) NOT NULL default '0',
  `icon_class` varchar(64) NOT NULL DEFAULT '',
  `link_ext` varchar(255) NOT NULL DEFAULT '',
  `link_cat` int(11) unsigned NOT NULL DEFAULT '0',
  `description` text,
  `count_date` datetime NOT NULL,
  `count_products` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--   `id` SERIAL,
--   PRIMARY KEY  (`id`),
CREATE TABLE IF NOT EXISTS `#__phocacart_tags_related` (
  `item_id` int(11) NOT NULL DEFAULT '0',
  `tag_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `i_tag_id` (`item_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--   `id` SERIAL,
--   PRIMARY KEY  (`id`),
CREATE TABLE IF NOT EXISTS `#__phocacart_taglabels_related` (
  `item_id` int(11) NOT NULL DEFAULT '0',
  `tag_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `i_taglabel_id` (`item_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_parameters` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_header` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `link_type` tinyint(1) NOT NULL DEFAULT '0',
  `limit_count` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

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
  `count_date` datetime NOT NULL,
  `count_products` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--  `id` SERIAL,
--   PRIMARY KEY  (`id`),
CREATE TABLE IF NOT EXISTS `#__phocacart_parameter_values_related` (
  `item_id` int(11) NOT NULL DEFAULT '0',
  `parameter_value_id` int(11) NOT NULL DEFAULT '0',
  `parameter_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `i_parameter_id` (`item_id`, `parameter_value_id`, `parameter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_taxes` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(5) NOT NULL DEFAULT '',
  `tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `tax_hide` text,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_tax_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_id` int(11) NOT NULL DEFAULT '0',
  `country_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tax_id` (`tax_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_tax_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_id` int(11) NOT NULL DEFAULT '0',
  `region_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tax_id` (`tax_id`),
  KEY `region_id` (`region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS `#__phocacart_currencies` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(5) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `exchange_rate` DECIMAL( 15, 8 ) NOT NULL DEFAULT '0',
  `price_format` tinyint(1) NOT NULL DEFAULT '0',
  `price_currency_symbol` varchar(10) NOT NULL DEFAULT '',
  `price_dec_symbol` char(1) NOT NULL DEFAULT '',
  `price_decimals` tinyint(1) NOT NULL DEFAULT '0',
  `price_thousands_sep` char(1) NOT NULL DEFAULT '',
  `price_suffix` varchar(255) NOT NULL DEFAULT '',
  `price_prefix` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_coupons` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `discount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `total_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `free_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `free_payment` tinyint(1) NOT NULL DEFAULT '0',
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '0',
  `quantity_from` int(11) NOT NULL DEFAULT '0',
  `quantity_to` int(11) NOT NULL DEFAULT '0',
  `available_quantity` int(11) NOT NULL DEFAULT '0',
  `available_quantity_user` int(11) NOT NULL DEFAULT '0',
  `category_filter` tinyint(1) NOT NULL DEFAULT '1',
  `product_filter` tinyint(1) NOT NULL DEFAULT '1',
  `type` tinyint(1) NOT NULL default '0',
  `coupon_type` tinyint(1) NOT NULL default '0',
  `description` text,
  `gift_title` varchar(255) NOT NULL DEFAULT '',
  `gift_description` text,
  `gift_image` varchar(255) NOT NULL DEFAULT '',
  `gift_recipient_name` varchar(100) NOT NULL default '',
  `gift_recipient_email` varchar(50) NOT NULL default '',
  `gift_sender_name` varchar(100) NOT NULL default '',
  `gift_sender_message` text,
  `gift_type` tinyint(1) NOT NULL DEFAULT '-1',
  `gift_order_id` int(11) NOT NULL DEFAULT '0',
  `gift_product_id` int(11) NOT NULL DEFAULT '0',
  `gift_order_product_id` int(11) NOT NULL DEFAULT '0',
  `gift_class_name` varchar(50) NOT NULL default '',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_coupon_count` (
  `id` int(11) NOT NULL auto_increment,
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_coupon_count_user` (
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  KEY `idx_coupon` (`coupon_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_coupon_products` (
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_coupon` (`coupon_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_coupon_categories` (
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_coupon` (`coupon_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_discounts` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `discount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `total_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `free_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `free_payment` tinyint(1) NOT NULL DEFAULT '0',
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '0',
  `quantity_from` int(11) NOT NULL DEFAULT '0',
  `quantity_to` int(11) NOT NULL DEFAULT '0',
  `available_quantity` int(11) NOT NULL DEFAULT '0',
  `available_quantity_user` int(11) NOT NULL DEFAULT '0',
  `category_filter` tinyint(1) NOT NULL DEFAULT '1',
  `product_filter` tinyint(1) NOT NULL DEFAULT '1',
  `type` tinyint(3) NOT NULL default '0',
  `description` text,
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_discount_products` (
  `discount_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_discountproduct` (`discount_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_discount_categories` (
  `discount_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_discountcategory` (`discount_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_payment_methods` (
  `id` int(11) NOT NULL auto_increment,
  `tax_id` int(11) NOT NULL DEFAULT '0',
  `cost` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `cost_additional` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `operator` char(1) NOT NULL DEFAULT '0',
  `calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(100) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(3) NOT NULL default '0',
  `description` text,
  `description_info` text,
  `lowest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `highest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `active_amount` tinyint(1) NOT NULL DEFAULT '0',
  `active_country` tinyint(1) NOT NULL DEFAULT '0',
  `active_region` tinyint(1) NOT NULL DEFAULT '0',
  `active_zone` tinyint(1) NOT NULL DEFAULT '0',
  `active_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `active_currency` tinyint(1) NOT NULL DEFAULT '0',
  `method` varchar(100) NOT NULL DEFAULT '',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL default '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_countries` (
  `payment_id` int(11) NOT NULL DEFAULT '0',
  `country_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_payment` (`payment_id`, `country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_regions` (
  `payment_id` int(11) NOT NULL DEFAULT '0',
  `region_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_payment` (`payment_id`, `region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_zones` (
  `payment_id` int(11) NOT NULL DEFAULT '0',
  `zone_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_payment` (`payment_id`, `zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_shipping` (
  `payment_id` int(11) NOT NULL DEFAULT '0',
  `shipping_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_payment` (`payment_id`, `shipping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_currencies` (
  `payment_id` int(11) NOT NULL DEFAULT '0',
  `currency_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_payment` (`payment_id`, `currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `review` text,
  `rating` int(1) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_form_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL DEFAULT '',
  `label` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `id_input` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL DEFAULT '',
  `predefined_values` text,
  `predefined_values_first_option` varchar(100) NOT NULL DEFAULT '',
  `type_default` tinyint(3) NOT NULL DEFAULT '0',
  `default` varchar(255) NOT NULL DEFAULT '',
  `size` varchar(50) NOT NULL DEFAULT '',
  `cols` varchar(5) NOT NULL DEFAULT '',
  `rows` varchar(5) NOT NULL DEFAULT '',
  `class` varchar(100) NOT NULL DEFAULT '',
  `filter` varchar(25) NOT NULL DEFAULT '',
  `read_only` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `validate` varchar(50) NOT NULL DEFAULT '',
  `autocomplete` varchar(50),
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `preicon` varchar(100) NOT NULL DEFAULT '',
  `format` varchar(50) NOT NULL DEFAULT '',
  `pattern` text,
  `maxlength` int(11) NOT NULL DEFAULT '0',
  `additional` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `display_billing` tinyint(1) NOT NULL DEFAULT '0',
  `display_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `display_account` tinyint(1) NOT NULL DEFAULT '0',
  `display_docs` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ba_sa` tinyint(1) NOT NULL DEFAULT '0',
  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `loyalty_card_number` varchar(30) NOT NULL DEFAULT '',
	`name_first` varchar(100) NOT NULL DEFAULT '',
	`name_middle` varchar(100) NOT NULL DEFAULT '',
	`name_last` varchar(100) NOT NULL DEFAULT '',
	`name_degree` varchar(100) NOT NULL DEFAULT '',
	`company` varchar(255) NOT NULL DEFAULT '',
	`vat_1` varchar(25) NOT NULL DEFAULT '',
	`vat_2` varchar(25) NOT NULL DEFAULT '',
	`vat_valid` tinyint(1) NOT NULL DEFAULT '0',
	`address_1` varchar(255) NOT NULL DEFAULT '',
	`address_2` varchar(255) NOT NULL DEFAULT '',
	`city` varchar(255) NOT NULL DEFAULT '',
	`zip` varchar(20) NOT NULL DEFAULT '',
	`country` int(11) NOT NULL DEFAULT '0',
	`region` int(11) NOT NULL DEFAULT '0',
	`email` varchar(100) NOT NULL DEFAULT '',
	`email_contact` varchar(100) NOT NULL DEFAULT '',
	`phone_1` varchar(20) NOT NULL DEFAULT '',
	`phone_2` varchar(20) NOT NULL DEFAULT '',
	`phone_mobile` varchar(20) NOT NULL DEFAULT '',
	`fax` varchar(20) NOT NULL DEFAULT '',
  `params_user` text,
  `privacy` tinyint(1) NOT NULL default '0',
  `date` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_phocacart_users` (`type`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

/*
CREATE TABLE IF NOT EXISTS `#__phocacart_cart` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `cart` text,
  `shipping` int(11) NOT NULL DEFAULT '0',
  `coupon` int(11) NOT NULL DEFAULT '0',
  `payment` int(11) NOT NULL DEFAULT '0',
  `reward` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
*/
CREATE TABLE IF NOT EXISTS `#__phocacart_cart_multiple` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  `unit_id` int(11) NOT NULL DEFAULT '0',
  `section_id` int(11) NOT NULL DEFAULT '0',
  `loyalty_card_number` varchar(30) NOT NULL DEFAULT '',
  `cart` text,
  `params_shipping` text,
  `params_payment` text,
  `shipping` int(11) NOT NULL DEFAULT '0',
  `coupon` int(11) NOT NULL DEFAULT '0',
  `payment` int(11) NOT NULL DEFAULT '0',
  `reward` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL default '0',
  `date` datetime NOT NULL,
  KEY `idx_uvtus` (`user_id`, `vendor_id`, `ticket_id`, `unit_id`, `section_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_vendor_id` (`vendor_id`),
  KEY `idx_section_id` (`section_id`),
  KEY `idx_unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS `#__phocacart_order_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `code` char(5) NOT NULL DEFAULT '',
  `description` text,
  `stock_movements` char(1) NOT NULL DEFAULT '',
  `change_user_group` tinyint(1) NOT NULL DEFAULT '0',
  `change_points_needed` tinyint(1) NOT NULL DEFAULT '0',
  `change_points_received` tinyint(1) NOT NULL DEFAULT '0',
  `email_customer` tinyint(1) NOT NULL DEFAULT '0',
  `email_others` text,
  `email_text` text,
  `email_footer` text,
  `email_text_others` text,
  `email_downloadlink_description` text,
  `email_attachments` text,
  `email_subject` varchar(255) NOT NULL DEFAULT '',
  `email_subject_others` varchar(255) NOT NULL DEFAULT '',
  `email_send` int(2) NOT NULL DEFAULT '0',
  `email_send_format` int(2) NOT NULL DEFAULT '0',
  `activate_gift` tinyint(1) NOT NULL DEFAULT '0',
  `email_gift` tinyint(1) NOT NULL DEFAULT '0',
  `email_subject_gift_sender` varchar(255) NOT NULL DEFAULT '',
  `email_text_gift_sender` text,
  `email_subject_gift_recipient` varchar(255) NOT NULL DEFAULT '',
  `email_text_gift_recipient` text,
  `email_gift_format` tinyint(1) NOT NULL DEFAULT '0',
  `orders_view_display` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `download` tinyint(1) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_orders` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`order_token` char(64) NOT NULL DEFAULT '',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`group_id` int(11) NOT NULL DEFAULT '0',
	`invoice_id` int(11) NOT NULL DEFAULT '0',
	`credit_id` int(11) NOT NULL DEFAULT '0',
	`status_id` int(11) NOT NULL DEFAULT '0',
	`shipping_id` int(11) NOT NULL DEFAULT '0',
	`payment_id` int(11) NOT NULL DEFAULT '0',
	`coupon_id` int(11) NOT NULL DEFAULT '0',
	`discount_id` int(11) NOT NULL DEFAULT '0',
	`currency_id` int(11) NOT NULL DEFAULT '0',
	`amount_pay` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
	`amount_tendered` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
	`amount_change` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
	`type` tinyint(3) NOT NULL default '0',
	`vendor_id` int(11) NOT NULL DEFAULT '0',
	`ticket_id` int(11) NOT NULL DEFAULT '0',
	`unit_id` int(11) NOT NULL DEFAULT '0',
	`section_id` int(11) NOT NULL DEFAULT '0',
	`loyalty_card_number` varchar(30) NOT NULL DEFAULT '',
	`tax_calculation` int(11) NOT NULL DEFAULT '0',
	`currency_code` varchar(5) NOT NULL DEFAULT '',
	`currency_exchange_rate` DECIMAL( 15, 8 ) NOT NULL DEFAULT '0',
	`unit_weight` varchar(50) NOT NULL DEFAULT '',
	`unit_volume` varchar(50) NOT NULL DEFAULT '',
	`unit_size` varchar(50) NOT NULL DEFAULT '',
	`title` varchar(255) NOT NULL DEFAULT '',
	`alias` varchar(255) NOT NULL DEFAULT '',
	`comment` text,
	`ip` varchar(46) NOT NULL DEFAULT '',
	`user_agent` varchar(255) NOT NULL DEFAULT '',
	`tracking_id` int(11) NOT NULL DEFAULT '0',
	`tracking_title` varchar(255) NOT NULL DEFAULT '',
	`tracking_description_custom` text,
	`tracking_link_custom` varchar(255) NOT NULL DEFAULT '',
	`tracking_number` varchar(255) NOT NULL DEFAULT '',
	`tracking_date_shipped` datetime NOT NULL,
	`reference_field1` varchar(128) NOT NULL DEFAULT '',
	`reference_field2` varchar(128) NOT NULL DEFAULT '',
	`reference_data` text,
	`order_number` varchar(64) NOT NULL DEFAULT '',
	`receipt_number` varchar(64) NOT NULL DEFAULT '',
	`invoice_number` varchar(64) NOT NULL DEFAULT '',
	`credit_number` varchar(64) NOT NULL DEFAULT '',
	`queue_number` varchar(64) NOT NULL DEFAULT '',
	`order_number_id` int(11) NOT NULL DEFAULT '0',
	`receipt_number_id` int(11) NOT NULL DEFAULT '0',
	`invoice_number_id` int(11) NOT NULL DEFAULT '0',
	`credit_number_id` int(11) NOT NULL DEFAULT '0',
	`queue_number_id` int(11) NOT NULL DEFAULT '0',
	`invoice_date` datetime NOT NULL,
	`invoice_due_date` datetime NOT NULL,
	`invoice_time_of_supply` datetime NOT NULL,
	`invoice_prn` varchar(64) NOT NULL DEFAULT '',
    `required_delivery_time` datetime NOT NULL,
	`invoice_spec_top_desc` text,
	`invoice_spec_middle_desc` text,
	`invoice_spec_bottom_desc` text,
	`oidn_spec_billing_desc` text,
	`oidn_spec_shipping_desc` text,
	`terms` tinyint(1) NOT NULL default '0',
	`privacy` tinyint(1) NOT NULL default '0',
	`newsletter` tinyint(1) NOT NULL default '0',
	`published` tinyint(1) NOT NULL DEFAULT '0',
	`checked_out` int unsigned,
	`checked_out_time` datetime,
	`ordering` int(11) NOT NULL DEFAULT '0',
	`date` datetime NOT NULL,
	`modified` datetime NOT NULL,
	`payment_date` datetime,
	`params` text,
	`params_shipping` text,
	`params_payment` text,
	`params_user` text,
	`user_lang` char(7) NOT NULL DEFAULT '',
	`default_lang` char(7) NOT NULL DEFAULT '',
	`language` char(7) NOT NULL DEFAULT '',
    `internal_comment` text,
	PRIMARY KEY (`id`),
	KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_order_users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`order_id` int(11) NOT NULL DEFAULT '0',
	`user_token` char(64) NOT NULL DEFAULT '',
	`user_address_id` int(11) NOT NULL DEFAULT '0',
	`user_groups` text,
	`type` tinyint(1) NOT NULL DEFAULT '0',
	`ba_sa` tinyint(1) NOT NULL DEFAULT '0',
	`name_first` varchar(100) NOT NULL DEFAULT '',
	`name_middle` varchar(100) NOT NULL DEFAULT '',
	`name_last` varchar(100) NOT NULL DEFAULT '',
	`name_degree` varchar(100) NOT NULL DEFAULT '',
	`company` varchar(255) NOT NULL DEFAULT '',
	`vat_1` varchar(25) NOT NULL DEFAULT '',
	`vat_2` varchar(25) NOT NULL DEFAULT '',
	`address_1` varchar(255) NOT NULL DEFAULT '',
	`address_2` varchar(255) NOT NULL DEFAULT '',
	`city` varchar(255) NOT NULL DEFAULT '',
	`zip` varchar(20) NOT NULL DEFAULT '',
	`country` int(11) NOT NULL DEFAULT '0',
	`region` int(11) NOT NULL DEFAULT '0',
	`email` varchar(100) NOT NULL DEFAULT '',
	`email_contact` varchar(100) NOT NULL DEFAULT '',
	`phone_1` varchar(20) NOT NULL DEFAULT '',
	`phone_2` varchar(20) NOT NULL DEFAULT '',
	`phone_mobile` varchar(20) NOT NULL DEFAULT '',
	`fax` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  FULLTEXT KEY `idx_fulltext` (`name_first`,`name_middle`,`name_last`,`name_degree`,`company`,`vat_1`,`vat_2`,`address_1`,`address_2`,`city`,`zip`,`email`,`email_contact`,`phone_1`,`phone_2`,`phone_mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `owner_id` int(11) NOT NULL DEFAULT '0',
  `product_id_key` text,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `sku` varchar(255) NOT NULL DEFAULT '',
  `netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `tax` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `dnetto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `dtax` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `dbrutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `dtype` int(11) NOT NULL DEFAULT '0',
  `damount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `stock_calculation` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT  '0',
  `volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT  '0',
  `points_received` int(11) NOT NULL DEFAULT '0',
  `points_needed` int(11) NOT NULL DEFAULT '0',
  `default_price` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `default_tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
  `default_tax_id` int(11) NOT NULL DEFAULT '0',
  `default_tax_id_c` int(11) NOT NULL DEFAULT '0',
  `default_tax_id_r` int(11) NOT NULL DEFAULT '0',
  `default_tax_id_p` int(11) NOT NULL DEFAULT '0',
  `default_calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `default_points_received` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `order_product_id` int(11) NOT NULL DEFAULT '0',
  `attribute_id` int(11) NOT NULL DEFAULT '0',
  `option_id` int(11) NOT NULL DEFAULT '0',
  `attribute_title` varchar(255) NOT NULL DEFAULT '',
  `option_title` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `option_value` text,
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `coupon_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `discount_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(3) NOT NULL default '0',
  `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_product_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `order_product_id` int(11) NOT NULL DEFAULT '0',
  `discount_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `product_id_key` text,
  `type` tinyint(3) NOT NULL default '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `tax` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `final` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_total` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`order_id` int(11) NOT NULL DEFAULT '0',
	`item_id` int(11) NOT NULL DEFAULT '0',
	`item_id_c` int(11) NOT NULL DEFAULT '0',
	`item_id_r` int(11) NOT NULL DEFAULT '0',
	`item_id_p` int(11) NOT NULL DEFAULT '0',
	`title` varchar(255) NOT NULL DEFAULT '',
	`title_lang` varchar(255) NOT NULL DEFAULT '',
	`title_lang_suffix` varchar(100) NOT NULL DEFAULT '',
	`title_lang_suffix2` varchar(100) NOT NULL DEFAULT '',
	`type` varchar(50) NOT NULL DEFAULT '',
	`amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`amount_currency` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`text` varchar(255) NOT NULL DEFAULT '',
	`checked_out` int unsigned,
	`checked_out_time` datetime,
	`ordering` int(11) NOT NULL DEFAULT '0',
	`published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_tax_recapitulation` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`order_id` int(11) NOT NULL DEFAULT '0',
	`item_id` int(11) NOT NULL DEFAULT '0',
	`item_id_c` int(11) NOT NULL DEFAULT '0',
	`item_id_r` int(11) NOT NULL DEFAULT '0',
	`item_id_p` int(11) NOT NULL DEFAULT '0',
	`title` varchar(255) NOT NULL DEFAULT '',
	`title_lang` varchar(255) NOT NULL DEFAULT '',
	`title_lang_suffix` varchar(100) NOT NULL DEFAULT '',
	`title_lang_suffix2` varchar(100) NOT NULL DEFAULT '',
	`type` varchar(50) NOT NULL DEFAULT '',
	`amount_netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`amount_tax` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`amount_brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`amount_brutto_currency` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
	`checked_out` int unsigned,
	`checked_out_time` datetime,
	`ordering` int(11) NOT NULL DEFAULT '0',
	`published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_history` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`order_id` int(11) NOT NULL DEFAULT '0',
	`order_status_id` int(11) NOT NULL DEFAULT '0',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`notify` tinyint(1) NOT NULL DEFAULT '0',
	`comment` varchar(255) NOT NULL DEFAULT '',
	`date` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `attribute_id` int(11) NOT NULL DEFAULT '0',
  `option_id` int(11) NOT NULL DEFAULT '0',
  `order_product_id` int(11) NOT NULL DEFAULT '0',
  `order_attribute_id` int(11) NOT NULL DEFAULT '0',
  `order_option_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `download_token` char(64) NOT NULL DEFAULT '',
  `download_folder` varchar(255) NOT NULL DEFAULT '',
  `download_file` varchar(255) NOT NULL DEFAULT '',
  `download_hits` int(11) NOT NULL DEFAULT '0',
  `download_days` int(11) NOT NULL DEFAULT '-1',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL default '0',
  `type_id` int(11) NOT NULL default '0',
  `type` tinyint(3) NOT NULL default '0',
  `priority` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(46) NOT NULL default '',
  `incoming_page` text,
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `header` text,
  `footer` text,
  `root` varchar(64) NOT NULL DEFAULT '',
  `item` varchar(64) NOT NULL DEFAULT '',
  `feed_plugin` varchar(64) NOT NULL DEFAULT '',
  `item_params` text,
  `feed_params` text,
  `params` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL DEFAULT '',
  `currency_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_wishlists` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`product_id` int(11) NOT NULL DEFAULT '0',
	`category_id` int(11) NOT NULL DEFAULT '0',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`title` varchar(255) NOT NULL DEFAULT '',
	`alias` varchar(255) NOT NULL DEFAULT '',
	`wishlist` text,
	`ip` varchar(46) NOT NULL DEFAULT '',
	`user_agent` varchar(255) NOT NULL DEFAULT '',
	`quantity` int(11) NOT NULL DEFAULT '0',
	`type` tinyint(1) NOT NULL DEFAULT '0',
	`priority` tinyint(1) NOT NULL DEFAULT '0',
	`published` tinyint(1) NOT NULL DEFAULT '0',
	`checked_out` int unsigned,
	`checked_out_time` datetime,
	`ordering` int(11) NOT NULL DEFAULT '0',
	`date` datetime NOT NULL,
	`params` text,
    `language` char(7) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `idx_product_user` (`product_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_questions` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `product_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `question_id` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `phone_mobile` varchar(20) NOT NULL default '',
  `ip` varchar(46) NOT NULL default '',
  `title` varchar(200) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `message` text,
  `date` datetime NOT NULL,
  `privacy` tinyint(1) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `published` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_import` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `row_id` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `item` mediumtext,
  `type` int(3) NOT NULL default '0',
  `file_type` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_export` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `row_id` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `item` mediumtext,
  `type` int(3) NOT NULL default '0',
  `file_type` int(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_hits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `item` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(46) NOT NULL default '',
  `type` tinyint(3) NOT NULL default '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_groups` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `display_price` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `display_price_method` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `display_addtocart` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `display_attributes` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `minimum_sum` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `valid_from` datetime NULL DEFAULT NULL,
  `valid_to` datetime NULL DEFAULT NULL,
  `activate_registration` int(1) NOT NULL DEFAULT 0,
  `type` tinyint(3) NOT NULL default '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_reward_points` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`order_id` int(11) NOT NULL DEFAULT '0',
	`title` varchar(255) NOT NULL DEFAULT '',
	`alias` varchar(255) NOT NULL DEFAULT '',
	`points` int(11) NOT NULL DEFAULT '0',
	`type` tinyint(3) NOT NULL default '0',
	`description` text,
	`published` tinyint(1) NOT NULL DEFAULT '0',
	`checked_out` int unsigned,
	`checked_out_time` datetime,
	`ordering` int(11) NOT NULL DEFAULT '0',
	`date` datetime NOT NULL,
	`params` text,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


-- access tables - one table replace all the listed tables - tables are identified by type
-- #__phocacart_categories
-- #__phocacart_products
-- #__phocacart_coupons
-- #__phocacart_discounts
-- #__phocacart_shipping_methods
-- #__phocacart_payment_methods
-- #__phocacart_product_discounts
-- #__phocacart_form_fields
CREATE TABLE IF NOT EXISTS `#__phocacart_item_access` (
  `item_id` int(11) NOT NULL DEFAULT '0',
  `access_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL DEFAULT '0',
  KEY `idx_itemaccess` (`item_id`, `access_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- group tables - one table replace all the listed tables - tables are identified by type
-- #__phocacart_categories
-- #__phocacart_products
-- #__phocacart_coupons
-- #__phocacart_discounts
-- #__phocacart_shipping_methods
-- #__phocacart_payment_methods
-- #__phocacart_product_discounts - product_id column needed
-- #__phocacart_form_fields
-- #__phocacart_users
CREATE TABLE IF NOT EXISTS `#__phocacart_item_groups` (
   `item_id` int(11) NOT NULL DEFAULT '0',
   `group_id` int(11) NOT NULL DEFAULT '0',
   `product_id` int(11) NOT NULL DEFAULT '0',
   `type` tinyint(3) NOT NULL DEFAULT '0',
   KEY `idx_itemgroup` (`item_id`, `group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__phocacart_vendors` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_sections` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

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
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `cat_idx` (`published`,`access`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_published` (`published`),
  KEY `idx_alias` (`alias`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

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
  `date` datetime NOT NULL,
  `description` text,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_submit_items` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `ip` varchar(46) NOT NULL default '',
  `items_item` text,
  `items_contact` text,
  `items_parameter` text,
  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `upload_token` char(64) NOT NULL DEFAULT '',
  `upload_folder` varchar(255) NOT NULL DEFAULT '',
  `date_submit` datetime NOT NULL,
  `privacy` tinyint(1) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `language` char(7) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `published` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_product_bundles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `main_product_id` int(11) NOT NULL DEFAULT 0,
  `child_product_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `main_product_id` (`main_product_id`),
  KEY `child_product_id` (`child_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_categories_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `title_long` varchar(255),
    `alias` varchar(255),
    `title_feed` varchar(255),
    `description` text,
    `description_bottom` text,
    `metatitle` varchar(255),
    `metakey` text,
    `metadesc` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_manufacturers_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `title_long` varchar(255),
    `alias` varchar(255),
    `link` varchar(255),
    `description` text,
    `metatitle` varchar(255),
    `metakey` text,
    `metadesc` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_products_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `title_long` varchar(255),
    `alias` varchar(255),
    `description` text,
    `description_long` text,
    `features` text,
    `metatitle` varchar(255),
    `metakey` text,
    `metadesc` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_attributes_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_attribute_values_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_specification_groups_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_specifications_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    `value` varchar(255),
    `alias_value` varchar(255),
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`),
    KEY `idx_alias_value` (`alias_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_shipping_methods_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    `description` text,
    `description_info` text,
    `tracking_title` varchar(255),
    `tracking_description` text,
    `tracking_link` varchar(255),
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_payment_methods_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    `description` text,
    `description_info` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_tags_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    `description` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_parameters_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    `title_header` varchar(255),
    `description` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_parameter_values_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    `description` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_coupons_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    `description` text,
    `gift_description` text,
    `gift_sender_message` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_discounts_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    `description` text,
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_taxes_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_content_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `context` varchar(255) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 0,
    `checked_out` int unsigned,
    `checked_out_time` datetime,
    `ordering` int(11) NOT NULL DEFAULT 0,
    `params` text,
    PRIMARY KEY (`id`),
    KEY `idx_context` (`context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


-- 6.1.0
--
-- Table structure for table `#__phocacart_subscriptions`
--
-- Status values:
-- 1: Active
-- 2: Future
-- 3: Expired
-- 4: On Hold
-- 5: Pending
-- 6: Failed
-- 7: In Trial
-- 8: Card Expired
-- 9: Canceled
--
CREATE TABLE IF NOT EXISTS `#__phocacart_subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '5',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `next_billing_date` datetime DEFAULT NULL,
  `trial_end_date` datetime DEFAULT NULL,
  `signup_fee_paid` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `renewal_count` int(11) unsigned NOT NULL DEFAULT 0,
  `cancelation_reason` varchar(100) DEFAULT NULL,
  `canceled_date` datetime DEFAULT NULL,
  `params` text DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_product_active` (`user_id`,`product_id`,`status`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_end_date` (`end_date`),
  KEY `idx_next_billing` (`next_billing_date`),
  KEY `idx_status_end_date` (`status`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `attribute_id` int(11) NOT NULL DEFAULT '0',
  `option_id` int(11) NOT NULL DEFAULT '0',
  `order_product_id` int(11) NOT NULL DEFAULT '0',
  `order_attribute_id` int(11) NOT NULL DEFAULT '0',
  `order_option_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `subscription_period` smallint(5) unsigned DEFAULT NULL,
  `subscription_unit` TINYINT(3) UNSIGNED DEFAULT NULL,
  `subscription_signup_fee` decimal(15,4) DEFAULT NULL,
  `subscription_renewal_discount` decimal(15,4) DEFAULT NULL,
  `subscription_renewal_discount_calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `subscription_order_signup_fee` decimal(15,4) DEFAULT NULL,
  `subscription_order_renewal_discount` decimal(15,4) DEFAULT NULL,
  `subscription_order_base_price` decimal(15,4) DEFAULT NULL,
  `subscription_order_total_price` decimal(15,4) DEFAULT NULL,
  `subscription_usergroup_add` text,
  `subscription_usergroup_remove` text,
  `subscription_trial_enabled` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `subscription_trial_period` smallint(5) unsigned DEFAULT NULL,
  `subscription_trial_unit` TINYINT(3) UNSIGNED DEFAULT NULL,
  `subscription_grace_period_days` smallint(5) unsigned DEFAULT 0,
  `subscription_max_renewals` int(11) DEFAULT NULL,
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__phocacart_subscription_history`
--
CREATE TABLE IF NOT EXISTS `#__phocacart_subscription_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) unsigned NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `status_from` int(11) DEFAULT NULL,
  `status_to` int(11) NOT NULL,
  `event_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `triggered_by` varchar(100) NOT NULL,
  `notify_user` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_subscription_id` (`subscription_id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__phocacart_subscription_acl`
--
CREATE TABLE IF NOT EXISTS `#__phocacart_subscription_acl` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) unsigned NOT NULL,
  `action` enum('add','remove') NOT NULL,
  `group_id` int(11) NOT NULL,
  `applied_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_subscription_id` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Data
INSERT INTO `#__phocacart_order_statuses` (`id`, `title`, `code`, `published`, `ordering`, `stock_movements`, `type`, `download`, `change_user_group`, `change_points_needed`, `change_points_received`, `orders_view_display`, `date`) VALUES
(1, 'COM_PHOCACART_STATUS_PENDING', 'P', '1', '1', '-', '1', '0', '0', '1', '0', '[1]', CURRENT_TIMESTAMP),
(2, 'COM_PHOCACART_STATUS_CONFIRMED', 'C', '1', '2', '=', '1', '0', '1', '1', '1', '[1,3]', CURRENT_TIMESTAMP),
(3, 'COM_PHOCACART_STATUS_CANCELED', 'CL', '1', '3', '+', '1', '0', '1', '2', '2', '[1]', CURRENT_TIMESTAMP),
(4, 'COM_PHOCACART_STATUS_SHIPPED', 'S', '1', '4', '=', '1', '0', '1', '1', '1', '[1,2,3]', CURRENT_TIMESTAMP),
(5, 'COM_PHOCACART_STATUS_REFUNDED', 'RF', '1', '5', '=', '1', '0', '1', '2', '2', '[1]', CURRENT_TIMESTAMP),
(6, 'COM_PHOCACART_STATUS_COMPLETED', 'CE', '1', '6', '=', '1', '1', '1', '1', '1', '[1,2,3]', CURRENT_TIMESTAMP),

(7, 'COM_PHOCACART_STATUS_FAILED', 'E', '0', '7', '=', '1', '0', '1', '2', '2', '[1]', CURRENT_TIMESTAMP),
(8, 'COM_PHOCACART_STATUS_DENIED', 'D', '0', '8', '=', '1', '0', '1', '2', '2', '[1]', CURRENT_TIMESTAMP),
(9, 'COM_PHOCACART_STATUS_CANCELED_REVERSAL', 'CRV', '0', '9', '=', '1', '0', '1', '2', '2', '[1]', CURRENT_TIMESTAMP),
(10, 'COM_PHOCACART_STATUS_REVERSED', 'RV', '0', '10', '=', '1', '0', '1', '2', '2', '[1]', CURRENT_TIMESTAMP);

-- -
INSERT INTO `#__phocacart_stock_statuses` (`id`, `title`, `published`, `ordering`, `date`) VALUES (NULL, 'COM_PHOCACART_STATUS_OUT_OF_STOCK', '1', '1', CURRENT_TIMESTAMP);
INSERT INTO `#__phocacart_stock_statuses` (`id`, `title`, `published`, `ordering`, `date`) VALUES (NULL, 'COM_PHOCACART_STATUS_IN_STOCK', '1', '2', CURRENT_TIMESTAMP);
INSERT INTO `#__phocacart_stock_statuses` (`id`, `title`, `published`, `ordering`, `date`) VALUES (NULL, 'COM_PHOCACART_STATUS_2_3_DAYS', '1', '3', CURRENT_TIMESTAMP);
-- -
INSERT INTO `#__phocacart_currencies` (`id`, `title`, `code`, `exchange_rate`, `price_currency_symbol`, `price_format`, `price_dec_symbol`, `price_decimals`, `price_thousands_sep`, `price_suffix`, `price_prefix`, `published`) VALUES
(1, 'Euro', 'EUR', 1, '€', 3, ',', 2, '.', '', '', 1);

-- -
INSERT INTO `#__phocacart_form_fields` ( `title`, `label`, `description`, `type`, `type_default`, `published`, `display_billing`, `display_shipping`, `display_account`, `required`, `read_only`, `filter`, `unique`, `class`, `validate`, `ordering`, `access`, `autocomplete`) VALUES
('name_first', 'COM_PHOCACART_FIRST_NAME_LABEL', 'COM_PHOCACART_FIRST_NAME_DESC', 'text:varchar(100)', 1, 1, 1, 1, 1, 1, 0, '', 0, '', '', 1, 1, 'given-name'),
('name_middle', 'COM_PHOCACART_MIDDLE_NAME_LABEL', 'COM_PHOCACART_MIDDLE_NAME_DESC', 'text:varchar(100)', 1, 0, 0, 0, 0, 0, 0, '', 0, '', '', 2, 1, 'additional-name'),
('name_last', 'COM_PHOCACART_LAST_NAME_LABEL', 'COM_PHOCACART_LAST_NAME_DESC', 'text:varchar(100)', 1, 1, 1, 1, 1, 1, 0, '', 0, '', '', 3, 1, 'family-name'),
('name_degree', 'COM_PHOCACART_DEGREE_LABEL', 'COM_PHOCACART_DEGREE_DESC', 'text:varchar(100)', 1, 0, 0, 0, 0, 0, 0, '', 0, '', '', 4, 1, 'honorific-prefix'),
('company', 'COM_PHOCACART_COMPANY_LABEL', 'COM_PHOCACART_COMPANY_DESC', 'text:varchar(255)', 1, 1, 1, 1, 1, 0, 0, '', 0, '', '', 5, 1, 'organization'),
('vat_1', 'COM_PHOCACART_VAT_1_LABEL', 'COM_PHOCACART_VAT_1_DESC', 'text:varchar(25)', 1, 0, 0, 0, 0, 0, 0, '', 0, '', '', 6, 1, null),
('vat_2', 'COM_PHOCACART_VAT_2_LABEL', 'COM_PHOCACART_VAT_2_DESC', 'text:varchar(25)', 1, 0, 0, 0, 0, 0, 0, '', 0, '', '', 7, 1, null),
('address_1', 'COM_PHOCACART_ADDRESS_1_LABEL', 'COM_PHOCACART_ADDRESS_1_DESC', 'text:varchar(255)', 1, 1, 1, 1, 1, 1, 0, '', 0, '', '', 8, 1, 'street-address'),
('address_2', 'COM_PHOCACART_ADDRESS_2_LABEL', 'COM_PHOCACART_ADDRESS_2_DESC', 'text:varchar(255)', 1, 0, 0, 0, 0, 0, 0, '', 0, '', '', 9, 1, null),
('zip', 'COM_PHOCACART_ZIP_LABEL', 'COM_PHOCACART_ZIP_DESC', 'text:varchar(20)', 1, 1, 1, 1, 1, 1, 0, '', 0, '', '', 10, 1, 'postal-code'),
('city', 'COM_PHOCACART_CITY_LABEL', 'COM_PHOCACART_CITY_DESC', 'text:varchar(255)', 1, 1, 1, 1, 1, 1, 0, '', 0, '', '', 11, 1, 'address-level2'),
('country', 'COM_PHOCACART_COUNTRY_LABEL', 'COM_PHOCACART_COUNTRY_DESC', 'phocaformcountry:int(11)', 1, 1, 1, 1, 1, 1, 0, '', 0, '', '', 12, 1, 'country-name'),
('region', 'COM_PHOCACART_REGION_LABEL', 'COM_PHOCACART_REGION_DESC', 'phocaformregion:int(11)', 1, 1, 1, 1, 1, 0, 0, '', 0, '', '', 13, 1, 'address-level1'),
('email', 'COM_PHOCACART_EMAIL_LABEL', 'COM_PHOCACART_EMAIL_DESC', 'text:varchar(100)', 1, 1, 1, 0, 1, 1, 2, 'string', 1, '', 'email', 14, 1, 'email'),
('email_contact', 'COM_PHOCACART_CONTACT_EMAIL_LABEL', 'COM_PHOCACART_CONTACT_EMAIL_DESC', 'text:varchar(100)', 1, 0, 0, 0, 0, 0, 0, 'string', 1, '', 'email', 15, 1, 'email'),
('phone_1', 'COM_PHOCACART_PHONE_1_LABEL', 'COM_PHOCACART_PHONE_1_DESC', 'text:varchar(20)', 1, 0, 0, 0, 0, 0, 0, '',0, '', '', 16, 1, 'tel'),
('phone_2', 'COM_PHOCACART_PHONE_2_LABEL', 'COM_PHOCACART_PHONE_2_DESC', 'text:varchar(20)', 1, 0, 0, 0, 0, 0, 0, '', 0, '', '', 17, 1, 'tel'),
('phone_mobile', 'COM_PHOCACART_MOBILE_PHONE_LABEL', 'COM_PHOCACART_MOBILE_PHONE_DESC', 'text:varchar(20)', 1, 0, 0, 0, 0, 0, 0, '', 0, '', '', 18, 1, 'tel'),
('fax', 'COM_PHOCACART_FAX_LABEL', 'COM_PHOCACART_FAX_DESC', 'text:varchar(20)', 1, 0, 0, 0, 0, 0, 0, '', 0, '', '', 19, 1, null);

INSERT INTO `#__phocacart_groups` (`id`, `title`, `published`, `display_price`, `display_addtocart`, `display_attributes`, `ordering`, `type`) VALUES (1, 'COM_PHOCACART_DEFAULT', '1', '1', '1', '1', '1', '1');

INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.watchdog', 'com_phocacart', '', 'COM_PHOCACART_EMAIL_WATCHDOG_SUBJECT', 'COM_PHOCACART_EMAIL_WATCHDOG_BODY', 'COM_PHOCACART_EMAIL_WATCHDOG_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","products","product_title","product_sku","product_link","product_url","site_name","site_link","site_url"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.question', 'com_phocacart', '', 'COM_PHOCACART_EMAIL_QUESTION_SUBJECT', 'COM_PHOCACART_EMAIL_QUESTION_BODY', 'COM_PHOCACART_EMAIL_QUESTION_HTMLBODY', '', '{"tags":["name","email","phone","product_title","product_long_title","product_sku","product_link","product_url","category_title","category_long_title","category_link","category_url","site_name","site_link","site_url"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.question.admin', 'com_phocacart', '', 'COM_PHOCACART_EMAIL_QUESTION_ADMIN_SUBJECT', 'COM_PHOCACART_EMAIL_QUESTION_ADMIN_BODY', 'COM_PHOCACART_EMAIL_QUESTION_ADMIN_HTMLBODY', '', '{"tags":["name","email","phone","product_title","product_long_title","product_sku","product_link","product_url","category_title","category_long_title","category_link","category_url","site_name","site_link","site_url"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.submit_item', 'com_phocacart', '', 'COM_PHOCACART_EMAIL_SUBMIT_ITEM_SUBJECT', 'COM_PHOCACART_EMAIL_SUBMIT_ITEM_BODY', 'COM_PHOCACART_EMAIL_SUBMIT_ITEM_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","products","product_title","product_sku","product_url","site_name","site_url"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.submit_item.admin', 'com_phocacart', '', 'COM_PHOCACART_EMAIL_SUBMIT_ITEM_ADMIN_SUBJECT', 'COM_PHOCACART_EMAIL_SUBMIT_ITEM_ADMIN_BODY', 'COM_PHOCACART_EMAIL_SUBMIT_ITEM_ADMIN_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","products","product_title","product_sku","product_url","site_name","site_url"]}');

INSERT INTO `#__phocacart_content_types` (`id`, `title`, `context`, `published`, `ordering`, `params`)
    VALUES (1, 'COM_PHOCACART_CONTENT_TYPE_CATEGORY_DEFAULT', 'category', 1, 1, '{}');
INSERT INTO `#__phocacart_content_types` (`id`, `title`, `context`, `published`, `ordering`, `params`)
    VALUES (2, 'COM_PHOCACART_CONTENT_TYPE_RELATED_DEFAULT', 'product_related', 1, 1, '{}');

-- 6.1.0 Subscription email templates
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.activated', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","subscription_status","renewal_count","site_name","site_link","site_url","account_url","renewal_url","html.document","text.document"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.renewed', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","subscription_status","renewal_count","site_name","site_link","site_url","account_url","renewal_url","html.document","text.document"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.expiring_soon', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_SOON_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_SOON_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_SOON_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","days_remaining","subscription_status","renewal_count","site_name","site_link","site_url","account_url","renewal_url","html.document","text.document"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.expired', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","subscription_status","renewal_count","site_name","site_link","site_url","account_url","resubscribe_url","html.document","text.document"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.canceled', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","cancellation_date","cancellation_reason","subscription_status","site_name","site_link","site_url","account_url","resubscribe_url","html.document","text.document"]}');
INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.status_changed', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_STATUS_CHANGED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_STATUS_CHANGED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_STATUS_CHANGED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","subscription_status","renewal_count","site_name","site_link","site_url","account_url","html.document","text.document"]}');

-- UTF-8 test: ä,ö,ü
-- Using text NOT NULL DEFAULT '' instead of text NOT NULL DEFAULT '' NOT NULL because of possible problems with strict rules: Field 'text' doesn't have a default value
-- all formats are problematic:
-- text not null default ''
-- text not null
-- text
-- so reverting back to "text" only

-- ----------------
-- ALPHA1 -> ALPHA2
-- ----------------

-- `type` tinyint(1) NOT NULL DEFAULT '0' added - `#__phocacart_order_statuses`
-- `download` tinyint(1) NOT NULL DEFAULT '0' added - `#__phocacart_order_statuses`
-- `email_text` text added - `#__phocacart_order_statuses`
-- `email_subject` varchar(255) added - `#__phocacart_order_statuses`
-- `email_send` int(2) NOT NULL DEFAULT '0' - `#__phocacart_order_statuses`
-- `type` tinyint(1) NOT NULL DEFAULT '0' added - `#__phocacart_stock_statuses`

-- `weight` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0' added - `#__phocacart_order_products`
-- `volume` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0' added - `#__phocacart_order_products`

-- `unit_weight` varchar(50) NOT NULL DEFAULT '' - `#__phocacart_orders`
-- `unit_volume` varchar(50) NOT NULL DEFAULT '' - `#__phocacart_orders`

-- `lowest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0', - `#__phocacart_payment_methods`
-- `highest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0', - `#__phocacart_payment_methods`
-- `active_amount` tinyint(1) NOT NULL DEFAULT '0', - `#__phocacart_payment_methods`
-- `active_country` tinyint(1) NOT NULL DEFAULT '0', - `#__phocacart_payment_methods`
-- `active_region` tinyint(1) NOT NULL DEFAULT '0', - `#__phocacart_payment_methods`
-- `active_shipping` tinyint(1) NOT NULL DEFAULT '0', - `#__phocacart_payment_methods`
-- `access` int(11) unsigned NOT NULL DEFAULT '0',  - `#__phocacart_payment_methods`

-- `access` int(11) unsigned NOT NULL DEFAULT '0',  - `#__phocacart_payment_methods`
-- `access` int(11) unsigned NOT NULL DEFAULT '0',  - `#__phocacart_form_fields`

-- CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_countries` (
--  `payment_id` int(11) NOT NULL DEFAULT '0',
--  `country_id` int(11) NOT NULL DEFAULT '0',
--  KEY `idx_payment` (`payment_id`, `country_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_regions` (
--  `payment_id` int(11) NOT NULL DEFAULT '0',
--  `region_id` int(11) NOT NULL DEFAULT '0',
--  KEY `idx_payment` (`payment_id`, `region_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_shipping` (
-- `payment_id` int(11) NOT NULL DEFAULT '0',
-- `shipping_id` int(11) NOT NULL DEFAULT '0',
-- KEY `idx_payment` (`payment_id`, `shipping_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- `free_payment` tinyint(1) NOT NULL DEFAULT '0' - `#__phocacart_coupons`

-- --------------
-- BETA1 -> BETA2
-- --------------

-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_featured` (
--  `product_id` int(11) NOT NULL DEFAULT '0',
--  `ordering` int(11) NOT NULL DEFAULT '0',
--  PRIMARY KEY (`product_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- `sales` int(11) NOT NULL DEFAULT '0',  - `#__phocacart_products`

-- `alias_value` varchar(255) NOT NULL DEFAULT '',  - `#__phocacart_specifications`

-- `serial_number` varchar(255) NOT NULL DEFAULT '', - `#__phocacart_products`
-- `registration_key` varchar(255) NOT NULL DEFAULT '', - `#__phocacart_products`
-- `external_id` varchar(255) NOT NULL DEFAULT '', - `#__phocacart_products`
-- `external_key` varchar(255) NOT NULL DEFAULT '', - `#__phocacart_products`

-- -----------
-- BETA2 -> RC
-- -----------

-- ALTER TABLE  `#__phocacart_order_downloads` 	ADD `ordering` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 			ADD `video` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_categories` 			ADD `title_feed` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_stock_statuses` 		ADD `title_feed` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_products` 			ADD `unit_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 			ADD `unit_unit` varchar(64) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_attribute_values` 	ADD `image` varchar(255) NOT NULL DEFAULT '';

-- CREATE TABLE IF NOT EXISTS `#__phocacart_feeds` (
-- `id` int(11) NOT NULL AUTO_INCREMENT,
-- `title` varchar(255) NOT NULL DEFAULT '',
-- `alias` varchar(255) NOT NULL DEFAULT '',
-- `description` text,
-- `header` text,
-- `footer` text,
-- `root` varchar(64) NOT NULL DEFAULT '',
-- `item` varchar(64) NOT NULL DEFAULT '',
-- `item_params` text,
-- `feed_params` text,
-- `params` text,
-- `published` tinyint(1) NOT NULL DEFAULT '0',
-- `checked_out` int unsigned,
-- `checked_out_time` datetime,
-- `ordering` int(11) NOT NULL DEFAULT '0',
-- `date` datetime NOT NULL,
-- `type` tinyint(1) NOT NULL DEFAULT '0',
-- `language` char(7) NOT NULL DEFAULT '',
-- PRIMARY KEY (`id`),
-- KEY (`type`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- ---------
-- RC -> RC2
-- ---------

-- New table added
-- ---------------
-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_categories` (
--   `product_id` int(11) NOT NULL DEFAULT '0',
--   `category_id` int(11) NOT NULL DEFAULT '0',
--   `ordering` int(11) NOT NULL DEFAULT '0',
--   KEY `idx_category` (`product_id`,`category_id`),
--   KEY `ordering` ( `ordering` )
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Run this sql query to convert categories to multiple categories
-- ---------------------------------------------------------------
-- INSERT INTO `#__phocacart_product_categories` (product_id, category_id, ordering)
-- SELECT id, catid, ordering FROM `#__phocacart_products`;

-- Altered columns
-- ---------------
-- ALTER TABLE  `#__phocacart_order_products`	ADD `category_id` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 		ADD `external_link` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_products` 		ADD `external_text` varchar(255) NOT NULL DEFAULT '';

-- ALTER TABLE  `#__phocacart_products` 		CHANGE `price` `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 		CHANGE `price_original` `price_original` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 		CHANGE `length` `length` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 		CHANGE `width` `width` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 		CHANGE `height` `height` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 		CHANGE `weight` `weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 		CHANGE `volume` `volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 		CHANGE `unit_amount` `unit_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_attribute_values` CHANGE `amount` `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_attribute_values` CHANGE `weight` `weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_attribute_values` CHANGE `volume` `volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_shipping_methods` CHANGE `cost` `cost` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_shipping_methods` CHANGE `lowest_weight` `lowest_weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_shipping_methods` CHANGE `highest_weight` `highest_weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_shipping_methods` CHANGE `lowest_volume` `lowest_volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_shipping_methods` CHANGE `highest_volume` `highest_volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_shipping_methods` CHANGE `lowest_amount` `lowest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_shipping_methods` CHANGE `highest_amount` `highest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_taxes` 			CHANGE `tax_rate` `tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_currencies` 		CHANGE `exchange_rate` `exchange_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_coupons` 			CHANGE `discount` `discount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_coupons` 			CHANGE `total_amount` `total_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_payment_methods` 	CHANGE `cost` `cost` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_payment_methods` 	CHANGE `lowest_amount` `lowest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_payment_methods` 	CHANGE `highest_amount` `highest_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_orders` 			CHANGE `currency_exchange_rate` `currency_exchange_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `netto`  `netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `tax`  `tax` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `brutto`  `brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `dnetto`  `dnetto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `dtax`  `dtax` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `dbrutto`  `dbrutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `damount`  `damount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `weight`  `weight` DECIMAL( 10, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	CHANGE  `volume`  `volume` DECIMAL( 10, 4 ) NOT NULL DEFAULT  '0';

-- ALTER TABLE  `#__phocacart_order_coupons` 	CHANGE  `amount`  `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_coupons` 	CHANGE  `netto`  `netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_coupons` 	CHANGE  `brutto`  `brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';

-- ALTER TABLE  `#__phocacart_order_total` 		CHANGE  `amount`  `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';


-- ---------
-- RC2 -> RC3
-- ---------

-- ALTER TABLE  `#__phocacart_countries` 		ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_regions` 			ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_currencies` 		ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_users` 			ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_order_statuses` 	ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_taxes` 			ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_feeds` 			ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_tags` 			ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_products` 		ADD `min_multiple_quantity` int(11) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_attribute_values` 		ADD `color` varchar(50) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_attribute_values` 		ADD `type` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_attribute_values` 		ADD `params` text;
-- ALTER TABLE  `#__phocacart_attribute_values` 		ADD `image_small` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_attribute_values` 		ADD `image_medium` varchar(255) NOT NULL DEFAULT '';


-- New table added
-- ---------------
-- CREATE TABLE IF NOT EXISTS `#__phocacart_wishlists` (
--	`id` int(11) NOT NULL AUTO_INCREMENT,
--	`product_id` int(11) NOT NULL DEFAULT '0',
--	`category_id` int(11) NOT NULL DEFAULT '0',
--	`user_id` int(11) NOT NULL DEFAULT '0',
--	`title` varchar(255) NOT NULL DEFAULT '',
--	`alias` varchar(255) NOT NULL DEFAULT '',
--	`ip` varchar(46) NOT NULL DEFAULT '',
--	`user_agent` varchar(255) NOT NULL DEFAULT '',
--	`quantity` int(11) NOT NULL DEFAULT '0',
--	`type` tinyint(1) NOT NULL DEFAULT '0',
--	`priority` tinyint(1) NOT NULL DEFAULT '0',
--	`published` tinyint(1) NOT NULL DEFAULT '0',
--	`checked_out` int unsigned,
--	`checked_out_time` datetime,
--	`ordering` int(11) NOT NULL DEFAULT '0',
--	`date` datetime NOT NULL,
--	`params` text,
--	PRIMARY KEY (`id`),
--  KEY `idx_product_user` (`product_id`, `user_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_questions` (
--  `id` int(11) unsigned NOT NULL auto_increment,
--  `product_id` int(11) NOT NULL default '0',
--  `category_id` int(11) NOT NULL default '0',
--  `user_id` int(11) NOT NULL default '0',
--  `question_id` int(11) NOT NULL default '0',
--  `name` varchar(100) NOT NULL default '',
--  `email` varchar(50) NOT NULL default '',
--  `phone` varchar(20) NOT NULL default '',
--  `phone_mobile` varchar(20) NOT NULL default '',
--  `ip` varchar(46) NOT NULL default '',
--  `title` varchar(200) NOT NULL default '',
--  `alias` varchar(255) NOT NULL default '',
--  `message` text,
--  `date` datetime NOT NULL,
--  `published` tinyint(1) NOT NULL default '0',
--  `checked_out` int unsigned,
--  `checked_out_time` datetime,
--	`ordering` int(11) NOT NULL DEFAULT '0',
--  `params` text,
--  `language` char(7) NOT NULL default '',
--  PRIMARY KEY  (`id`),
--  KEY `published` (`published`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- ---------
-- RC3 -> RC4
-- ---------

-- ALTER TABLE  `#__phocacart_wishlists` 			ADD `wishlist` text;
-- ALTER TABLE  `#__phocacart_attribute_values` 	ADD `default_value` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 			ADD `public_download_file` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_products` 			ADD `public_download_text` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_attribute_values` 	ADD `sku` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_attribute_values` 	ADD `ean` varchar(15) NOT NULL DEFAULT '';



-- CREATE TABLE IF NOT EXISTS `#__phocacart_import` (
--   `id` int(11) unsigned NOT NULL auto_increment,
--   `user_id` int(11) NOT NULL default '0',
--   `product_id` int(11) NOT NULL default '0',
--   `row_id` int(11) NOT NULL default '0',
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `item` text,
--   `type` int(3) NOT NULL default '0',
--   `file_type` int(3) NOT NULL default '0',
--   PRIMARY KEY  (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_export` (
--   `id` int(11) unsigned NOT NULL auto_increment,
--   `user_id` int(11) NOT NULL default '0',
--   `product_id` int(11) NOT NULL default '0',
--   `row_id` int(11) NOT NULL default '0',
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `item` text,
--   `type` int(3) NOT NULL default '0',
--   `file_type` int(3) NOT NULL default '0',
--   PRIMARY KEY  (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_hits` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--	 `catid` int(11) NOT NULL default '0',
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `user_id` int(11) NOT NULL default '0',
--   `product_id` int(11) NOT NULL default '0',
--   `item` varchar(255) NOT NULL DEFAULT '',
--   `ip` varchar(46) NOT NULL default '',
--   `type` tinyint(3) NOT NULL default '0',
--	 `hits` int(11) NOT NULL DEFAULT '0',
--   `published` tinyint(1) NOT NULL DEFAULT '0',
--   `checked_out` int unsigned,
--   `checked_out_time` datetime,
--   `ordering` int(11) NOT NULL DEFAULT '0',
--   `date` datetime NOT NULL,
--   `params` text,
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


-- ---------
-- RC4 -> RC5
-- ---------

-- CREATE TABLE IF NOT EXISTS `#__phocacart_tax_countries` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `tax_id` int(11) NOT NULL DEFAULT '0',
--   `country_id` int(11) NOT NULL DEFAULT '0',
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
--   PRIMARY KEY (`id`),
--   KEY `tax_id` (`tax_id`),
--   KEY `country_id` (`country_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_tax_regions` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `tax_id` int(11) NOT NULL DEFAULT '0',
--   `region_id` int(11) NOT NULL DEFAULT '0',
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0',
--   PRIMARY KEY (`id`),
--   KEY `tax_id` (`tax_id`),
--   KEY `region_id` (`region_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- ---------
-- RC5 -> RC6
-- ---------

-- CREATE TABLE IF NOT EXISTS `#__phocacart_payment_method_zones` (
--   `payment_id` int(11) NOT NULL DEFAULT '0',
--   `zone_id` int(11) NOT NULL DEFAULT '0',
--   KEY `idx_payment` (`payment_id`, `zone_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_shipping_method_zones` (
--   `shipping_id` int(11) NOT NULL DEFAULT '0',
--   `zone_id` int(11) NOT NULL DEFAULT '0',
--   KEY `idx_shipping` (`shipping_id`, `zone_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_zones` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `type` int(11) NOT NULL DEFAULT '0',
--   `code2` varchar(20) NOT NULL DEFAULT '',
--   `code3` varchar(20) NOT NULL DEFAULT '',
--   `image` varchar(255) NOT NULL DEFAULT '',
--   `published` tinyint(1) NOT NULL DEFAULT '0',
--   `checked_out` int unsigned,
--   `checked_out_time` datetime,
--   `ordering` int(11) NOT NULL DEFAULT '0',
--   `params` text,
--   `language` char(7) NOT NULL DEFAULT '',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_zone_countries` (
--   `zone_id` int(11) NOT NULL DEFAULT '0',
--   `country_id` int(11) NOT NULL DEFAULT '0',
--   KEY `idx_zonecountry` (`zone_id`, `country_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_zone_regions` (
--   `zone_id` int(11) NOT NULL DEFAULT '0',
--   `region_id` int(11) NOT NULL DEFAULT '0',
--   KEY `idx_zoneregion` (`zone_id`, `region_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_discounts` (
--   `id` int(11) NOT NULL auto_increment,
--   `product_id` int(11) NOT NULL DEFAULT '0',
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `code` varchar(255) NOT NULL DEFAULT '',
--   `discount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--   `total_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--   `calculation_type` tinyint(1) NOT NULL DEFAULT '0',
--   `valid_from` datetime NOT NULL,
--   `valid_to` datetime NOT NULL,
--   `quantity` int(11) NOT NULL DEFAULT '0',
--   `quantity_from` int(11) NOT NULL DEFAULT '0',
--   `quantity_to` int(11) NOT NULL DEFAULT '0',
--   `available_quantity` int(11) NOT NULL DEFAULT '0',
--   `available_quantity_user` int(11) NOT NULL DEFAULT '0',
--   `description` text,
--   `access` int(11) unsigned NOT NULL DEFAULT '0',
--   `published` tinyint(1) NOT NULL DEFAULT '0',
--   `checked_out` int unsigned,
--   `checked_out_time` datetime,
--   `ordering` int(11) NOT NULL DEFAULT '0',
--   `params` text,
--   `language` char(7) NOT NULL DEFAULT '',
--   PRIMARY KEY  (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_discounts` (
--   `id` int(11) NOT NULL auto_increment,
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `code` varchar(255) NOT NULL DEFAULT '',
--   `discount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--   `total_amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--   `calculation_type` tinyint(1) NOT NULL DEFAULT '0',
--   `free_shipping` tinyint(1) NOT NULL DEFAULT '0',
--   `free_payment` tinyint(1) NOT NULL DEFAULT '0',
--   `valid_from` datetime NOT NULL,
--   `valid_to` datetime NOT NULL,
--   `quantity` int(11) NOT NULL DEFAULT '0',
--	 `quantity_from` int(11) NOT NULL DEFAULT '0',
--   `quantity_to` int(11) NOT NULL DEFAULT '0',
--   `available_quantity` int(11) NOT NULL DEFAULT '0',
--   `available_quantity_user` int(11) NOT NULL DEFAULT '0',
--   `description` text,
--   `access` int(11) unsigned NOT NULL DEFAULT '0',
--   `published` tinyint(1) NOT NULL DEFAULT '0',
--   `checked_out` int unsigned,
--   `checked_out_time` datetime,
--   `ordering` int(11) NOT NULL DEFAULT '0',
--   `params` text,
--   `language` char(7) NOT NULL DEFAULT '',
--   PRIMARY KEY  (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_discount_products` (
--   `discount_id` int(11) NOT NULL DEFAULT '0',
--   `product_id` int(11) NOT NULL DEFAULT '0',
--   KEY `idx_discountproduct` (`discount_id`,`product_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_discount_categories` (
--   `discount_id` int(11) NOT NULL DEFAULT '0',
--   `category_id` int(11) NOT NULL DEFAULT '0',
--   KEY `idx_discountcategory` (`discount_id`,`category_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_item_access` (
--   `item_id` int(11) NOT NULL DEFAULT '0',
--   `access_id` int(11) NOT NULL DEFAULT '0',
--   `type` tinyint(3) NOT NULL DEFAULT '0',
--   KEY `idx_itemaccess` (`item_id`, `access_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_order_discounts` (
--   `id` int(11) NOT NULL AUTO_INCREMENT,
--   `order_id` int(11) NOT NULL DEFAULT '0',
--   `discount_id` int(11) NOT NULL DEFAULT '0',
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `code` varchar(255) NOT NULL DEFAULT '',
--   `type` tinyint(3) NOT NULL default '0',
--   `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
--   `netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
--   `brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
--   PRIMARY KEY (`id`),
--   KEY `order_id` (`order_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_order_product_discounts` (
-- `id` int(11) NOT NULL AUTO_INCREMENT,
--  `order_id` int(11) NOT NULL DEFAULT '0',
--  `product_id` int(11) NOT NULL DEFAULT '0',
--  `order_product_id` int(11) NOT NULL DEFAULT '0',
--  `discount_id` int(11) NOT NULL DEFAULT '0',
--  `category_id` int(11) NOT NULL DEFAULT '0',
--  `product_id_key` text,
--  `type` tinyint(3) NOT NULL default '0',
--  `title` varchar(255) NOT NULL DEFAULT '',
--  `alias` varchar(255) NOT NULL DEFAULT '',
--  `code` varchar(255) NOT NULL DEFAULT '',
--  `amount` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
--  `netto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
--  `brutto` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
--  `tax` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
--  `quantity` int(11) NOT NULL DEFAULT '0',
--  `final` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0',
--  `published` tinyint(1) NOT NULL DEFAULT '0',
--  PRIMARY KEY (`id`),
--  KEY `product_id` (`product_id`),
--  KEY `order_id` (`order_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--


-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_stock` (
--  `id` int(11) NOT NULL auto_increment,
--  `product_id` int(11) NOT NULL DEFAULT '0',
--  `product_key` text,
--  `attributes` text,
--  `stock` int(11) NOT NULL DEFAULT '0',
--  PRIMARY KEY  (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- ALTER TABLE  `#__phocacart_shipping_methods` 	ADD `method` varchar(100) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_shipping_methods` 	ADD `active_zone` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_payment_methods` 		ADD `active_zone` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_coupons` 				ADD `access` int(11) unsigned NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_coupons` 				ADD `quantity` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_coupons` 				ADD `quantity_from` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_coupons` 				ADD `quantity_to` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 			ADD `stock_calculation` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_products` 			ADD `min_quantity_calculation` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_order_products` 		ADD `stock_calculation` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_payment_methods`		ADD `calculation_type` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_shipping_methods`		ADD `calculation_type` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_orders`				ADD `discount_id` int(11) NOT NULL DEFAULT '0';




-- ---------
-- RC6 -> RC7
-- ---------

-- CREATE TABLE IF NOT EXISTS `#__phocacart_groups` (
--   `id` int(11) NOT NULL auto_increment,
--   `title` varchar(255) NOT NULL DEFAULT '',
--   `alias` varchar(255) NOT NULL DEFAULT '',
--   `image` varchar(255) NOT NULL DEFAULT '',
--   `description` text,
--   `display_price` tinyint(1) unsigned NOT NULL DEFAULT '0',
--   `display_price_method` tinyint(3) unsigned NOT NULL DEFAULT '0',
--   `minimum_sum` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--   `product_id` int(11) NOT NULL DEFAULT '0',
--   `valid_from` datetime NOT NULL,
--   `valid_to` datetime NOT NULL,
--   `type` tinyint(3) NOT NULL default '0',
--   `access` int(11) unsigned NOT NULL DEFAULT '0',
--   `published` tinyint(1) NOT NULL DEFAULT '0',
--   `checked_out` int unsigned,
--   `checked_out_time` datetime,
--   `ordering` int(11) NOT NULL DEFAULT '0',
--   `params` text,
--   `language` char(7) NOT NULL DEFAULT '',
--   PRIMARY KEY  (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- INSERT INTO `#__phocacart_groups` (`id`, `title`, `published`, `display_price`, `ordering`, `type`) VALUES (1, 'COM_PHOCACART_DEFAULT', '1', '1', '1', '1');



-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_groups` (
--   `id` int(11) NOT NULL auto_increment,
--   `product_id` int(11) NOT NULL DEFAULT '0',
--   `group_id` int(11) NOT NULL DEFAULT '0',
--   `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--   PRIMARY KEY  (`id`),
--   KEY `idx_product` (`product_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_price_groups` (
--   `id` int(11) NOT NULL auto_increment,
--   `product_id` int(11) NOT NULL DEFAULT '0',
--   `group_id` int(11) NOT NULL DEFAULT '0',
--   `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--   PRIMARY KEY  (`id`),
--   KEY `idx_product` (`product_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
--
-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_point_groups` (
--   `id` int(11) NOT NULL auto_increment,
--   `product_id` int(11) NOT NULL DEFAULT '0',
--   `group_id` int(11) NOT NULL DEFAULT '0',
--   `points_received` int(11) NOT NULL DEFAULT '0',
--   PRIMARY KEY  (`id`),
--   KEY `idx_product` (`product_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_item_groups` (
--    `item_id` int(11) NOT NULL DEFAULT '0',
--    `group_id` int(11) NOT NULL DEFAULT '0',
--    `product_id` int(11) NOT NULL DEFAULT '0',
--    `type` tinyint(3) NOT NULL DEFAULT '0',
--    KEY `idx_itemgroup` (`item_id`, `group_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `#__phocacart_reward_points` (
-- 	`id` int(11) NOT NULL AUTO_INCREMENT,
-- 	`user_id` int(11) NOT NULL DEFAULT '0',
-- 	`order_id` int(11) NOT NULL DEFAULT '0',
-- 	`title` varchar(255) NOT NULL DEFAULT '',
-- 	`alias` varchar(255) NOT NULL DEFAULT '',
-- 	`points` int(11) NOT NULL DEFAULT '0',
-- 	`type` tinyint(3) NOT NULL default '0',
-- 	`description` text,
-- 	`published` tinyint(1) NOT NULL DEFAULT '0',
-- 	`checked_out` int unsigned,
-- 	`checked_out_time` datetime,
-- 	`ordering` int(11) NOT NULL DEFAULT '0',
-- 	`date` datetime NOT NULL,
-- 	`params` text,
-- 	PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- ALTER TABLE `#__phocacart_orders`				ADD `group_id` int(11) NOT NULL DEFAULT '0';

-- ALTER TABLE `#__phocacart_products`				ADD `points_received` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_products`				ADD `points_needed` int(11) NOT NULL DEFAULT '0';

-- ALTER TABLE `#__phocacart_order_products`		ADD `points_received` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_order_products`		ADD `points_needed` int(11) NOT NULL DEFAULT '0';

-- ALTER TABLE `#__phocacart_cart`					ADD `reward` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_form_fields`			ADD `type_default` tinyint(3) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_order_users`			ADD `user_groups` text;


-- ALTER TABLE `#__phocacart_order_products`		ADD `default_price` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_order_products`		ADD `default_tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_order_products`		ADD `default_calculation_type` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_order_products`		ADD `default_points_received` int(11) NOT NULL DEFAULT '0';


-- ALTER TABLE `#__phocacart_order_statuses`		ADD `change_user_group` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_order_statuses`		ADD `change_points_needed` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_order_statuses`		ADD `change_points_received` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_users`					ADD `vat_valid` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_products`				ADD `active` tinyint(1) NOT NULL DEFAULT '0';



-- ---------
-- RC7 -> RC8
-- ---------


-- CREATE TABLE IF NOT EXISTS `#__phocacart_product_price_history` (
--   `id` int(11) NOT NULL auto_increment,
--   `product_id` int(11) NOT NULL DEFAULT '0',
--   `date` datetime NOT NULL,
--   `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0',
--   PRIMARY KEY  (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- ALTER TABLE `#__phocacart_shipping_methods`		ADD `default` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_payment_methods`		ADD `default` tinyint(1) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_shipping_methods`		ADD `tracking_title` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE `#__phocacart_shipping_methods`		ADD `tracking_description` text;
-- ALTER TABLE `#__phocacart_shipping_methods`		ADD `tracking_link` varchar(255) NOT NULL DEFAULT '';

-- ALTER TABLE `#__phocacart_orders`				ADD	`tracking_id` int(11) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_orders`				ADD	`tracking_title` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE `#__phocacart_orders`				ADD	`tracking_description_custom` text;
-- ALTER TABLE `#__phocacart_orders`				ADD	`tracking_link_custom` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE `#__phocacart_orders`				ADD	`tracking_number` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE `#__phocacart_orders`				ADD	`tracking_date_shipped` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

-- ALTER TABLE `#__phocacart_shipping_methods`		ADD `maximal_width` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_shipping_methods`		ADD `maximal_height` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_shipping_methods`		ADD `maximal_length` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
-- ALTER TABLE `#__phocacart_shipping_methods`		ADD `active_size` tinyint(1) NOT NULL DEFAULT '0';


-- ---------
-- RC8 -> RC9
-- ---------

-- ALTER TABLE  `#__phocacart_currencies` 		CHANGE `exchange_rate` `exchange_rate` DECIMAL( 15, 8 ) NOT NULL DEFAULT '0';
-- ALTER TABLE  `#__phocacart_orders` 			CHANGE `currency_exchange_rate` `currency_exchange_rate` DECIMAL( 15, 8 ) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_order_total` 		ADD `amount_currency` DECIMAL( 15, 4 ) NOT NULL DEFAULT  '0';
-- ALTER TABLE  `#__phocacart_order_products` 	ADD `default_tax_id` int(11) NOT NULL DEFAULT '0';

-- ALTER TABLE  `#__phocacart_categories` 		ADD `metatitle` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_products` 		ADD `metatitle` varchar(255) NOT NULL DEFAULT '';

-- ALTER TABLE  `#__phocacart_order_total` 		ADD `item_id` int(11) NOT NULL DEFAULT '0';


-- ---------
-- RC9 -> RC9.1
-- ---------
-- ALTER TABLE  `#__phocacart_product_stock` 		ADD `image` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_product_stock` 		ADD `image_small` varchar(255) NOT NULL DEFAULT '';
-- ALTER TABLE  `#__phocacart_product_stock` 		ADD `image_medium` varchar(255) NOT NULL DEFAULT '';

-- ALTER TABLE  `#__phocacart_products` 		CHANGE `isbn` `isbn` varchar(20) NOT NULL DEFAULT '';

-- ---------
-- RC9.1 -> STABLE
-- ---------

-- ALTER TABLE  `#__phocacart_manufacturers` 		ADD `link` varchar(255) NOT NULL DEFAULT '';

-- ALTER TABLE  `#__phocacart_coupons` 		ADD `category_filter` tinyint(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__phocacart_coupons` 		ADD `product_filter` tinyint(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__phocacart_discounts` 	ADD `category_filter` tinyint(1) NOT NULL DEFAULT '1';
-- ALTER TABLE  `#__phocacart_discounts` 	ADD `product_filter` tinyint(1) NOT NULL DEFAULT '1';


-- ---------
-- 3.0.0 - 3.0.1 and newer versions
-- ---------

-- SEE: administrator/components/com_phocacart/update/ - SQL should be updated automatically by Joomla! extension manager rules

