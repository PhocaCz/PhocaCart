<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
/*********** XML PARAMETERS AND VALUES ************/
$xml_item = "component";// component | template
$xml_file = "phocacart.xml";		
$xml_name = "com_phocacart";
$xml_name2 = "Phoca Cart";
$xml_creation_date = "23/04/2017";
$xml_author = "Jan Pavelka (www.phoca.cz)";
$xml_author_email = "";
$xml_author_url = "www.phoca.cz";
$xml_copyright = "Jan Pavelka";
$xml_license = "GNU/GPL";
$xml_version = "3.0.0 RC6";
$xml_description = "Phoca Cart";
$xml_copy_file = 1;//Copy other files in to administration area (only for development), ./front, ./language, ./other
$xml_script_file = 'install/script.php';

$iP = 'media/com_phocacart/images/administrator/images/';

$xml_menu = array (0 => "COM_PHOCACART", 1 => "option=com_phocacart", 2 => "media/com_phocacart/images/administrator/images/icon-16-cart-menu.png");

$t = 'COM_PHOCACART_CONTROLPANEL';
$xml_submenu[] = array (0 => $t, 1 => "option=com_phocacart", 2 => $iP.'icon-16-pc-menu-cp.png', 3 => $t, 4 => 'phocacartcp');

$t = 'COM_PHOCACART_PRODUCTS';$v = 'phocacartitems';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-item.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_CATEGORIES';$v = 'phocacartcategories';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-category.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_SPECIFICATIONS';$v = 'phocacartspecifications';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-specification.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_MANUFACTURERS';$v = 'phocacartmanufacturers';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-manufacturer.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_ORDERS';$v = 'phocacartorders';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-order.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_ORDER_STATUSES';$v = 'phocacartstatuses';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-orderstatus.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_STOCK_STATUSES';$v = 'phocacartstockstatuses';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-stockstatus.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_SHIPPING';$v = 'phocacartshippings';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-shipping.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_COUNTRIES';$v = 'phocacartcountries';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-country.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_REGIONS';$v = 'phocacartregions';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-region.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_ZONES';$v = 'phocacartzones';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-zone.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_PAYMENT';$v = 'phocacartpayments';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-payment.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_CURRENCIES';$v = 'phocacartcurrencies';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-currency.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_TAXES';$v = 'phocacarttaxes';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-tax.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_USERS';$v = 'phocacartusers';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-user.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_FORM_FIELDS';$v = 'phocacartformfields';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-user.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_REVIEWS';$v = 'phocacartreviews';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-review.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_COUPONS';$v = 'phocacartcoupons';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-coupon.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_DISCOUNTS';$v = 'phocacartdiscounts';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-discount.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_DOWNLOADS';$v = 'phocacartdownloads';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-download.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_TAGS';$v = 'phocacarttags';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-tag.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_XML_FEEDS';$v = 'phocacartfeeds';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-feeds.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_WISH_LISTS';$v = 'phocacartwishlists';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-wishlists.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_QUESTIONS';$v = 'phocacartquestions';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-questions.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_STATISTICS';$v = 'phocacartstatistics';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-statistics.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_HITS';$v = 'phocacarthits';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-hits.png', 3 => $t, 4 => $v);


$t = 'COM_PHOCACART_IMPORT';$v = 'phocacartimports';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-import.png', 3 => $t, 4 => $v);


$t = 'COM_PHOCACART_EXPORT';$v = 'phocacartexports';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-export.png', 3 => $t, 4 => $v);


$t = 'COM_PHOCACART_SYSTEM_LOG';$v = 'phocacartlogs';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-log.png', 3 => $t, 4 => $v);

$t = 'COM_PHOCACART_INFO';$v = 'phocacartinfo';
$xml_submenu[] = array (0 => $t, 1 => 'option=com_phocacart&view='.$v, 2 => $iP.'icon-16-pc-menu-info.png', 3 => $t, 4 => $v);

$xml_install_file = ''; 
$xml_uninstall_file = '';
/*********** XML PARAMETERS AND VALUES ************/
?>