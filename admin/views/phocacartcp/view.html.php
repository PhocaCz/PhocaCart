<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
jimport( 'joomla.application.component.view' );

use Joomla\Event\Event;
use Phoca\PhocaCart\User\AdvancedACL;
use Phoca\Render\Adminviews;

class PhocaCartCpViewPhocaCartCp extends HtmlView
{
	protected $t;
	protected $r;
	protected $s;

	function display($tpl = null) {

		$this->t	= PhocacartUtils::setVars();
		$this->s    = PhocacartRenderStyle::getStyles();
		$this->r	= new PhocacartRenderAdminview();

		$i = ' icon-';
		$d = 'duotone ';

		$this->views = array(
		'items'			=> array($this->t['l'] . '_PRODUCTS', $d.$i .'archive', '#c1a46d'),
		'categories'	=> array($this->t['l'] . '_CATEGORIES', $d.$i .'folder-open', '#da7400'),
		'specifications'=> array($this->t['l'] . '_SPECIFICATIONS', $d.$i .'equalizer', '#4e5f81'),
		'manufacturers'	=> array($this->t['l'] . '_MANUFACTURERS', $d.$i .'wrench', '#ff7d49'),
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
		'coupons'		=> array($this->t['l'] . '_COUPONS', $i .'gift', '#FF6685'),
		'discounts'		=> array($this->t['l'] . '_DISCOUNTS',$d.$i .'scissors', '#aa56fe'),
		'downloads'		=> array($this->t['l'] . '_DOWNLOADS', $i .'download-alt', '#33af49'),
		'tags'			=> array($this->t['l'] . '_TAGS', $d.$i .'tag-double', '#CC0033'),
		'parameters'	=> array($this->t['l'] . '_PARAMETERS', $i .'ellipsis-h', '#0040ff'),
		'parametervalues'=> array($this->t['l'] . '_PARAMETER_VALUES', $i .'ellipsis-v', '#0040ff'),
		'fieldgroups'	=> array('JGLOBAL_FIELD_GROUPS', $i .'ellipsis-h', '#ff4000', 'index.php?option=com_fields&view=groups&context=com_phocacart.phocacartitem'),
		'fields'		=> array('JGLOBAL_FIELDS', $i .'ellipsis-v', '#ff4000', 'index.php?option=com_fields&context=com_phocacart.phocacartitem'),
		'feeds'			=> array($this->t['l'] . '_XML_FEEDS', $i .'feed', '#ffb300'),
		'wishlists'		=> array($this->t['l'] . '_WISH_LISTS', $i .'heart', '#EA7C7C'),
		'contenttypes'	=> array($this->t['l'] . '_CONTENT_TYPES', $i .'sourcetree', '#7faa7f'),
		'questions'		=> array($this->t['l'] . '_QUESTIONS', $d.$i .'messaging', '#9900CC'),
		'times'			=> array($this->t['l'] . '_OPENING_TIMES', $i .'clock-alt', '#73b9ff'),
		'submititems'	=> array($this->t['l'] . '_SUBMITTED_ITEMS', $d.$i .'duplicate-alt', '#7fff73'),
		'statistics'	=> array($this->t['l'] . '_STATISTICS', $d.$i .'pie', '#c1756d'),
		'reports'		=> array($this->t['l'] . '_REPORTS', $d.$i .'chart', '#8c0069'),
		'hits'			=> array($this->t['l'] . '_HITS', $d.$i .'mouse-pointer-highlighter', '#fb1000'),
		'imports'		=> array($this->t['l'] . '_IMPORT', $d.$i .'sign-in', '#668099'),
		'exports'		=> array($this->t['l'] . '_EXPORT', $d.$i .'sign-out', '#669999'),
		'logs'			=> array($this->t['l'] . '_SYSTEM_LOG', $d.$i .'logs', '#c0c0c0'),
		'extensions'	=> array($this->t['l'] . '_EXTENSIONS', $d.$i .'modules', '#2693ff'),
		'vendors'		=> array($this->t['l'] . '_VENDORS', $d.$i .'users', '#b30059'),
		'sections'		=> array($this->t['l'] . '_SECTIONS', $d.$i .'notification-circle', '#b35900'),
		'units'			=> array($this->t['l'] . '_UNITS', $d.$i .'menu', '#ff9326'),
		'bulkprices'	=> array($this->t['l'] . '_BULK_PRICE_EDITOR', $d.$i .'click', '#f310de'),
		'subscriptions'	=> array($this->t['l'] . '_SUBSCRIPTIONS', $d.$i .'calendar', '#B8860B'),

		'info'			=> array($this->t['l'] . '_INFO', $d.$i .'info-circle', '#3378cc'),
		);

		foreach($this->views as $view => $params) {
			if (isset($params[3])) {
				$action = $view;
			} else {
				$action = AdvancedACL::getActionFromView('phocacart' . $view);
			}

			if (!empty($action) && !AdvancedACL::authorise($action)) {
				unset($this->views[$view]);
			}
		}

		$this->t['version'] = PhocacartUtils::getPhocaVersion('com_phocacart');

		$paramsC = PhocacartUtils::getComponentParameters();
		$this->t['enable_wizard']	= $paramsC->get( 'enable_wizard', 1 );

		new PhocacartRenderAdminmedia();

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		require_once JPATH_COMPONENT.'/helpers/phocacartcp.php';

		$state	= $this->get('State');
		$canDo	= PhocaCartCpHelper::getActions();
		ToolbarHelper::title( Text::_( 'COM_PHOCACART_PC_CONTROL_PANEL' ), 'home' );

		// This button is unnecessary but it is displayed because Joomla! design bug
		$bar = Toolbar::getInstance( 'toolbar' );
		$dhtml = '<joomla-toolbar-button><a href="index.php?option=com_phocacart" class="btn btn-primary btn-small"><i class="icon-home-2" title="'.Text::_('COM_PHOCACART_CONTROL_PANEL').'"></i> '.Text::_('COM_PHOCACART_CONTROL_PANEL').'</a></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml);


		if ($canDo->get('core.admin')) {
			ToolbarHelper::preferences('com_phocacart');
			ToolbarHelper::divider();
		}

		ToolbarHelper::help( 'screen.phocacart', true );

		$this->addModal();
	}

	protected function addModal() {


		// Getting Started Wizard
		$this->t['modalwindowdynamic'] = '';
		$autoOpenModal 	= 0;
		$idMd 			= 'phWizardStatusModal';
		$textButton 	= 'COM_PHOCACART_GETTING_STARTED_WIZARD';
		$linkWizard 	= Route::_( 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component&page=0', false );
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
			$linkWizard = Route::_( 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component&page=0', false );
		} else if ($this->t['enable_wizard'] == 2) {
			$autoOpenModal = 1;
			$linkWizard = Route::_( 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component&page=0', false );
		}

		// 2d) Run the first page of wizard - user currently work with wizard so it will be automatically loaded
		// 11 means: 1 ... enable 1 ... to to page 1 = 11
		if ($this->t['enable_wizard'] == 11) {
			$autoOpenModal = 1;
			$linkWizard = Route::_( 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component&page=1', false );
		}


		$customFooter = '<form action="'.Route::_('index.php?option=com_phocacart').'" method="post" style="display: inline;">'
		.' <input type="hidden" name="task" value="phocacartwizard.skipwizard">'
		.' <input type="hidden" name="tmpl" value="component" />'
		.' <input type="hidden" name="option" value="com_phocacart" />'
		.' <button class="btn ph-btn"><span class="icon-delete"></span> '.Text::_('COM_PHOCACART_SKIP_WIZARD').'</button>'
		. HTMLHelper::_('form.token')
		. '</form> ';
		$pageClass = 'ph-wizard-start-page-window';

		$rV = new PhocacartRenderAdminview();

		$this->t['modalwindowdynamic'] = $rV->modalWindowDynamic($idMd, $textButton, $w, $h, false, $autoOpenModal, $linkWizard, 'ph-body-iframe-wizard', $customFooter, $pageClass);


	}
}
?>
