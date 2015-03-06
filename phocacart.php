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
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

if (! class_exists('PhocaCartLoader')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocacart'.DS.'libraries'.DS.'loader.php');
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
require_once( JPATH_COMPONENT.'/controller.php' );
phocacartimport('phocacart.utils.utils');
phocacartimport('phocacart.utils.settings');
phocacartimport('phocacart.utils.exception');
phocacartimport('phocacart.utils.log');
phocacartimport('phocacart.date.date');
phocacartimport('phocacart.path.path');
phocacartimport('phocacart.file.file');
phocacartimport('phocacart.access.access');
phocacartimport('phocacart.file.fileupload');
phocacartimport('phocacart.file.fileuploadmultiple');
phocacartimport('phocacart.file.fileuploadsingle');
phocacartimport('phocacart.file.filethumbnail');
phocacartimport('phocacart.image.image');
phocacartimport('phocacart.image.imageadditional');
phocacartimport('phocacart.attribute.attribute');
phocacartimport('phocacart.specification.specification');
phocacartimport('phocacart.region.region');
phocacartimport('phocacart.country.country');
phocacartimport('phocacart.related.related');
phocacartimport('phocacart.coupon.coupon');
phocacartimport('phocacart.shipping.shipping');
phocacartimport('phocacart.payment.payment');
phocacartimport('phocacart.render.renderadmin');
phocacartimport('phocacart.render.renderadminview');
phocacartimport('phocacart.render.renderadminviews');
phocacartimport('phocacart.render.renderjs');
phocacartimport('phocacart.html.category');
phocacartimport('phocacart.html.batch');
phocacartimport('phocacart.category.category');
phocacartimport('phocacart.tag.tag');
phocacartimport('phocacart.user.user');
phocacartimport('phocacart.user.guestuser');
phocacartimport('phocacart.form.formuser');
phocacartimport('phocacart.form.formitems');
phocacartimport('phocacart.country.country');
phocacartimport('phocacart.product.product');
phocacartimport('phocacart.html.jgrid');
phocacartimport('phocacart.order.order');
phocacartimport('phocacart.order.orderstatus');
phocacartimport('phocacart.order.orderview');
phocacartimport('phocacart.order.orderrender');
phocacartimport('phocacart.currency.currency');
phocacartimport('phocacart.price.price');
phocacartimport('phocacart.download.download');
phocacartimport('phocacart.email.email');
phocacartimport('phocacart.stock.stock');

jimport('joomla.application.component.controller');
$controller	= JControllerLegacy::getInstance('phocacartCp');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
?>