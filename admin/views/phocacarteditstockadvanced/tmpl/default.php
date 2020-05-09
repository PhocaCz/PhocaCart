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


$link		= JRoute::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditstockadvanced&tmpl=component&id='.(int)$this->id);


echo '<div class="ph-edit-stock-advanced-box">';

echo '<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button>'
	. '<ul><li>'.JText::_('COM_PHOCACART_TO_SEE_ALL_ATTRIBUTES_LISTED_CLOSE_WINDOW_SAVE_THE_PRODUCT_FIRST') . '</li>'
	. '<li>'. JText::_('COM_PHOCACART_CHECK_LIST_EVERY_TIME_ATTRIBUTES_CHANGE').'</li></ul></div>';

if (!empty($this->t['product'])) {

	echo '<div class="ph-attribute-box">';

	echo '<form action="'.$link.'" method="post">';


	if (!empty($this->t['combinations'])) {
		ksort($this->t['combinations']);
		echo '<table class="ph-attribute-option-box">';

		echo '<tr>';
		echo '<th>'.JText::_('COM_PHOCACART_TITLE').'</th>';
		echo '<th>'.JText::_('COM_PHOCACART_ATTRIBUTES').'</th>';
		//echo '<th>'.JText::_('COM_PHOCACART_PRODUCT_KEY').'</th>';
		echo '<th>'.JText::_('COM_PHOCACART_IN_STOCK').'</th>';
		echo '</tr>';


		foreach($this->t['combinations'] as $k => $v) {

			echo '<tr>';
			echo '<td>'.$v['product_title'].'</td>';
			echo '<td>'.$v['title'].'</td>';
			//echo '<td><input type="text" class="input-large" name="jform['.$v['product_key'].'][product_key]" value="'.$v['product_key'].'" /></td>';

			// Set value from database
			$stock = 0;
			if (isset($this->t['combinations_stock'][$v['product_key']]['stock'])) {
				$stock = $this->t['combinations_stock'][$v['product_key']]['stock'];
			}
			echo '<td><input type="text" class="input-mini" name="jform['.$v['product_key'].'][stock]" value="'.$stock.'" />';
			echo '<input type="hidden" name="jform['.$v['product_key'].'][product_key]" value="'.$v['product_key'].'" />';
			echo '<input type="hidden" name="jform['.$v['product_key'].'][product_id]" value="'.$v['product_id'].'" />';
			echo '<input type="hidden" name="jform['.$v['product_key'].'][attributes]" value="'.PhocacartProduct::getProductKey($v['product_id'], $v['attributes'], 0).'" />';
			echo '</td>';
			echo '</tr>';

		}

		echo '<tr><td colspan="3"></td></tr>';

		echo '<tr>';
		echo '<td></td>';
		echo '<td></td>';

		echo '<td>';
		echo '<input type="hidden" name="id" value="'.(int)$this->t['product']->id.'">';
		echo '<input type="hidden" name="task" value="phocacarteditstockadvanced.save">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<button class="btn btn-success btn-sm ph-btn"><span class="icon-ok ph-icon-white"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
		echo JHtml::_('form.token');


		echo '</tr>';

		echo '</table>';
	}
}

?>
