ALTER TABLE `#__phocacart_feeds` ADD COLUMN `currency_id` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `#__phocacart_attribute_values` ADD COLUMN `published` int(1) NOT NULL DEFAULT '1' AFTER `alias`;
ALTER TABLE `#__phocacart_attributes` CHANGE `published` `published` int(1) NOT NULL DEFAULT '1';
UPDATE `#__phocacart_attributes` SET `published` = 1;
