<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!JFactory::getUser()->authorise('core.manage', 'com_phocacart')) {
	throw new Exception(JText::_('COM_PHOCACART_ERROR_ALERTNOAUTHOR'), 404);
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
require_once( JPATH_COMPONENT.'/controller.php' );

JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

/*
if (! class_exists('PhocaCartLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/loader.php');
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
require_once( JPATH_COMPONENT.'/controller.php' );
phocacart import('phocacart.utils.utils');
phocacart import('phocacart.utils.settings');
phocacart import('phocacart.utils.exception');
phocacart import('phocacart.utils.log');
phocacart import('phocacart.utils.batchhelper');
phocacart import('phocacart.utils.extension');
phocacart import('phocacart.date.date');
phocacart import('phocacart.path.path');
phocacart import('phocacart.file.file');
phocacart import('phocacart.access.access');
phocacart import('phocacart.file.fileupload');
phocacart import('phocacart.file.fileuploadmultiple');
phocacart import('phocacart.file.fileuploadsingle');
phocacart import('phocacart.file.filethumbnail');
phocacart import('phocacart.image.image');
phocacart import('phocacart.image.imageadditional');
phocacart import('phocacart.attribute.attribute');
phocacart import('phocacart.specification.specification');
phocacart import('phocacart.region.region');
phocacart import('phocacart.country.country');
phocacart import('phocacart.zone.zone');
phocacart import('phocacart.related.related');
phocacart import('phocacart.coupon.coupon');
phocacart import('phocacart.shipping.shipping');
phocacart import('phocacart.payment.payment');
phocacart import('phocacart.render.renderadmin');
phocacart import('phocacart.render.renderadminview');
phocacart import('phocacart.render.renderadminviews');
phocacart import('phocacart.render.renderadminmedia');
phocacart import('phocacart.render.renderjs');
phocacart import('phocacart.html.category');
phocacart import('phocacart.html.batch');
phocacart import('phocacart.html.featured');
phocacart import('phocacart.category.category');
phocacart import('phocacart.category.categorymultiple');
phocacart import('phocacart.tag.tag');
phocacart import('phocacart.user.user');
phocacart import('phocacart.user.guestuser');
phocacart import('phocacart.form.formuser');
phocacart import('phocacart.form.formitems');
phocacart import('phocacart.country.country');
phocacart import('phocacart.product.product');
phocacart import('phocacart.html.jgrid');
phocacart import('phocacart.order.order');
phocacart import('phocacart.order.orderstatus');
phocacart import('phocacart.order.orderview');
phocacart import('phocacart.order.orderrender');
phocacart import('phocacart.currency.currency');
phocacart import('phocacart.price.price');
phocacart import('phocacart.download.download');
phocacart import('phocacart.email.email');
phocacart import('phocacart.stock.stock');
phocacart import('phocacart.statistics.statistics');
phocacart import('phocacart.tax.tax');*/


jimport('joomla.application.component.controller');
$controller	= JControllerLegacy::getInstance('phocacartCp');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
?>