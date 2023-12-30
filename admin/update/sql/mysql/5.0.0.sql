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

ALTER TABLE `#__phocacart_form_fields` ADD COLUMN `autocomplete` varchar(50) AFTER `validate`;
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'given-name' WHERE `title` = 'name_first';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'additional-name' WHERE `title` = 'name_middle';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'family-name' WHERE `title` = 'name_last';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'honorific-prefix' WHERE `title` = 'name_degree';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'organization' WHERE `title` = 'company';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'street-address' WHERE `title` = 'address_1';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'postal-code' WHERE `title` = 'zip';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'address-level2' WHERE `title` = 'city';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'country-name' WHERE `title` = 'country';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'address-level1' WHERE `title` = 'region';

UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'email' WHERE `title` = 'email';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'email' WHERE `title` = 'email_contact';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'tel' WHERE `title` = 'phone_1';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'tel' WHERE `title` = 'phone_2';
UPDATE `#__phocacart_form_fields` SET `autocomplete` = 'tel' WHERE `title` = 'phone_mobile';

ALTER TABLE `#__phocacart_users` ADD UNIQUE `uq_phocacart_users` (`type`, `user_id`);
ALTER TABLE `#__phocacart_cart_multiple` ADD UNIQUE `idx_user_id` (`user_id`);
ALTER TABLE `#__phocacart_cart_multiple` ADD UNIQUE `idx_vendor_id` (`vendor_id`);
ALTER TABLE `#__phocacart_cart_multiple` ADD UNIQUE `idx_section_id` (`section_id`);
ALTER TABLE `#__phocacart_cart_multiple` ADD UNIQUE `idx_unit_id` (`unit_id`);

ALTER TABLE `#__phocacart_orders` ADD COLUMN `internal_comment` text;

ALTER TABLE `#__phocacart_order_users` ADD FULLTEXT KEY `idx_fulltext` (`name_first`,`name_middle`,`name_last`,`name_degree`,`company`,`vat_1`,`vat_2`,`address_1`,`address_2`,`city`,`zip`,`email`,`email_contact`,`phone_1`,`phone_2`,`phone_mobile`);

ALTER TABLE `#__phocacart_products` ADD `discount_percent` DECIMAL(15,2) AS (if(price_original <> 0, (price_original - price) / price_original * 100, 0)) VIRTUAL AFTER `price_original`;

-- ALTER TABLE `#__phocacart_attributes` ADD COLUMN `uuid` char(36) NOT NULL DEFAULT UUID() AFTER `id`;
-- ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `uuid` char(36) NOT NULL DEFAULT UUID() AFTER `id`;
