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
		$pagination 		= $paramsC->get( 'item_pagination', '5,10,15,20,50,100' );
		$paginationArray	= explode( ',', $pagination );
		
		// Initialize variables
		$limits = array ();

		foreach ($paginationArray as $paginationValue) {
			$limits[] = JHTML::_('select.option', $paginationValue);
		}
		$limits[] = JHTML::_('select.option', '0', JText::_('COM_PHOCACART_ALL'));

		$selected = $this->viewall ? 0 : $this->limit;

		// Build the select list
		if ($app->isAdmin()) {
			$html = JHTML::_('select.genericlist',  $limits, 'limit', 'class="inputbox" size="1" onchange="submitform();"', 'value', 'text', $selected);
		} else {
			$html = JHTML::_('select.genericlist',  $limits, 'limit', 'class="inputbox" size="1" onchange="phEventChangeFormPagination(this.form)"', 'value', 'text', $selected);
		}
		return $html;
	}
	
}
?>