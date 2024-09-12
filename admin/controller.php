<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;

jimport('joomla.application.component.controller');
$app		= Factory::getApplication();
$option 	= $app->input->get('option');

$l['cp']	= array('COM_PHOCACART_CONTROL_PANEL', '');
$l['ps']	= array('COM_PHOCACART_PRODUCTS', 'phocacartitems');
$l['cs']	= array('COM_PHOCACART_CATEGORIES', 'phocacartcategories');
$l['sp']	= array('COM_PHOCACART_SPECIFICATIONS', 'phocacartspecifications');
$l['mn']	= array('COM_PHOCACART_MANUFACTURERS', 'phocacartmanufacturers');
$l['os']	= array('COM_PHOCACART_ORDERS', 'phocacartorders');
$l['st']	= array('COM_PHOCACART_ORDER_STATUSES', 'phocacartstatuses');
$l['sk']	= array('COM_PHOCACART_STOCK_STATUSES', 'phocacartstockstatuses');
$l['sh']	= array('COM_PHOCACART_SHIPPING', 'phocacartshippings');
$l['ct']	= array('COM_PHOCACART_COUNTRIES', 'phocacartcountries');
$l['re']	= array('COM_PHOCACART_REGIONS', 'phocacartregions');
$l['zo']	= array('COM_PHOCACART_ZONES', 'phocacartzones');
$l['pa']	= array('COM_PHOCACART_PAYMENT', 'phocacartpayments');
$l['cu']	= array('COM_PHOCACART_CURRENCIES', 'phocacartcurrencies');
$l['tx']	= array('COM_PHOCACART_TAXES', 'phocacarttaxes');
$l['us']	= array('COM_PHOCACART_CUSTOMERS', 'phocacartusers');
$l['gr']	= array('COM_PHOCACART_CUSTOMER_GROUPS', 'phocacartgroups');
$l['rp']	= array('COM_PHOCACART_REWARD_POINTS', 'phocacartrewards');
$l['ff']	= array('COM_PHOCACART_FORM_FIELDS', 'phocacartformfields');
$l['rw']	= array('COM_PHOCACART_REVIEWS', 'phocacartreviews');
//$l['vo']	= array('COM_PHOCACART_VOUCHERS', 'phocacartvouchers');
$l['co']	= array('COM_PHOCACART_COUPONS', 'phocacartcoupons');
$l['di']	= array('COM_PHOCACART_DISCOUNTS', 'phocacartdiscounts');
$l['do']	= array('COM_PHOCACART_DOWNLOADS', 'phocacartdownloads');
$l['tg']	= array('COM_PHOCACART_TAGS', 'phocacarttags');
$l['pr']	= array('COM_PHOCACART_PARAMETERS', 'phocacartparameters');
$l['pv']	= array('COM_PHOCACART_PARAMETER_VALUES', 'phocacartparametervalues');
$l['fd']	= array('COM_PHOCACART_XML_FEEDS', 'phocacartfeeds');
$l['wl']	= array('COM_PHOCACART_WISH_LISTS', 'phocacartwishlists');
$l['qu']	= array('COM_PHOCACART_QUESTIONS', 'phocacartquestions');
$l['ot']	= array('COM_PHOCACART_OPENING_TIMES', 'phocacarttimes');
$l['si']	= array('COM_PHOCACART_SUBMITTED_ITEMS', 'phocacartsubmititems');
$l['sc']	= array('COM_PHOCACART_STATISTICS', 'phocacartstatistics');
$l['rt']	= array('COM_PHOCACART_REPORTS', 'phocacartreports');
$l['hi']	= array('COM_PHOCACART_HITS', 'phocacarthits');
$l['im']	= array('COM_PHOCACART_IMPORT', 'phocacartimports');
$l['ex']	= array('COM_PHOCACART_EXPORT', 'phocacartexports');
$l['lo']	= array('COM_PHOCACART_SYSTEM_LOG', 'phocacartlogs');
$l['in']	= array('COM_PHOCACART_INFO', 'phocacartinfo');
$l['et']	= array('COM_PHOCACART_EXTENSIONS', 'phocacartextensions');

$l['ve']	= array('COM_PHOCACART_VENDORS', 'phocacartvendors');
$l['se']	= array('COM_PHOCACART_SECTIONS', 'phocacartsections');
$l['un']	= array('COM_PHOCACART_UNITS', 'phocacartunits');
$l['bp']	= array('COM_PHOCACART_BULK_PRICE_EDITOR', 'phocacartbulkprices');

// Submenu view


$view	= Factory::getApplication()->input->get('view');
$layout	= Factory::getApplication()->input->get('layout');

if ($layout == 'edit') {
} else {
	foreach ($l as $k => $v) {

		if ($v[1] == '') {
			$link = 'index.php?option='.$option;
		} else {
			$link = 'index.php?option='.$option.'&view=';
		}

		if ($view == $v[1]) {
			Sidebar::addEntry(Text::_($v[0]), $link.$v[1], true );
		} else {
			Sidebar::addEntry(Text::_($v[0]), $link.$v[1]);
		}
	}
}
class phocaCartCpController extends BaseController
{
	function display($cachable = false, $urlparams = array()) {
		parent::display($cachable, $urlparams);
	}
}
?>
