-- 3.1.7
-- time of supply | date of taxable supply | tax point
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_time_of_supply` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `#__phocacart_orders` ADD COLUMN `credit_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `credit_number` varchar(64) NOT NULL DEFAULT '';

ALTER TABLE `#__phocacart_orders` ADD COLUMN `order_number_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `receipt_number_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `invoice_number_id` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_orders` ADD COLUMN `credit_number_id` int(11) NOT NULL DEFAULT '0';
