<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartStatistics extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $d;

	function display($tpl = null) {

		$document				= JFactory::getDocument();
		$this->t				= PhocacartUtils::setVars('statistic');
		$this->state			= $this->get('State');
		$this->t['date_from'] 	= $this->state->get('filter.date_from', PhocacartDate::getCurrentDate(30));
		$this->t['date_to'] 	= $this->state->get('filter.date_to', PhocacartDate::getCurrentDate());

		$dateDays = PhocacartDate::getDateDays($this->t['date_from'], $this->t['date_to']);
		if (!empty($dateDays)) {
			$count	= iterator_count($dateDays);
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
			$this->items		= $this->get('Items');
			//$this->pagination	= $this->get('Pagination');
		}


		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		$dataGraph 	= '';
		$amount		= array();
		$orders		= array();


		if (!empty($this->items) && !empty($dateDays)) {
			foreach($dateDays as $date) {
				$amount[ $date->format('Y-m-d') ] = 0;
				$orders[ $date->format('Y-m-d') ] = 0;
			}


			foreach($this->items as $k => $v) {


				if (isset($amount[$v->date_only])) {
					///// -- $amount[$v->date_only] += $v->order_amount;
					$amount[$v->date_only] = $v->order_amount;
				}
				if (isset($orders[$v->date_only])) {
					///// -- $orders[$v->date_only] += $v->count_orders;
					$orders[$v->date_only] = $v->count_orders;
				}
			}
		}


		$this->d['amount'] 	= '';
		$this->d['orders'] 	= '';
		$this->d['ticks']	= '';
		$i = 1;
		foreach ($amount as $k => $v) {
			if ($this->d['amount'] != '') {
				$this->d['amount'] .= ', ';
			}
			//$this->d['amount'] .= '["'.$i.'",'.$v.']';
			//$this->d['amount'] .= '\''.$v.'\'';
			$this->d['amount'] .= (int)$v;
			$i++;
		}
		$i = 1;
		foreach ($orders as $k => $v) {
			if ($this->d['orders'] != '') {
				$this->d['orders'] .= ', ';
			}
			if ($this->d['ticks'] != '') {
				$this->d['ticks'] .= ', ';
			}
			//$this->d['orders'] .= '["'.$i.'",'.$v.']';
			//$this->d['orders'] .= '\''.$v.'\'';
			$this->d['orders'] .= (int)$v;
			//$this->d['ticks'] .= '['.$i.',"'.$k.'"]';
			$this->d['ticks'] .= '\''.$k.'\'';

			$i++;
		}

		$media = new PhocacartRenderAdminmedia();
		JHtml::_('jquery.framework', false);
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/jquery.equalheights.min.js');
		$document->addScriptDeclaration(
		'jQuery(window).load(function(){
			jQuery(\'.ph-admin-stat-box\').equalHeights();
		});');

		// Most viewed and best-selling products
		$this->t['most_viewed'] 	= PhocacartProduct::getMostViewedProducts();
		$this->t['best_selling'] 	= PhocacartProduct::getBestSellingProducts();
		$this->t['best_selling2'] 	= PhocacartProduct::getBestSellingProducts(5, $this->t['date_from'], $this->t['date_to']);

		$this->t['most_viewed_count'] 	= PhocacartProduct::getMostViewedProducts(0, false, false, true);
		$this->t['best_selling_count'] 	= PhocacartProduct::getBestSellingProducts(0, '', '', true);
		$this->t['best_selling2_count'] = PhocacartProduct::getBestSellingProducts(5, $this->t['date_from'], $this->t['date_to'], true);


		$this->addToolbar();
		parent::display($tpl);
	}

	function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		$state	= $this->get('State');
		$class	= ucfirst($this->t['tasks']).'Helper';
		$canDo	= $class::getActions($this->t, $state->get('filter.statistic_id'));

		JToolbarHelper::title( JText::_( $this->t['l'].'_STATISTICS' ), 'stats' );

		// This button is unnecessary but it is displayed because Joomla! design bug
		$bar = JToolbar::getInstance( 'toolbar' );
		$dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-small"><i class="icon-home-2" title="'.JText::_('COM_PHOCACART_CONTROL_PANEL').'"></i> '.JText::_('COM_PHOCACART_CONTROL_PANEL').'</a>';
		$bar->appendButton('Custom', $dhtml);

	/*
		if ($canDo->get('core.create')) {
			JToolbarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit')) {
			JToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {

			JToolbarHelper::divider();
			JToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($canDo->get('core.delete')) {
			JToolbarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'phocacartlogs.delete', $this->t['l'].'_DELETE');
		}*/
		JToolbarHelper::divider();
		JToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}

	protected function getSortFields() {
		return array(
			'a.ordering'		=> JText::_('JGRID_HEADING_ORDERING'),
			'a.title' 			=> JText::_($this->t['l'] . '_TITLE'),
			'a.published' 		=> JText::_($this->t['l'] . '_PUBLISHED'),
			'a.id' 				=> JText::_('JGRID_HEADING_ID')
		);
	}
}
?>
