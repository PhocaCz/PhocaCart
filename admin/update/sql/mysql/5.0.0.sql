CREATE TABLE IF NOT EXISTS `#__phocacart_product_bundles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `main_product_id` int(11) NOT NULL DEFAULT 0,
    `child_product_id` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `main_product_id` (`main_product_id`),
    KEY `child_product_id` (`child_product_id`)
) DEFAULT CHARSET=utf8;

-- ALTER TABLE `#__phocacart_attributes` ADD COLUMN `uuid` char(36) NOT NULL DEFAULT UUID() AFTER `id`;
-- ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `uuid` char(36) NOT NULL DEFAULT UUID() AFTER `id`;
