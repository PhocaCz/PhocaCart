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
		'payments'		=> array($this->t['l'] . '_PAYMENT', 'credit-card', '#4f9ce2'),
		'currencies'	=> array($this->t['l'] . '_CURRENCIES', 'eur', '#dca300'),
		'taxes'			=> array($this->t['l'] . '_TAXES', 'calendar', '#dd5500'),
		'users'			=> array($this->t['l'] . '_USERS', 'user', '#7faa7f'),
		'formfields'	=> array($this->t['l'] . '_FORM_FIELDS', 'list-alt', '#ffde00'),
		'reviews'		=> array($this->t['l'] . '_REVIEWS', 'comment', '#399ed0'),
		//'ratings'		=> array($this->t['l'] . '_RATINGS', 'xxx', '#ffde00'),
		//'vouchers'	=> array($this->t['l'] . '_VOUCHERS', 'xxx', '#ffde00'),
		'coupons'		=> array($this->t['l'] . '_COUPONS', 'gift', '#FF6685'),
		'downloads'		=> array($this->t['l'] . '_DOWNLOADS', 'download-alt', '#33af49'),
		'tags'			=> array($this->t['l'] . '_TAGS', 'tag', '#CC0033'),
		'feeds'			=> array($this->t['l'] . '_XML_FEEDS', 'bullhorn', '#ffb300'),
		'wishlists'		=> array($this->t['l'] . '_WISH_LISTS', 'heart', '#EA7C7C'),
		'questions'		=> array($this->t['l'] . '_QUESTIONS', 'question-sign', '#9900CC'),
		'statistics'	=> array($this->t['l'] . '_STATISTICS', 'stats', '#c1756d'),
		'logs'			=> array($this->t['l'] . '_SYSTEM_LOG', 'list', '#c0c0c0'),
		'info'			=> array($this->t['l'] . '_INFO', 'info-sign', '#3378cc')
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
		//'ratings'		=> array($this->t['l'] . '_RATINGS', 'xxx', '#ffde00'),
		//'vouchers'	=> array($this->t['l'] . '_VOUCHERS', 'xxx', '#ffde00'),
		'coupons'		=> array($this->t['l'] . '_COUPONS', 'gift', '#f86cb5'),
		'downloads'		=> array($this->t['l'] . '_DOWNLOADS', 'download-alt', '#f86cd9'),
		'tags'			=> array($this->t['l'] . '_TAGS', 'tag', '#d673dd'),
		'statistics'	=> array($this->t['l'] . '_STATISTICS', 'stats', '#f4adf9'),
		'logs'			=> array($this->t['l'] . '_SYSTEM_LOG', 'list', '#4f70a6'),
		'info'			=> array($this->t['l'] . '_INFO', 'info-sign', '#7fadf8')
		);
		*/
		JHTML::stylesheet( $this->t['s'] );
		JHTML::_('behavior.tooltip');
		$this->t['version'] = PhocaCartUtils::getPhocaVersion('com_phocacart');
		
		
		JHtml::_('jquery.framework', false);
		$document					= JFactory::getDocument();
		JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.glyphicons.min.css' );

		$this->addToolbar();
		parent::display($tpl);
		
	}
	
	protected function addToolbar() {
		require_once JPATH_COMPONENT.'/helpers/phocacartcp.php';

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