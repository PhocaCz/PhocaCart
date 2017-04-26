<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
//if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
require_once( JPATH_COMPONENT.'/controller.php' );
require_once( JPATH_COMPONENT.'/helpers/route.php' );


JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

/*
if (! class_exists('PhocaCartLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/loader.php');
}

phocacart import('phocacart.utils.settings');
phocacart import('phocacart.utils.utils');
phocacart import('phocacart.utils.log');
phocacart import('phocacart.security.security');
phocacart import('phocacart.utils.extension');
phocacart import('phocacart.category.category');
phocacart import('phocacart.category.categorymultiple');
phocacart import('phocacart.date.date');
phocacart import('phocacart.path.path');
phocacart import('phocacart.path.route');
phocacart import('phocacart.access.access');
phocacart import('phocacart.ordering.ordering');
phocacart import('phocacart.image.image');
phocacart import('phocacart.pagination.pagination');
phocacart import('phocacart.shipping.shipping');
phocacart import('phocacart.payment.payment');
phocacart import('phocacart.cart.cart');
phocacart import('phocacart.cart.cartdb');
phocacart import('phocacart.cart.rendercart');
phocacart import('phocacart.cart.rendercheckout');
phocacart import('phocacart.price.price');
phocacart import('phocacart.related.related');
phocacart import('phocacart.tag.tag');
phocacart import('phocacart.stock.stock');
phocacart import('phocacart.attribute.attribute');
phocacart import('phocacart.specification.specification');
phocacart import('phocacart.review.review');
phocacart import('phocacart.file.filethumbnail');
phocacart import('phocacart.currency.currency');
phocacart import('phocacart.form.formuser');
phocacart import('phocacart.form.formitems');
phocacart import('phocacart.zone.zone');
phocacart import('phocacart.country.country');
phocacart import('phocacart.region.region');
phocacart import('phocacart.user.user');
phocacart import('phocacart.user.guestuser');
phocacart import('phocacart.render.renderjs');
phocacart import('phocacart.coupon.coupon');
phocacart import('phocacart.product.product');
phocacart import('phocacart.order.order');
phocacart import('phocacart.order.orderstatus');
phocacart import('phocacart.order.orderview');
phocacart import('phocacart.order.orderrender');
phocacart import('phocacart.compare.compare');
phocacart import('phocacart.wishlist.wishlist');
phocacart import('phocacart.download.download');
phocacart import('phocacart.render.renderfront');
phocacart import('phocacart.email.email');
phocacart import('phocacart.search.search');
phocacart import('phocacart.feed.feed');
phocacart import('phocacart.render.rendermedia');
phocacart import('phocacart.captcha.recaptcha');
phocacart import('phocacart.statistics.hits');
phocacart import('phocacart.tax.tax');

*/

$controller = JControllerLegacy::getInstance('PhocaCart');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
?>