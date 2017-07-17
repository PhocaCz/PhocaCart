<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');


$link		= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditproductpricegroup&tmpl=component&id='.(int)$this->id);


echo '<div id="phAdminEditPopup" class="ph-edit-stock-advanced-box">';

echo '<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button>'
	. '<ul><li>'.JText::_('COM_PHOCACART_TO_SEE_ALL_CUSTOMER_GROUPS_LISTED_CLOSE_WINDOW_SAVE_THE_PRODUCT_FIRST') . '</li>'
	. '<li>'. JText::_('COM_PHOCACART_CHECK_LIST_EVERY_TIME_CUSTOMER_GROUPS_CHANGE').'</li>'
	. '<li>'.JText::_('COM_PHOCACART_IF_YOU_SET_ZERO_AS_PRICE_THEN_PRICE_WILL_BE_ZERO').'</li>'
	.'</ul></div>';
	
if (!empty($this->t['product'])) {

	echo '<div class="ph-product-customer-group-box">';
	
	echo '<form action="'.$link.'" method="post">';
	

	if (!empty($this->t['groups'])) {
		
		echo '<table class="ph-product-customer-group-box">';
		
		echo '<tr>';
		echo '<th>'.JText::_('COM_PHOCACART_CUSTOMER_GROUP').'</th>';
		//echo '<th>'.JText::_('COM_PHOCACART_PRODUCT_KEY').'</th>';
		echo '<th>'.JText::_('COM_PHOCACART_PRICE').'</th>';
		echo '</tr>';
	
	

	
		foreach($this->t['groups'] as $k => $v) {
			
			
			echo '<tr>';
			echo '<td>'.JText::_($v['title']).'</td>';
			
			if ($v['type'] == 1) {
				
				// Default 
				echo '<td><input type="text" class="input-small" name="jform['.$v['id'].'][price]" value="'.PhocacartPrice::cleanPrice($this->t['product']->price).'" readonly />';
				echo '<input type="hidden" name="jform['.$v['id'].'][group_id]" value="'.$v['id'].'" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$this->id.'" />';
				
			} else {
				
				// Set value from database
				$price = '';
				if (isset($this->t['product_groups'][$v['id']]['price'])) {
					$price = $this->t['product_groups'][$v['id']]['price'];
					if ($price > 0 || $price == 0) {
						$price = PhocacartPrice::cleanPrice($price);
					}
				}
				echo '<td><input type="text" class="input-small" name="jform['.$v['id'].'][price]" value="'.$price.'" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][group_id]" value="'.$v['id'].'" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$this->id.'" />';
				//echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$v['product_id'].'" />';
				//echo '<input type="hidden" name="jform['.$v['id'].'][attributes]" value="'.serialize($v['attributes']).'" />';
				
				
				
			}
			
			echo '</td>';
			echo '</tr>';
			
		}
		
		echo '<tr><td colspan="2"></td></tr>';
		
		echo '<tr>';
		echo '<td></td>';
		
		echo '<td>';
		echo '<input type="hidden" name="id" value="'.(int)$this->t['product']->id.'">';
		echo '<input type="hidden" name="task" value="phocacarteditproductpricegroup.save">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<button class="btn btn-success btn-sm ph-btn"><span class="icon-ok ph-icon-white"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
		echo JHtml::_('form.token');
		
		
		echo '</tr>';
		
		echo '</table>';
	}
}
	
?>
