<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
require_once( JPATH_COMPONENT.'/controller.php' );
require_once( JPATH_COMPONENT.'/helpers/route.php' );

if (! class_exists('PhocaCartLoader')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocacart'.DS.'libraries'.DS.'loader.php');
}

phocacartimport('phocacart.utils.settings');
phocacartimport('phocacart.utils.utils');
phocacartimport('phocacart.utils.log');
phocacartimport('phocacart.date.date');
phocacartimport('phocacart.path.path');
phocacartimport('phocacart.path.route');
phocacartimport('phocacart.access.access');
phocacartimport('phocacart.ordering.ordering');
phocacartimport('phocacart.image.image');
phocacartimport('phocacart.pagination.pagination');
phocacartimport('phocacart.shipping.shipping');
phocacartimport('phocacart.payment.payment');
phocacartimport('phocacart.cart.cart');
phocacartimport('phocacart.cart.cartdb');
phocacartimport('phocacart.cart.rendercart');
phocacartimport('phocacart.cart.rendercheckout');
phocacartimport('phocacart.price.price');
phocacartimport('phocacart.related.related');
phocacartimport('phocacart.tag.tag');
phocacartimport('phocacart.stock.stock');
phocacartimport('phocacart.attribute.attribute');
phocacartimport('phocacart.specification.specification');
phocacartimport('phocacart.review.review');
phocacartimport('phocacart.file.filethumbnail');
phocacartimport('phocacart.currency.currency');
phocacartimport('phocacart.form.formuser');
phocacartimport('phocacart.form.formitems');
phocacartimport('phocacart.country.country');
phocacartimport('phocacart.region.region');
phocacartimport('phocacart.user.user');
phocacartimport('phocacart.user.guestuser');
phocacartimport('phocacart.render.renderjs');
phocacartimport('phocacart.coupon.coupon');
phocacartimport('phocacart.product.product');
phocacartimport('phocacart.order.order');
phocacartimport('phocacart.order.orderstatus');
phocacartimport('phocacart.order.orderview');
phocacartimport('phocacart.order.orderrender');
phocacartimport('phocacart.compare.compare');
phocacartimport('phocacart.download.download');
phocacartimport('phocacart.render.renderfront');
phocacartimport('phocacart.email.email');

/*
if($controller = JRequest::getWord('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}
$classname    = 'PhocaCartController'.ucfirst($controller);
$controller   = new $classname( );
$controller->execute( JFactory::getApplication()->input->get('task') );
$controller->redirect();*/

$controller = JControllerLegacy::getInstance('PhocaCart');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
?>