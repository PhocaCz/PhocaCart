<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Router\Route;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartReports extends HtmlView
{

	protected $state;
	protected $t;
	protected $r;
	protected $s;
	protected $params;

	protected $items 	= array();
	protected $total	= array();




	function display($tpl = null) {

		$this->t				= PhocacartUtils::setVars('report');
		$this->r				= new PhocacartRenderAdminviews();
		$this->s                = PhocacartRenderStyle::getStyles();
		$this->state			= $this->get('State');
		$this->t['date_from'] 	= $this->state->get('filter.date_from', PhocacartDate::getCurrentDate(30));
		$this->t['date_to'] 	= $this->state->get('filter.date_to', PhocacartDate::getCurrentDate());
		$this->t['date_days'] 	= PhocacartDate::getDateDays($this->t['date_from'], $this->t['date_to']);

		$this->params			= PhocacartUtils::getComponentParameters();
		$app				= Factory::getApplication();
		$this->t['format']	= $app->input->get('format', '', 'string');

		if (!empty($this->t['date_days'])) {
			$count	= iterator_count($this->t['date_days']);
		} else {
			$count = 0;
		}

		$this->t['data_error'] 			= 0;
		$this->t['data_possible_days'] 	= 365;
		if ($count > (int)$this->t['data_possible_days']) {
			$this->state->set('filter.date_to', '');
			$this->state->set('filter.date_from', '');
			$this->t['data_error'] = 1;
		}

		if ($this->t['data_error'] == 0) {

			$items				= $this->get('Items');
			$orderCalc 			= new PhocacartOrderCalculation();
			$orderCalc->calculateOrderItems($items);
			$this->items		= $orderCalc->getItems();
			$this->total		= $orderCalc->getTotal();
			$this->currencies 	= $orderCalc->getCurrencies();

		}

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}


		$media = new PhocacartRenderAdminmedia();


		$this->addToolbar();
		parent::display($tpl);
	}

	function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.report_id'));

		ToolbarHelper::title( Text::_( $this->t['l'].'_REPORTS' ), 'chart' );

		// This button is unnecessary but it is displayed because Joomla! design bug
		$bar = Toolbar::getInstance( 'toolbar' );
		$dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-primary btn-small"><i class="icon-home-2" title="'.Text::_('COM_PHOCACART_CONTROL_PANEL').'"></i> '.Text::_('COM_PHOCACART_CONTROL_PANEL').'</a>';
		$bar->appendButton('Custom', $dhtml);




		$linkTxt 		= Route::_( 'index.php?option=com_phocacart&view=phocacartreports&tmpl=component&format=raw' );
		// Direct download
		$linkTxtHandler	= 'onclick="window.open(this.href, \'orderview\', \'width=880,height=560,scrollbars=yes,menubar=no,resizable=yes\');return false;"';
		//$linkTxtHandler = '';
		$dhtml = '<joomla-toolbar-button><a href="'.$linkTxt.'" class="btn btn-primary btn-small" '.$linkTxtHandler.'><i id="ph-icon-text" class="icon-dummy fa fa-list-alt fa-fw ph-icon-text fas fa fa-file"></i>'.Text::_('COM_PHOCACART_VIEW_REPORT_HTML').'</a></joomla-toolbar-button>';
		$bar->appendButton('Custom', $dhtml);

		$this->t['plugin-pdf']		= PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
		$this->t['component-pdf']	= PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');
		if ($this->t['plugin-pdf'] == 1 && $this->t['component-pdf']) {
			$linkPdf 		= Route::_( 'index.php?option=com_phocacart&view=phocacartreports&tmpl=component&format=pdf' );
			$linkPdfHandler	= 'onclick="window.open(this.href, \'orderview\', \'width=880,height=560,scrollbars=yes,menubar=no,resizable=yes\');return false;"';
			//$linkPdfHandler = '';
			$dhtml = '<joomla-toolbar-button><a href="'.$linkPdf.'" class="btn btn-small btn-danger" '.$linkPdfHandler.'><i id="ph-icon-pdf" class="icon-dummy fa fa-list-alt fa-fw ph-icon-pdf fas fa fa-file-pdf"></i>'.Text::_('COM_PHOCACART_VIEW_REPORT_PDF').'</a></joomla-toolbar-button>';
			$bar->appendButton('Custom', $dhtml);

		}
	/*
		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {

			ToolbarHelper::divider();
			ToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			ToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($canDo->get('core.delete')) {
			ToolbarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartlogs.delete', $this->t['l'].'_DELETE');
		}*/
		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}

	protected function getSortFields() {
		return array(
			/*'a.ordering'		=> Text::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> Text::_($this->t['l'] . '_TITLE'),
			'a.published' 		=> Text::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> Text::_('JGRID_HEADING_ID'),*/
			'a.date' 			=> Text::_($this->t['l'] . '_DATE'),
			'a.order_number' 	=> Text::_($this->t['l'] . '_ORDER_NUMBER'),
			'a.currency_code'	=> Text::_($this->t['l'] . '_CURRENCY'),
			//'a.type'			=> Text::_($this->t['l'] . '_TYPE')
		);
	}
}
?>
