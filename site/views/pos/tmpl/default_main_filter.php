<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div class="ph-pos-message-box"></div>';

echo '<div class="ph-pos-filter-box">';


// DATE FILTER (ORDERS)
echo '<div class="ph-pos-date-order-box" id="phPosDateOrdersBox">';
echo '<form id="phPosDateOrdersForm" class="form-inline" action="'.$this->t['linkpos'].'" method="post">';

Joomla\CMS\HTML\HTMLHelper::_('script', 'system/html5fallback.js', false, true);

// DATE FROM
$name		= "date";
$id			= 'phPosDateOrders';
$format 	= '%Y-%m-%d';
$attributes = array(
			'onChange' => 'jQuery(\'#phPosDateOrdersForm\').submit()',
			"showTime" => false,
			"todayBtn" => true,
			"weekNumbers" => false,
			"fillTable" => true,
			"singleHeader" => false
);
$valueFrom 	= $this->escape($this->state->get('filter.date', PhocacartDate::getCurrentDate()));


$calendar = Joomla\CMS\HTML\HTMLHelper::_('calendar', $valueFrom, $name, $id, $format, $attributes);
$calendarIcon = $this->s['i']['calendar'];
$calendar = str_replace('icon-calendar', $calendarIcon .' icon-calendar', $calendar);

echo '<div class="ph-inline-param">'. $calendar.'</div>';

echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="format" value="raw" />';
echo '<input type="hidden" name="page" value="main.content.orders" />';
echo '<input type="hidden" name="ticketid" value="'.$this->t['ticket']->id.'" />';
echo '<input type="hidden" name="unitid" value="'.$this->t['unit']->id.'" />';
echo '<input type="hidden" name="sectionid" value="'.$this->t['section']->id.'" />';
echo Joomla\CMS\HTML\HTMLHelper::_('form.token');
echo '</form>';
echo '</div>';


// SKU PRODUCT (ajax based on .phItemCartBoxForm)
echo '<div class="ph-pos-sku-product-box" id="phPosSkuProductBox">';
echo '<div class="inner-addon right-addon">';

echo ' <i class="'.$this->s['i']['barcode'].'"></i>';

echo '<form id="phPosSkuProductForm" class="phItemCartBoxForm phjAddToCart phjPos phjAddToCartVPosPSku form-inline" action="'.$this->t['linkpos'].'" method="post">';

echo '<input type="hidden" name="quantity" value="1">';
echo '<input type="hidden" name="task" value="pos.add">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="page" value="'.$this->t['page'].'" />';
echo '<input type="hidden" name="ticketid" value="'.$this->t['ticket']->id.'" />';
echo '<input type="hidden" name="unitid" value="'.$this->t['unit']->id.'" />';
echo '<input type="hidden" name="sectionid" value="'.$this->t['section']->id.'" />';
//echo '<input type="hidden" name="return" value="'.$this->t['mainboxdatabase64'].'" />';
echo '<input type="'.$this->t['pos_sku_input_type'].'" name="sku" id="phPosSku" value="'.htmlspecialchars($this->t['sku']).'" class="'.$this->s['c']['form-control'].' ph-pos-search" placeholder="'.$this->t['skutypetxt'].' ..." '.$this->t['pos_input_autocomplete_output'].' />';
//echo '<input type="submit" value="submit" />';
echo Joomla\CMS\HTML\HTMLHelper::_('form.token');
echo '</form>';

echo '</div>';
echo '</div>';


// LOYALTY CARD NUMBER (USER) (ajax based on .editMainContent)
echo '<div class="ph-pos-card-user-box" id="phPosCartUserBox">';
echo '<div class="inner-addon right-addon">';

echo ' <i class="'.$this->s['i']['barcode'].'"></i>';

echo '<form id="phPosCardUserForm" class="phjAddToCartVPosPCard form-inline" action="'.$this->t['linkpos'].'" method="post">';

echo '<input type="hidden" name="quantity" value="1">';
echo '<input type="hidden" name="task" value="pos.savecustomer">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="page" value="'.$this->t['page'].'" />';
echo '<input type="hidden" name="ticketid" value="'.$this->t['ticket']->id.'" />';
echo '<input type="hidden" name="unitid" value="'.$this->t['unit']->id.'" />';
echo '<input type="hidden" name="sectionid" value="'.$this->t['section']->id.'" />';
//echo '<input type="hidden" name="return" value="'.$this->t['mainboxdatabase64'].'" />';
echo '<input type="'.$this->t['pos_loyalty_card_number_input_type'].'" name="card" id="phPosCard" value="'.htmlspecialchars($this->t['card']).'" class="'.$this->s['c']['form-control'].' ph-pos-search" placeholder="'.JText::_('COM_PHOCACART_FIELD_LOYALTY_CARD_NUMBER_LABEL').' ..." '.$this->t['pos_input_autocomplete_output'].' />';
//echo '<input type="submit" value="submit" />';
echo Joomla\CMS\HTML\HTMLHelper::_('form.token');
echo '</form>';

echo '</div>';
echo '</div>';


// SEARCH
echo '<div class="ph-pos-search-box" id="phPosSearchBox">';
echo '<div class="inner-addon right-addon">';
echo ' <i class="'.$this->s['i']['search'].'"></i>';
echo '	<input type="text" name="phpossearch" id="phPosSearch" value="'.htmlspecialchars($this->t['search']).'" class="'.$this->s['c']['form-control'].' ph-pos-search" placeholder="'.JText::_('COM_PHOCACART_SEARCH').' ..." '.$this->t['pos_input_autocomplete_output'].' />';
echo '</div>';
echo '</div>';

echo '</div>'; // end ph-pos-filter-box
?>
