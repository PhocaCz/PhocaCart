-- 4.0.0 Alpha => 4.0.0 Beta
-- ALTER TABLE `#__phocacart_order_statuses` ADD COLUMN `code` char(5) NOT NULL DEFAULT '';

-- UPDATE `#__phocacart_order_statuses` SET `code` = 'P' WHERE `id` = 1;
-- UPDATE `#__phocacart_order_statuses` SET `code` = 'C' WHERE `id` = 2;
-- UPDATE `#__phocacart_order_statuses` SET `code` = 'CL' WHERE `id` = 3;
-- UPDATE `#__phocacart_order_statuses` SET `code` = 'S' WHERE `id` = 4;
-- UPDATE `#__phocacart_order_statuses` SET `code` = 'RF' WHERE `id` = 5;
-- UPDATE `#__phocacart_order_statuses` SET `code` = 'CE' WHERE `id` = 6;

--INSERT INTO `#__phocacart_order_statuses` (`id`, `title`, `code`, `published`, `ordering`, `stock_movements`, `type`, `download`, `change_user_group`, `change_points_needed`, `change_points_received`, `orders_view_display`) VALUES
--(7, 'COM_PHOCACART_STATUS_FAILED', 'E', '0', '7', '=', '1', '0', '1', '2', '2', '[1]'),
--(8, 'COM_PHOCACART_STATUS_DENIED', 'D', '0', '8', '=', '1', '0', '1', '2', '2', '[1]'),
--(9, 'COM_PHOCACART_STATUS_CANCELED_REVERSAL', 'CRV', '0', '9', '=', '1', '0', '1', '2', '2', '[1]'),
--(10, 'COM_PHOCACART_STATUS_REVERSED', 'RV', '0', '10', '=', '1', '0', '1', '2', '2', '[1]');


-- 4.0.0 Beta ==> RC
-- ALTER TABLE `#__phocacart_cart_multiple` ADD COLUMN `params_shipping` text;
-- ALTER TABLE `#__phocacart_cart_multiple` ADD COLUMN `params_payment` text;
-- ALTER TABLE `#__phocacart_orders` ADD COLUMN `unit_size` varchar(50) NOT NULL DEFAULT '';
