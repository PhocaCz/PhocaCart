<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$price = $this->t['price'];

echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_ORDERS_SALES').'</div>';
if (!empty($this->items)) {

	echo '<div class="row ph-pos-customer-row-header">';
		echo '<div class="row-item col-sx-12 col-sm-2 col-md-2">'.JText::_('COM_PHOCACART_ORDER').'</div>';
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">'.JText::_('COM_PHOCACART_VENDOR').'</div>';
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">'.JText::_('COM_PHOCACART_TOTAL').'</div>';
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">'.JText::_('COM_PHOCACART_CUSTOMER').'</div>';
		echo '<div class="row-item col-sx-12 col-sm-2 col-md-2">'.JText::_('COM_PHOCACART_DATE').'</div>';
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">'.JText::_('COM_PHOCACART_SECTION').'</div>';
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">'.JText::_('COM_PHOCACART_UNIT').'</div>';
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">'.JText::_('COM_PHOCACART_TICKET').'</div>';
		echo '<div class="row-item col-sx-12 col-sm-2 col-md-2"></div>';
	echo '</div>';
	
	foreach ($this->items as $v) {
		echo '<div class="row ph-pos-customer-row">';
		
		echo '<div class="row-item col-sx-12 col-sm-2 col-md-2">';
		echo '<div class="ph-pos-customer-name">'.PhocacartOrder::getOrderNumber($v->id, $v->date, $v->order_number).'</div>';
		echo '</div>';
		
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">';
		echo '<div class="ph-pos-vendor-name">'.$v->vendor_title.'</div>';
		echo '</div>';
		
		$price->setCurrency($v->currency_id, $v->id);
		
		$amount = (isset($v->total_amount_currency) && $v->total_amount_currency > 0) ? $price->getPriceFormat($v->total_amount_currency, 0, 1) : $price->getPriceFormat($v->total_amount);
		
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">';
		echo '<div class="ph-pos-total">'.$amount.'</div>';
		echo '</div>';
		
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">';
		echo '<div class="ph-pos-customer-name">'.$v->user_title.'</div>';
		echo '</div>';
		
		
		echo '<div class="row-item col-sx-12 col-sm-2 col-md-2">';
		echo '<div class="ph-pos-customer-name">'.$v->date.'</div>';
		echo '</div>';
		
		
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">';
		echo '<div class="ph-pos-section-name">';
		$title = $v->section_id;
		if (isset($v->section_title)) {
			$title = $v->section_title;
		}
		echo '<span class="label label-primary">'.$title.'</span>';
		echo '</div>';
		echo '</div>';
		
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">';
		echo '<div class="ph-pos-unit-name">';
		$title = $v->unit_id;
		if (isset($v->unit_title)) {
			$title = $v->unit_title;
		}
		echo '<span class="label label-info">'.$title.'</span>';
		echo '</div>';
		echo '</div>';
		
		
		echo '<div class="row-item col-sx-12 col-sm-1 col-md-1">';
		echo '<div class="ph-pos-ticket-name">';
		echo '<span class="label label-warning">'.$v->ticket_id.'</span>';
		echo '</div>';
		echo '</div>';
		
		echo '<div class="row-item ph-pos-customer-action col-sx-12 col-sm-2 col-md-2">';
		echo '<form class="form-inline" action="'.$this->t['linkpos'].'" method="post">';
		echo '<input type="hidden" name="page" value="main.content.order">';
		echo '<input type="hidden" name="id" value="'.(int)$v->id.'">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
		echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
		echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';
		echo JHtml::_('form.token');
		echo '<button class="btn btn-success loadMainContent">'.JText::_('COM_PHOCACART_VIEW').'</button>';
		echo '</form>';
		echo '</div>';

		echo '</div>';// end row
	}
	
} else {
	echo '<div class="ph-pos-no-items">'.JText::_('COM_PHOCACART_NO_ORDER_SALE_FOUND_FOR_SELECTED_DATE').'</div>';
}

echo $this->loadTemplate('pagination');

?>