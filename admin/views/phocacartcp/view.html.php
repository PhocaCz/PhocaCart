<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );

use Phoca\Render\Adminviews;

class PhocaCartCpViewPhocaCartCp extends JViewLegacy
{
	protected $t;
	protected $r;
	protected $s;

	function display($tpl = null) {


		$this->t	= PhocacartUtils::setVars();
		$this->s    = PhocacartRenderStyle::getStyles();
		$this->r	= new PhocacartRenderAdminview();
		/*$this->views= array(
		'items'			=> array($this->t['l'] . '_PRODUCTS', 'folder-close', '#c1a46d'),
		'categories'	=> array($this->t['l'] . '_CATEGORIES', 'folder-open', '#da7400'),
		'specifications'=> array($this->t['l'] . '_SPECIFICATIONS', 'th-list', '#4e5f81'),
		'manufacturers'	=> array($this->t['l'] . '_MANUFACTURERS', 'home', '#ff7d49'),
		'orders'		=> array($this->t['l'] . '_ORDERS', 'shopping-cart', '#0099CC'),
		'statuses'		=> array($this->t['l'] . '_ORDER_STATUSES', 'time', '#c1976d'),
		'stockstatuses'	=> array($this->t['l'] . '_STOCK_STATUSES', 'tasks', '#777777'),
		'shippings'		=> array($this->t['l'] . '_SHIPPING', 'barcode', '#afbb6a'),
		'countries'		=> array($this->t['l'] . '_COUNTRIES', 'globe', '#478CD1'),
		'regions'		=> array($this->t['l'] . '_REGIONS', 'globe', '#01868B'),
		'zones'			=> array($this->t['l'] . '_ZONES', 'globe', '#a5dee5'),
		'payments'		=> array($this->t['l'] . '_PAYMENT', 'credit-card', '#4f9ce2'),
		'currencies'	=> array($this->t['l'] . '_CURRENCIES', 'eur', '#dca300'),
		'taxes'			=> array($this->t['l'] . '_TAXES', 'calendar', '#dd5500'),
		'users'			=> array($this->t['l'] . '_CUSTOMERS', 'user', '#7faa7f'),
		'groups'		=> array($this->t['l'] . '_CUSTOMER_GROUPS', 'user', '#aa7faa'),
		'rewards'		=> array($this->t['l'] . '_REWARD_POINTS', 'certificate', '#7faaaa'),
		'formfields'	=> array($this->t['l'] . '_FORM_FIELDS', 'list-alt', '#ffde00'),
		'reviews'		=> array($this->t['l'] . '_REVIEWS', 'comment', '#399ed0'),
		//'ratings'		=> array($this->t['l'] . '_RATINGS', 'x x x', '#ffde00'),
		//'vouchers'	=> array($this->t['l'] . '_VOUCHERS', 'x x x', '#ffde00'),
		'coupons'		=> array($this->t['l'] . '_COUPONS', 'gift', '#FF6685'),
		'discounts'		=> array($this->t['l'] . '_DISCOUNTS', 'piggy-bank', '#aa56fe'),
		'downloads'		=> array($this->t['l'] . '_DOWNLOADS', 'download-alt', '#33af49'),
		'tags'			=> array($this->t['l'] . '_TAGS', 'tag', '#CC0033'),
		'parameters'	=> array($this->t['l'] . '_PARAMETERS', 'align-justify', '#0040ff'),
		'parametervalues'=> array($this->t['l'] . '_PARAMETER_VALUES', 'list', '#0040ff'),
		'feeds'			=> array($this->t['l'] . '_XML_FEEDS', 'bullhorn', '#ffb300'),
		'wishlists'		=> array($this->t['l'] . '_WISH_LISTS', 'heart', '#EA7C7C'),
		'questions'		=> array($this->t['l'] . '_QUESTIONS', 'question-sign', '#9900CC'),
		'times'			=> array($this->t['l'] . '_OPENING_TIMES', 'time', '#73b9ff'),
		'submititems'	=> array($this->t['l'] . '_SUBMITTED_ITEMS', 'duplicate', '#7fff73'),
		'statistics'	=> array($this->t['l'] . '_STATISTICS', 'stats', '#c1756d'),
		'reports'		=> array($this->t['l'] . '_REPORTS', 'list-alt', '#8c0069'),
		'hits'			=> array($this->t['l'] . '_HITS', 'equalizer', '#fb1000'),
		'imports'		=> array($this->t['l'] . '_IMPORT', 'import', '#668099'),
		'exports'		=> array($this->t['l'] . '_EXPORT', 'export', '#669999'),
		'logs'			=> array($this->t['l'] . '_SYSTEM_LOG', 'list', '#c0c0c0'),
		'info'			=> array($this->t['l'] . '_INFO', 'info-sign', '#3378cc'),
		'extensions'	=> array($this->t['l'] . '_EXTENSIONS', 'th-large', '#2693ff'),
		'vendors'		=> array($this->t['l'] . '_VENDORS', 'user', '#b30059'),
		'sections'		=> array($this->t['l'] . '_SECTIONS', 'unchecked', '#b35900'),
		'units'			=> array($this->t['l'] . '_UNITS', 'modal-window', '#ff9326'),
		);*/

		$i = ' icon-';
		$d = 'duotone ';
		$this->views= array(
		'items'			=> array($this->t['l'] . '_PRODUCTS', $d.$i .'archive', '#c1a46d'),
		'categories'	=> array($this->t['l'] . '_CATEGORIES', $d.$i .'folder-open', '#da7400'),
		'specifications'=> array($this->t['l'] . '_SPECIFICATIONS', $d.$i .'equalizer', '#4e5f81'),
		'manufacturers'	=> array($this->t['l'] . '_MANUFACTURERS', $d.$i .'home', '#ff7d49'),
		'orders'		=> array($this->t['l'] . '_ORDERS', $d.$i .'cart', '#0099CC'),
		'statuses'		=> array($this->t['l'] . '_ORDER_STATUSES', $d.$i .'disable-motion', '#c1976d'),
		'stockstatuses'	=> array($this->t['l'] . '_STOCK_STATUSES', $d.$i .'components', '#777777'),
		'shippings'		=> array($this->t['l'] . '_SHIPPING', $d.$i .'cube', '#afbb6a'),
		'countries'		=> array($this->t['l'] . '_COUNTRIES', $i .'globe', '#478CD1'),
		'regions'		=> array($this->t['l'] . '_REGIONS', $i .'globe', '#01868B'),
		'zones'			=> array($this->t['l'] . '_ZONES', $d.$i .'location', '#a5dee5'),
		'payments'		=> array($this->t['l'] . '_PAYMENT', $d.$i .'credit', '#4f9ce2'),
		'currencies'	=> array($this->t['l'] . '_CURRENCIES', $d.$i .'tags-squared', '#dca300'),
		'taxes'			=> array($this->t['l'] . '_TAXES', $i .'calendar', '#dd5500'),
		'users'			=> array($this->t['l'] . '_CUSTOMERS', $d.$i .'users', '#7faa7f'),
		'groups'		=> array($this->t['l'] . '_CUSTOMER_GROUPS', $d.$i .'groups', '#aa7faa'),
		'rewards'		=> array($this->t['l'] . '_REWARD_POINTS',  $d.$i .'vcard', '#7faaaa'),
		'formfields'	=> array($this->t['l'] . '_FORM_FIELDS',  $d.$i .'fields', '#ffde00'),
		'reviews'		=> array($this->t['l'] . '_REVIEWS', $d.$i.'comment', '#399ed0'),
		//'ratings'		=> array($this->t['l'] . '_RATINGS', $i .'x x x', '#ffde00'),
		//'vouchers'	=> array($this->t['l'] . '_VOUCHERS', $i .'x x x', '#ffde00'),
		'coupons'		=> array($this->t['l'] . '_COUPONS', $i .'gift', '#FF6685'),
		'discounts'		=> array($this->t['l'] . '_DISCOUNTS',$d.$i .'scissors', '#aa56fe'),
		'downloads'		=> array($this->t['l'] . '_DOWNLOADS', $i .'download-alt', '#33af49'),
		'tags'			=> array($this->t['l'] . '_TAGS', $d.$i .'tag-double', '#CC0033'),
		'parameters'	=> array($this->t['l'] . '_PARAMETERS', $i .'ellipsis-h', '#0040ff'),
		'parametervalues'=> array($this->t['l'] . '_PARAMETER_VALUES', $i .'ellipsis-v', '#0040ff'),
		'feeds'			=> array($this->t['l'] . '_XML_FEEDS', $i .'feed', '#ffb300'),
		'wishlists'		=> array($this->t['l'] . '_WISH_LISTS', $i .'heart', '#EA7C7C'),
		'questions'		=> array($this->t['l'] . '_QUESTIONS', $d.$i .'messaging', '#9900CC'),
		'times'			=> array($this->t['l'] . '_OPENING_TIMES', $i .'clock-alt', '#73b9ff'),
		'submititems'	=> array($this->t['l'] . '_SUBMITTED_ITEMS', $d.$i .'duplicate-alt', '#7fff73'),
		'statistics'	=> array($this->t['l'] . '_STATISTICS', $d.$i .'pie', '#c1756d'),
		'reports'		=> array($this->t['l'] . '_REPORTS', $d.$i .'chart', '#8c0069'),
		'hits'			=> array($this->t['l'] . '_HITS', $d.$i .'mouse-pointer-highlighter', '#fb1000'),
		'imports'		=> array($this->t['l'] . '_IMPORT', $d.$i .'sign-in', '#668099'),
		'exports'		=> array($this->t['l'] . '_EXPORT', $d.$i .'sign-out', '#669999'),
		'logs'			=> array($this->t['l'] . '_SYSTEM_LOG', $d.$i .'logs', '#c0c0c0'),
		'info'			=> array($this->t['l'] . '_INFO', $d.$i .'info-circle', '#3378cc'),
		'extensions'	=> array($this->t['l'] . '_EXTENSIONS', $d.$i .'modules', '#2693ff'),
		'vendors'		=> array($this->t['l'] . '_VENDORS', $d.$i .'users', '#b30059'),
		'sections'		=> array($this->t['l'] . '_SECTIONS', $d.$i .'notification-circle', '#b35900'),
		'units'			=> array($this->t['l'] . '_UNITS', $d.$i .'menu', '#ff9326'),
		'bulkprices'			=> array($this->t['l'] . '_BULK_PRICE_EDITOR', $d.$i .'disable-motion', '#f310de')
		);

	/*
		$this->views= array(
		'items'			=> array($this->t['l'] . '_PRODUCTS', 'folder-close', '#eff8a5'),
		'categories'	=> array($this->t['l'] . '_CATEGORIES', 'folder-open', '#fdb784'),
		'specifications'=> array($this->t['l'] . '_SPECIFICATIONS', 'th-list', '#fa9d58'),
		'manufacturers'	=> array($this->t['l'] . '_MANUFACTURERS', 'home', '#ed145b'),
		'orders'		=> array($this->t['l'] . '_ORDERS', 'shopping-cart', '#f86cb5'),
		'statuses'		=> array($this->t['l'] . '_ORDER_STATUSES', 'time', '#f86cd9'),
		'stockstatuses'	=> array($this->t['l'] . '_STOCK_STATUSES', 'tasks', '#d673dd'),
		'shippings'		=> array($this->t['l'] . '_SHIPPING', 'barcode', '#f4adf9'),
		'countries'		=> array($this->t['l'] . '_COUNTRIES', 'globe', '#4f70a6'),
		'regions'		=> array($this->t['l'] . '_REGIONS', 'globe', '#7fadf8'),
		'payments'		=> array($this->t['l'] . '_PAYMENT', 'credit-card', '#88ecac'),
		'currencies'	=> array($this->t['l'] . '_CURRENCIES', 'eur', '#b9f3cd'),
		'taxes'			=> array($this->t['l'] . '_TAXES', 'calendar', '#eff8a5'),
		'users'			=> array($this->t['l'] . '_USERS', 'user', '#fdb784'),
		'formfields'	=> array($this->t['l'] . '_FORM_FIELDS', 'list-alt', '#fa9d58'),
		'reviews'		=> array($this->t['l'] . '_REVIEWS', 'comment', '#ed145b'),
		//'ratings'		=> array($this->t['l'] . '_RATINGS', 'x x x', '#ffde00'),
		//'vouchers'	=> array($this->t['l'] . '_VOUCHERS', 'x x x', '#ffde00'),
		'coupons'		=> array($this->t['l'] . '_COUPONS', 'gift', '#f86cb5'),
		'downloads'		=> array($this->t['l'] . '_DOWNLOADS', 'download-alt', '#f86cd9'),
		'tags'			=> array($this->t['l'] . '_TAGS', 'tag', '#d673dd'),
		'statistics'	=> array($this->t['l'] . '_STATISTICS', 'stats', '#f4adf9'),
		'logs'			=> array($this->t['l'] . '_SYSTEM_LOG', 'list', '#4f70a6'),
		'info'			=> array($this->t['l'] . '_INFO', 'info-sign', '#7fadf8')
		);
		*/

		$this->t['version'] = PhocacartUtils::getPhocaVersion('com_phocacart');

		$paramsC = PhocacartUtils::getComponentParameters();
		$this->t['enable_wizard']	= $paramsC->get( 'enable_wizard', 1 );



		$media = new PhocacartRenderAdminmedia();


		$this->addToolbar();
		parent::display($tpl);

	}

	protected function addToolbar() {
		require_once JPATH_COMPONENT.'/helpers/phocacartcp.php';

		$state	= $this->get('State');
		$canDo	= PhocaCartCpHelper::getActions();
		JToolbarHelper::title( JText::_( 'COM_PHOCACART_PC_CONTROL_PANEL' ), 'home' );

		// This button is unnecessary but it is displayed because Joomla! design bug
		$bar = JToolbar::getInstance( 'toolbar' );
		$dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-small"><i class="icon-home-2" title="'.JText::_('COM_PHOCACART_CONTROL_PANEL').'"></i> '.JText::_('COM_PHOCACART_CONTROL_PANEL').'</a>';
		$bar->appendButton('Custom', $dhtml);


		if ($canDo->get('core.admin')) {
			JToolbarHelper::preferences('com_phocacart');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help( 'screen.phocacart', true );

		$this->addModal();
	}

	protected function addModal() {


		// Getting Started Wizard
		$this->t['modalwindowdynamic'] = '';
		$autoOpenModal 	= 0;
		$idMd 			= 'phWizardStatusModal';
		$textButton 	= 'COM_PHOCACART_GETTING_STARTED_WIZARD';
		$linkWizard 	= JRoute::_( 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component&page=0', false );
		$w 				= 700;
		$h 				= 400;

		// WIZARD
		// 1 ... run wizard automatically but only if product and category do not exist
		// 2 ... run wizard automatically - force it any way
		// 11 ... run wizard automatically - go to first site of wizard

		// ------------------------------
		// 1) MANUALLY RUN START WIZARD
		// ------------------------------
		// Render Button to Stard Wizard
		PhocacartRenderAdminview::renderWizardButton('start', $idMd , $linkWizard, $w, $h);

		// ---------------------------------
		// 2) AUTOMATICALLY RUN START WIZARD
		// ---------------------------------
		// 2a) Enable Wizard is disabled but category and product exists, don't run wizard at start automatically
		// Seems like user added some data yet, he/she can start wizard manually
		// 2b) But if in Options FORCE WIZARD SET, then run it (enable_wizard =2)
		// 2c) But if in Options FORCE WIZARD SET, then run it (enable_wizard =2)
		$category 	= PhocacartUtils::doesExist('category');
		$product	= PhocacartUtils::doesExist('product');
		if ($this->t['enable_wizard'] == 0 && $category == 1 && $product == 1) {
			$autoOpenModal = 0;
		} else if ($this->t['enable_wizard'] == 1 && $category == 0 && $product == 0) {
			$autoOpenModal = 1;
			$linkWizard = JRoute::_( 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component&page=0', false );
		} else if ($this->t['enable_wizard'] == 2) {
			$autoOpenModal = 1;
			$linkWizard = JRoute::_( 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component&page=0', false );
		}

		// 2d) Run the first page of wizard - user currently work with wizard so it will be automatically loaded
		// 11 means: 1 ... enable 1 ... to to page 1 = 11
		if ($this->t['enable_wizard'] == 11) {
			$autoOpenModal = 1;
			$linkWizard = JRoute::_( 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component&page=1', false );
		}


		$customFooter = '<form action="'.JRoute::_('index.php?option=com_phocacart').'" method="post" style="display: inline;">'
		.' <input type="hidden" name="task" value="phocacartwizard.skipwizard">'
		.' <input type="hidden" name="tmpl" value="component" />'
		.' <input type="hidden" name="option" value="com_phocacart" />'
		.' <button class="btn btn-primary ph-btn"><span class="icon-delete"></span> '.JText::_('COM_PHOCACART_SKIP_WIZARD').'</button>'
		. Joomla\CMS\HTML\HTMLHelper::_('form.token')
		. '</form> ';
		$pageClass = 'ph-wizard-start-page-window';

		$rV = new PhocacartRenderAdminview();
		$this->t['modalwindowdynamic'] = $rV->modalWindowDynamic($idMd, $textButton, $w, $h, false, $autoOpenModal, $linkWizard, 'ph-body-iframe-wizard', $customFooter, $pageClass);


	}
}
?>
