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

class PhocaCartCpViewPhocaCartCp extends JViewLegacy
{
	protected $t;
	
	function display($tpl = null) {
		
		$this->t	= PhocaCartUtils::setVars();
		$this->views= array(
		'items'			=> $this->t['l'] . '_PRODUCTS',
		'categories'	=> $this->t['l'] . '_CATEGORIES',
		'specifications'=> $this->t['l'] . '_SPECIFICATIONS',
		'manufacturers'	=> $this->t['l'] . '_MANUFACTURERS',
		'orders'		=> $this->t['l'] . '_ORDERS',
		'statuses'		=> $this->t['l'] . '_ORDER_STATUSES',
		'stockstatuses'	=> $this->t['l'] . '_STOCK_STATUSES',
		'shippings'		=> $this->t['l'] . '_SHIPPING',
		'countries'		=> $this->t['l'] . '_COUNTRIES',
		'regions'		=> $this->t['l'] . '_REGIONS',
		'payments'		=> $this->t['l'] . '_PAYMENT',
		'currencies'	=> $this->t['l'] . '_CURRENCIES',
		'taxes'			=> $this->t['l'] . '_TAXES',
		'users'			=> $this->t['l'] . '_USERS',
		'formfields'	=> $this->t['l'] . '_FORM_FIELDS',
		'reviews'		=> $this->t['l'] . '_REVIEWS',
		//'ratings'		=> $this->t['l'] . '_RATINGS',
		//'vouchers'		=> $this->t['l'] . '_VOUCHERS',
		'coupons'		=> $this->t['l'] . '_COUPONS',
		'downloads'		=> $this->t['l'] . '_DOWNLOADS',
		'tags'			=> $this->t['l'] . '_TAGS',
		'statistics'	=> $this->t['l'] . '_STATISTICS',
		'logs'			=> $this->t['l'] . '_SYSTEM_LOG',
		'info'			=> $this->t['l'] . '_INFO'
		);
		
		JHTML::stylesheet( $this->t['s'] );
		JHTML::_('behavior.tooltip');
		$this->t['version'] = PhocaCartUtils::getPhocaVersion('com_phocacart');

		$this->addToolbar();
		parent::display($tpl);
		
	}
	
	protected function addToolbar() {
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'phocacartcp.php';

		$state	= $this->get('State');
		$canDo	= PhocaCartCpHelper::getActions();
		JToolBarHelper::title( JText::_( 'COM_PHOCACART_PC_CONTROL_PANEL' ), 'home-2 cpanel' );
		
		// This button is unnecessary but it is displayed because Joomla! design bug
		$bar = JToolBar::getInstance( 'toolbar' );
		$dhtml = '<a href="index.php?option=com_phocacart" class="btn btn-small"><i class="icon-home-2" title="'.JText::_('COM_PHOCACART_CONTROL_PANEL').'"></i> '.JText::_('COM_PHOCACART_CONTROL_PANEL').'</a>';
		$bar->appendButton('Custom', $dhtml);
		
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_phocacart');
			JToolBarHelper::divider();
		}
		
		JToolBarHelper::help( 'screen.phocacart', true );
	}
}
?>