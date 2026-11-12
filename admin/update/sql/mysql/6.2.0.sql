-- ---------------------------------------------------------------
-- Phoca Cart 6.2.0 - Booking Feature
-- ---------------------------------------------------------------
--
-- Product columns: booking configuration stored inline in products table,
-- following the same pattern as subscription_* columns.
--
-- booking_mode:
--   1 = Time Slot Mode  (services/appointments with predefined hours)
--   2 = Date Range Mode (rentals/accommodations: check-in / check-out dates)
--

ALTER TABLE `#__phocacart_products`
  ADD COLUMN `booking_mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  ADD COLUMN `booking_capacity` int(11) NOT NULL DEFAULT 1,
  ADD COLUMN `booking_min_advance_hours` int(11) NOT NULL DEFAULT 0,
  ADD COLUMN `booking_max_advance_days` int(11) DEFAULT NULL,
  ADD COLUMN `booking_checkin_time` time DEFAULT NULL,
  ADD COLUMN `booking_checkout_time` time DEFAULT NULL,
  ADD COLUMN `booking_usergroup_confirm` text DEFAULT NULL,
  ADD COLUMN `booking_button_text` varchar(255) DEFAULT NULL;

ALTER TABLE `#__phocacart_products_i18n`
  ADD COLUMN `booking_button_text` varchar(255) DEFAULT NULL;

--
-- Table: #__phocacart_booking_slots
--
-- Used for Time Slot Mode only.
-- Admins create predefined slots (date + start/end time + capacity) per product.
-- Availability is determined by comparing capacity against booked count.
-- NOT used in Date Range Mode (availability checked directly against bookings table).
--
CREATE TABLE IF NOT EXISTS `#__phocacart_booking_slots` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time_start` time NOT NULL,
  `slot_time_end` time NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `booked` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `params` text DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_product_date` (`product_id`, `slot_date`),
  KEY `idx_slot_date` (`slot_date`),
  KEY `idx_published` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: #__phocacart_bookings
--
-- Runtime booking records per user/product.
-- Mirrors #__phocacart_subscriptions in structure and pattern.
--
-- Status values:
--  1: Confirmed
--  2: Pending
--  3: Cancelled
--  4: Completed
--  5: No-Show
--  6: Pending Payment
--  7: Refunded
--
-- For Time Slot Mode: slot_id references #__phocacart_booking_slots.
--   booking_date_start = booking_date_end = the slot date.
-- For Date Range Mode: slot_id is NULL.
--   booking_date_start = check-in date, booking_date_end = check-out date.
--
CREATE TABLE IF NOT EXISTS `#__phocacart_bookings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `slot_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `booking_mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `status` int(11) NOT NULL DEFAULT 2,
  `booking_date_start` date NOT NULL,
  `booking_date_end` date NOT NULL,
  `booking_time_start` time DEFAULT NULL,
  `booking_time_end` time DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `cancelled_date` datetime DEFAULT NULL,
  `params` text DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_slot_id` (`slot_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_booking_date_start` (`booking_date_start`),
  KEY `idx_booking_date_end` (`booking_date_end`),
  KEY `idx_status_date` (`status`, `booking_date_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: #__phocacart_order_bookings
--
-- Frozen snapshot of booking data captured at order time.
-- Mirrors #__phocacart_order_subscriptions in structure.
--
CREATE TABLE IF NOT EXISTS `#__phocacart_order_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `order_product_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `booking_mode` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `slot_id` int(11) unsigned DEFAULT NULL,
  `booking_date_start` date DEFAULT NULL,
  `booking_date_end` date DEFAULT NULL,
  `booking_time_start` time DEFAULT NULL,
  `booking_time_end` time DEFAULT NULL,
  `booking_capacity` int(11) NOT NULL DEFAULT 1,
  `booking_quantity` int(11) NOT NULL DEFAULT 1,
  `booking_usergroup_confirm` text DEFAULT NULL,
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: #__phocacart_booking_history
--
-- Full audit log of booking status changes and events.
-- Mirrors #__phocacart_subscription_history in structure.
--
CREATE TABLE IF NOT EXISTS `#__phocacart_booking_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) unsigned NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `status_from` int(11) DEFAULT NULL,
  `status_to` int(11) NOT NULL,
  `event_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `triggered_by` varchar(100) NOT NULL,
  `notify_user` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 6.2.0 Booking email templates
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.booking.created', 'com_phocacart', '', 'COM_PHOCACART_MAIL_BOOKING_CREATED_SUBJECT', 'COM_PHOCACART_MAIL_BOOKING_CREATED_BODY', 'COM_PHOCACART_MAIL_BOOKING_CREATED_HTMLBODY', '', '{"tags":["user_name","user_email","product_name","product_title","product_sku","product_link","booking_id","booking_status","booking_date_start","booking_date_end","booking_time_start","booking_time_end","booking_nights","order_number","site_name","site_link","site_url","account_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.booking.confirmed', 'com_phocacart', '', 'COM_PHOCACART_MAIL_BOOKING_CONFIRMED_SUBJECT', 'COM_PHOCACART_MAIL_BOOKING_CONFIRMED_BODY', 'COM_PHOCACART_MAIL_BOOKING_CONFIRMED_HTMLBODY', '', '{"tags":["user_name","user_email","product_name","product_title","product_sku","product_link","booking_id","booking_status","booking_date_start","booking_date_end","booking_time_start","booking_time_end","booking_nights","order_number","site_name","site_link","site_url","account_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.booking.cancelled', 'com_phocacart', '', 'COM_PHOCACART_MAIL_BOOKING_CANCELLED_SUBJECT', 'COM_PHOCACART_MAIL_BOOKING_CANCELLED_BODY', 'COM_PHOCACART_MAIL_BOOKING_CANCELLED_HTMLBODY', '', '{"tags":["user_name","user_email","product_name","product_title","product_sku","product_link","booking_id","booking_status","booking_date_start","booking_date_end","booking_time_start","booking_time_end","booking_nights","order_number","site_name","site_link","site_url","account_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.booking.completed', 'com_phocacart', '', 'COM_PHOCACART_MAIL_BOOKING_COMPLETED_SUBJECT', 'COM_PHOCACART_MAIL_BOOKING_COMPLETED_BODY', 'COM_PHOCACART_MAIL_BOOKING_COMPLETED_HTMLBODY', '', '{"tags":["user_name","user_email","product_name","product_title","product_sku","product_link","booking_id","booking_status","booking_date_start","booking_date_end","booking_time_start","booking_time_end","booking_nights","order_number","site_name","site_link","site_url","account_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.booking.reminder', 'com_phocacart', '', 'COM_PHOCACART_MAIL_BOOKING_REMINDER_SUBJECT', 'COM_PHOCACART_MAIL_BOOKING_REMINDER_BODY', 'COM_PHOCACART_MAIL_BOOKING_REMINDER_HTMLBODY', '', '{"tags":["user_name","user_email","product_name","product_title","product_sku","product_link","booking_id","booking_status","booking_date_start","booking_date_end","booking_time_start","booking_time_end","booking_nights","order_number","site_name","site_link","site_url","account_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.booking.status_changed', 'com_phocacart', '', 'COM_PHOCACART_MAIL_BOOKING_STATUS_CHANGED_SUBJECT', 'COM_PHOCACART_MAIL_BOOKING_STATUS_CHANGED_BODY', 'COM_PHOCACART_MAIL_BOOKING_STATUS_CHANGED_HTMLBODY', '', '{"tags":["user_name","user_email","product_name","product_title","product_sku","product_link","booking_id","booking_status","booking_date_start","booking_date_end","booking_time_start","booking_time_end","booking_nights","order_number","site_name","site_link","site_url","account_url","html.document","text.document"]}');
