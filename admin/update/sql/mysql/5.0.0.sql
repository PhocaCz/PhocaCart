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

ALTER TABLE `#__phocacart_orders` ADD COLUMN `internal_comment` text;

ALTER TABLE `#__phocacart_order_users` ADD FULLTEXT KEY `idx_fulltext` (`name_first`,`name_middle`,`name_last`,`name_degree`,`company`,`vat_1`,`vat_2`,`address_1`,`address_2`,`city`,`zip`,`email`,`email_contact`,`phone_1`,`phone_2`,`phone_mobile`);

ALTER TABLE `#__phocacart_products` ADD `discount_percent` DECIMAL(15,2) AS (if(price_original <> 0, (price_original - price) / price_original * 100, 0)) VIRTUAL AFTER `price_original`;


CREATE TABLE IF NOT EXISTS `#__phocacart_categories_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `title_long` varchar(255),
    `alias` varchar(255),
    `title_feed` varchar(255),
    `description` text,
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

ALTER TABLE `#__phocacart_parameter_values_related` DROP INDEX `i_parameter_id`, ADD UNIQUE `i_parameter_id` (`item_id`, `parameter_value_id`, `parameter_id`);

ALTER TABLE `#__phocacart_wishlists` ADD COLUMN `language` CHAR(7) NOT NULL DEFAULT '';

INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.watchdog', 'com_phocacart', '', 'COM_PHOCACART_EMAIL_WATCHDOG_SUBJECT', 'COM_PHOCACART_EMAIL_WATCHDOG_BODY', 'COM_PHOCACART_EMAIL_WATCHDOG_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_title","product_sku","product_url","site_name","site_url"]}');

ALTER TABLE `#__phocacart_categories` ADD COLUMN `description_bottom` TEXT AFTER `description`;
ALTER TABLE `#__phocacart_categories_i18n` ADD COLUMN `description_bottom` TEXT AFTER `description`;

CREATE TABLE IF NOT EXISTS `#__phocacart_content_types` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `context` varchar(255) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 0,
    `checked_out` int(11),
    `checked_out_time` datetime,
    `ordering` int(11) NOT NULL DEFAULT 0,
    `params` text,
    PRIMARY KEY (`id`),
    KEY `idx_context` (`context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__phocacart_content_types` (`id`, `title`, `context`, `published`, `ordering`, `params`)
    VALUES (1, 'COM_PHOCACART_CONTENT_TYPE_CATEGORY_DEFAULT', 'category', 1, 1, '{}');
INSERT INTO `#__phocacart_content_types` (`id`, `title`, `context`, `published`, `ordering`, `params`)
    VALUES (2, 'COM_PHOCACART_CONTENT_TYPE_RELATED_DEFAULT', 'product_related', 1, 1, '{}');

ALTER TABLE `#__phocacart_categories` ADD COLUMN `category_type` int(11) NOT NULL AFTER `owner_id`;
ALTER TABLE `#__phocacart_product_related` ADD COLUMN `related_type` int(11) NOT NULL AFTER `id`;

UPDATE `#__phocacart_categories` SET `category_type` = 1 WHERE `category_type` = 0;
UPDATE `#__phocacart_product_related` SET `related_type` = 2 WHERE `related_type` = 0;

ALTER TABLE `#__phocacart_product_related` ADD COLUMN `ordering` int(11) NOT NULL DEFAULT 0;

ALTER TABLE `#__phocacart_attributes` ADD COLUMN `attribute_template` int(11) AFTER `product_id`;
ALTER TABLE `#__phocacart_attributes` ADD COLUMN `is_filter` int(11) NOT NULL DEFAULT 1 AFTER `published`;

ALTER TABLE `#__phocacart_export` CHANGE `item` `item` MEDIUMTEXT;
ALTER TABLE `#__phocacart_import` CHANGE `item` `item` MEDIUMTEXT;

ALTER TABLE `#__phocacart_orders` ADD INDEX `idx_user_id` (`user_id`);
ALTER TABLE `#__phocacart_cart_multiple` ADD INDEX `idx_user_id` (`user_id`);
ALTER TABLE `#__phocacart_cart_multiple` ADD INDEX `idx_vendor_id` (`vendor_id`);
ALTER TABLE `#__phocacart_cart_multiple` ADD INDEX `idx_section_id` (`section_id`);
ALTER TABLE `#__phocacart_cart_multiple` ADD INDEX `idx_unit_id` (`unit_id`);

CREATE TABLE IF NOT EXISTS `#__phocacart_taxes_i18n` (
    `id` int(11) NOT NULL,
    `language` char(7) NOT NULL,
    `title` varchar(255),
    `alias` varchar(255),
    PRIMARY KEY  (`id`, `language`),
    KEY `idx_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


-- 5.0.0 Beta83
ALTER TABLE `#__phocacart_shipping_methods` ADD COLUMN `change_tax` tinyint(1) NOT NULL DEFAULT '0';
