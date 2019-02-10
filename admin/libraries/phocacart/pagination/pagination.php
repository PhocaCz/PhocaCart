<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.html.pagination');
class PhocacartPagination extends JPagination
{
	function getLimitBox() {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$pos			= PhocacartPos::isPos();
		if ($pos == 1) {
			$pagination = $paramsC->get( 'pos_pagination', '6,12,24,36,48,60' );
		} else {
			$pagination = $paramsC->get( 'item_pagination', '5,10,15,20,50' );
		}
		
		$paginationArray	= explode( ',', $pagination );
		
		// Initialize variables
		$limits = array ();

		foreach ($paginationArray as $paginationValue) {
			$limits[] = JHtml::_('select.option', $paginationValue);
		}
		$limits[] = JHtml::_('select.option', '0', JText::_('COM_PHOCACART_ALL'));

		$selected = $this->viewall ? 0 : $this->limit;

		// Build the select list
		if ($app->isClient('administrator')) {
			$html = JHtml::_('select.genericlist',  $limits, 'limit', 'class="inputbox" size="1" onchange="submitform();"', 'value', 'text', $selected);
		} else {
			$html = JHtml::_('select.genericlist',  $limits, 'limit', 'class="inputbox" size="1" onchange="phEventChangeFormPagination(this.form, this)"', 'value', 'text', $selected);
		}
		return $html;
	}
	
	//public static function getMaximumLimit(int $limit) : int {
	public static function getMaximumLimit($limit) {
		
		$paramsC 	= PhocacartUtils::getComponentParameters();
		$pos		= PhocacartPos::isPos();
		if ($pos == 1) {
			$item_pagination_limit	= 0;
			$pagination 			= $paramsC->get( 'pos_pagination', '6,12,24,36,48,60' );
		} else {
			$item_pagination_limit	= $paramsC->get( 'item_pagination_limit', 0);
			$pagination 			= $paramsC->get( 'item_pagination', '5,10,15,20,50' );
		}
		
		
		$l = $limit;
		if ((int)$item_pagination_limit == 1) {
			
			$paginationArray		= explode( ',', $pagination );
			$maxPagination			= max($paginationArray);
			
			if ((int)$limit == 0 && (int)$maxPagination > 0) {
				// Pagination limit not set in frontend (all) but maximum pagination limit set
				$l = (int)$maxPagination;
			} else if ((int)$limit > 0 && (int)$maxPagination > 0 && $limit > (int)$maxPagination) {
				// Pagination limit is set in frontend (e.g. 20) but maximum pagination limit is smaller (e.g. 10)
				$l = (int)$maxPagination;
			} /*else {
				// Maximum pagination limit not set, so used the standard one - set in frontend by visitor
				$l = $limit;
			}*/
		}
		return $l;
	}
	
}
?>