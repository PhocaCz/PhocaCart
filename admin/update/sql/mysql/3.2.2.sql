-- 3.2.2
ALTER TABLE `#__phocacart_shipping_methods`		ADD `minimal_width` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_shipping_methods`		ADD `minimal_height` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `#__phocacart_shipping_methods`		ADD `minimal_length` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0';
