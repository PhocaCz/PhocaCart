CREATE TABLE IF NOT EXISTS `#__phocacart_product_bundles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `main_product_id` int(11) NOT NULL DEFAULT 0,
    `child_product_id` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `main_product_id` (`main_product_id`),
    KEY `child_product_id` (`child_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__phocacart_products` ADD COLUMN `redirect_product_id` int(11);
ALTER TABLE `#__phocacart_products` ADD COLUMN `redirect_url` varchar(255);

ALTER TABLE `#__phocacart_vendors` ADD COLUMN `type` int(11) NOT NULL DEFAULT 0 AFTER `alias`;
UPDATE `#__phocacart_vendors` v
    JOIN `#__users` u ON u.`id` = v.`user_id`
    SET `title` = u.`name`
    WHERE `title` = '';
-- ALTER TABLE `#__phocacart_attributes` ADD COLUMN `uuid` char(36) NOT NULL DEFAULT UUID() AFTER `id`;
-- ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `uuid` char(36) NOT NULL DEFAULT UUID() AFTER `id`;
