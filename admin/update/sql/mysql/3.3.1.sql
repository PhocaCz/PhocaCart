-- 3.3.1
ALTER TABLE `#__phocacart_products` ADD COLUMN `params_feed` text;
ALTER TABLE `#__phocacart_feeds` ADD COLUMN `feed_plugin` varchar(64) NOT NULL DEFAULT '';
