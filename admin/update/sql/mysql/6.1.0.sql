ALTER TABLE  `#__phocacart_products` ADD `max_quantity` int(11) NOT NULL DEFAULT '0';
ALTER TABLE  `#__phocacart_products` ADD `max_quantity_calculation` int(11) NOT NULL DEFAULT '0';

--
-- Table structure for table `#__phocacart_subscriptions`
--
-- Status values:
-- 1: Active
-- 2: Future
-- 3: Expired
-- 4: On Hold
-- 5: Pending
-- 6: Failed
-- 7: In Trial
-- 8: Card Expired
-- 9: Canceled
--
CREATE TABLE IF NOT EXISTS `#__phocacart_subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '5',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `next_billing_date` datetime DEFAULT NULL,
  `trial_end_date` datetime DEFAULT NULL,
  `signup_fee_paid` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `renewal_count` int(11) unsigned NOT NULL DEFAULT 0,
  `cancelation_reason` varchar(100) DEFAULT NULL,
  `canceled_date` datetime DEFAULT NULL,
  `params` text DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `checked_out` int(11) DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_product_active` (`user_id`,`product_id`,`status`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_end_date` (`end_date`),
  KEY `idx_next_billing` (`next_billing_date`),
  KEY `idx_status_end_date` (`status`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__phocacart_order_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `attribute_id` int(11) NOT NULL DEFAULT '0',
  `option_id` int(11) NOT NULL DEFAULT '0',
  `order_product_id` int(11) NOT NULL DEFAULT '0',
  `order_attribute_id` int(11) NOT NULL DEFAULT '0',
  `order_option_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `subscription_period` smallint(5) unsigned DEFAULT NULL,
  `subscription_unit` TINYINT(3) UNSIGNED DEFAULT NULL,
  `subscription_signup_fee` decimal(15,4) DEFAULT NULL,
  `subscription_renewal_discount` decimal(15,4) DEFAULT NULL,
  `subscription_renewal_discount_calculation_type` tinyint(1) NOT NULL DEFAULT '0',
  `subscription_order_signup_fee` decimal(15,4) DEFAULT NULL,
  `subscription_order_renewal_discount` decimal(15,4) DEFAULT NULL,
  `subscription_order_base_price` decimal(15,4) DEFAULT NULL,
  `subscription_order_total_price` decimal(15,4) DEFAULT NULL,
  `subscription_usergroup_add` text,
  `subscription_usergroup_remove` text,
  `subscription_trial_enabled` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `subscription_trial_period` smallint(5) unsigned DEFAULT NULL,
  `subscription_trial_unit` TINYINT(3) UNSIGNED DEFAULT NULL,
  `subscription_grace_period_days` smallint(5) unsigned DEFAULT 0,
  `subscription_max_renewals` int(11) DEFAULT NULL,
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__phocacart_subscription_history`
--
CREATE TABLE IF NOT EXISTS `#__phocacart_subscription_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) unsigned NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `status_from` int(11) DEFAULT NULL,
  `status_to` int(11) NOT NULL,
  `event_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `triggered_by` varchar(100) NOT NULL,
  `notify_user` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_subscription_id` (`subscription_id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__phocacart_subscription_acl`
--
CREATE TABLE IF NOT EXISTS `#__phocacart_subscription_acl` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` int(11) unsigned NOT NULL,
  `action` enum('add','remove') NOT NULL,
  `group_id` int(11) NOT NULL,
  `applied_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_subscription_id` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Update #__phocacart_products table
--
ALTER TABLE `#__phocacart_products`
ADD COLUMN `subscription_period` smallint(5) unsigned DEFAULT NULL,
ADD COLUMN `subscription_unit` TINYINT(3) UNSIGNED DEFAULT NULL,
ADD COLUMN `subscription_signup_fee` decimal(15,4) DEFAULT NULL,
ADD COLUMN `subscription_renewal_discount` decimal(15,4) DEFAULT NULL,
ADD COLUMN `subscription_renewal_discount_calculation_type` tinyint(1) NOT NULL DEFAULT '0',
ADD COLUMN `subscription_usergroup_add` text,
ADD COLUMN `subscription_usergroup_remove` text,
ADD COLUMN `subscription_trial_enabled` tinyint(1) unsigned NOT NULL DEFAULT 0,
ADD COLUMN `subscription_trial_period` smallint(5) unsigned DEFAULT NULL,
ADD COLUMN `subscription_trial_unit` TINYINT(3) UNSIGNED DEFAULT NULL,
ADD COLUMN `subscription_grace_period_days` smallint(5) unsigned DEFAULT 0,
ADD COLUMN `subscription_max_renewals` int(11) DEFAULT NULL;


-- 6.1.0 Subscription email templates
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.activated', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_ACTIVATED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","subscription_status","renewal_count","site_name","site_link","site_url","account_url","renewal_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.renewed', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_RENEWED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","subscription_status","renewal_count","site_name","site_link","site_url","account_url","renewal_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.expiring_soon', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_SOON_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_SOON_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRING_SOON_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","days_remaining","subscription_status","renewal_count","site_name","site_link","site_url","account_url","renewal_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.expired', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_EXPIRED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","subscription_status","renewal_count","site_name","site_link","site_url","account_url","resubscribe_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.canceled', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_CANCELED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","cancellation_date","cancellation_reason","subscription_status","site_name","site_link","site_url","account_url","resubscribe_url","html.document","text.document"]}');
INSERT IGNORE INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`, `params`) VALUES
    ('com_phocacart.subscription.status_changed', 'com_phocacart', '', 'COM_PHOCACART_MAIL_SUBSCRIPTION_STATUS_CHANGED_SUBJECT', 'COM_PHOCACART_MAIL_SUBSCRIPTION_STATUS_CHANGED_BODY', 'COM_PHOCACART_MAIL_SUBSCRIPTION_STATUS_CHANGED_HTMLBODY', '', '{"tags":["user_name","user_username","user_email","product_name","product_title","product_sku","product_link","start_date","end_date","subscription_status","renewal_count","site_name","site_link","site_url","account_url","html.document","text.document"]}');
