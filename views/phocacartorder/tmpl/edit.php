<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');


$class		= $this->t['n'] . 'RenderAdminview';
$r 			=  new $class();
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == '<?php echo $this->t['task'] ?>.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
	else {
		Joomla.renderMessages({"error": ["<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true);?>"]});
	}
}
</script><?php
echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div  class="col-xs-12 col-sm-10 col-md-10 form-horizontal">';
$tabs = array (
'order' 			=> JText::_($this->t['l'].'_ORDER_OPTIONS'),
'billingaddress' 	=> JText::_($this->t['l'].'_BILLING_ADDRESS'),
'shippingaddress' 	=> JText::_($this->t['l'].'_SHIPPING_ADDRESS'),
'tracking' 			=> JText::_($this->t['l'].'_SHIPMENT_TRACKING_OPTIONS'),
'products' 			=> JText::_($this->t['l'].'_ORDERED_PRODUCTS'),
'download' 			=> JText::_($this->t['l'].'_DOWNLOAD_LINKS'),
'orderlink' 		=> JText::_($this->t['l'].'_ORDER_LINK'),
'billing' 			=> JText::_($this->t['l'].'_BILLING'));

echo $r->navigation($tabs);

echo '<div id="phAdminEdit" class="tab-content">'. "\n";

echo '<div class="tab-pane active" id="order">'."\n"; 

echo $r->itemText(PhocacartOrder::getOrderNumber($this->itemcommon->id, $this->itemcommon->date, $this->itemcommon->order_number), JText::_('COM_PHOCACART_ORDER_NUMBER'));


$user = $this->itemcommon->user_name;
if ($this->itemcommon->user_username != '') {
	$user .= ' <small>('.$this->itemcommon->user_username.')</small>';
}
if ($user != '') {
	echo $r->itemText($user, JText::_('COM_PHOCACART_USER'));
} else {
	echo $r->itemText('<span class="label label-info">'.JText::_('COM_PHOCACART_GUEST').'</span>', JText::_('COM_PHOCACART_USER'));
}

if (isset($this->itemcommon->vendor_name) && $this->itemcommon->vendor_name != '') {
	$vendor = $this->itemcommon->vendor_name;
	if ($this->itemcommon->vendor_username != '') {
		$vendor .= ' <small>('.$this->itemcommon->vendor_username.')</small>';
	}
	echo $r->itemText($vendor, JText::_('COM_PHOCACART_VENDOR'));
}
if (isset($this->itemcommon->section_name) && $this->itemcommon->section_name != '') {
	echo $r->itemText($this->itemcommon->section_name, JText::_('COM_PHOCACART_SECTION'));
}
if (isset($this->itemcommon->unit_name) && $this->itemcommon->unit_name != '') {
	echo $r->itemText($this->itemcommon->unit_name, JText::_('COM_PHOCACART_UNIT'));
}
if (isset($this->itemcommon->ticket_id) && $this->itemcommon->ticket_id != '') {
	echo $r->itemText($this->itemcommon->ticket_id, JText::_('COM_PHOCACART_TICKET'));
}


echo $r->itemText($this->itemcommon->ip, JText::_('COM_PHOCACART_USER_IP'));
echo $r->itemText($this->itemcommon->user_agent, JText::_('COM_PHOCACART_USER_AGENT'));
echo $r->itemText(JHtml::date($this->itemcommon->date, JText::_('DATE_FORMAT_LC2')), JText::_('COM_PHOCACART_DATE'));
if ($this->itemcommon->currencytitle != '') {
	echo $r->itemText($this->itemcommon->currencytitle, JText::_('COM_PHOCACART_CURRENCY'));
}

if ($this->itemcommon->discounttitle != '') {
	echo $r->itemText($this->itemcommon->discounttitle, JText::_('COM_PHOCACART_CART_DISCOUNT'));
}
if ($this->itemcommon->coupontitle != '') {	
	echo $r->itemText($this->itemcommon->coupontitle, JText::_('COM_PHOCACART_COUPON'));
}
if ($this->itemcommon->shippingtitle != '') {
	echo $r->itemText($this->itemcommon->shippingtitle, JText::_('COM_PHOCACART_SHIPPING_METHOD'));
}
if ($this->itemcommon->paymenttitle != '') {
	echo $r->itemText($this->itemcommon->paymenttitle, JText::_('COM_PHOCACART_PAYMENT_METHOD'));
}

$formArray = array ('id', 'status_id', 'order_token', 'comment');
echo $r->group($this->form, $formArray);
echo '</div>';

$data = PhocacartUser::getAddressDataForm($this->formbas, $this->fieldsbas['array'], $this->u, '_phb', '_phs');

echo '<div class="tab-pane" id="billingaddress">'."\n"; 
echo $data['b'];
echo '</div>';

echo '<div class="tab-pane" id="shippingaddress">'."\n"; 
echo $data['s'];
echo '</div>';

echo '<div class="tab-pane" id="tracking">'."\n"; 

if ($this->itemcommon->shippingtrackinglink != '') {
	PhocacartRenderJs::renderJsAddTrackingCode('jform_tracking_number', 'tracking-link');
	echo $r->itemText($this->itemcommon->shippingtrackinglink, JText::_('COM_PHOCACART_TRACKING_LINK'), 'tracking-link');
}

$formArray = array ('tracking_number', 'tracking_link_custom', 'tracking_date_shipped', 'tracking_description_custom');
echo $r->group($this->form, $formArray);

if ($this->itemcommon->shippingtrackingdescription != '') {
	echo $r->itemText($this->itemcommon->shippingtrackingdescription, JText::_('COM_PHOCACART_TRACKING_DESCRIPTION'));
}

echo '</div>';


echo '<div class="tab-pane" id="products">'."\n"; 


echo '<table class="ph-order-products" id="phAdminEditProducts">';
if (!empty($this->itemproducts)) {
	echo '<tr>';
	echo '<th>'.JText::_('COM_PHOCACART_TITLE').'</th>';
	echo '<th>'.JText::_('COM_PHOCACART_QUANTITY').'</th>';
	echo '<th>'.JText::_('COM_PHOCACART_PRICE_EXCL_TAX').'</th>';
	echo '<th>'.JText::_('COM_PHOCACART_TAX').'</th>';
	echo '<th>'.JText::_('COM_PHOCACART_PRICE_INCL_TAX').'</td>';
	echo '<th>'.JText::_('COM_PHOCACART_PUBLISHED').'</td>';
	echo '<th>'.JText::_('COM_PHOCACART_AMOUNT').'</td>';
	echo '</tr>';


	foreach($this->itemproducts as $k => $v) {
		echo '<tr>';
		echo '<td>'.$r->itemCalc($v->id, 'title', $v->title, 'pform', 2).'</td>';
		echo '<td>'.$r->itemCalc($v->id, 'quantity', $v->quantity, 'pform', 0).'</td>';
		echo '<td>'.$r->itemCalc($v->id, 'netto', PhocacartPrice::cleanPrice($v->netto)).'</td>';
		echo '<td>'.$r->itemCalc($v->id, 'tax', PhocacartPrice::cleanPrice($v->tax)).'</td>';
		echo '<td>'.$r->itemCalc($v->id, 'brutto', PhocacartPrice::cleanPrice($v->brutto)).'</td>';
		echo '<td align="center">'.$r->itemCalcCheckBox($v->id, 'published', $v->published).'</td>';
		echo '<td class="ph-col-add-cur">( '. $this->pr->getPriceFormat($v->brutto).' )</td>';
		echo '</tr>';
		

		if (!empty($this->itemproductdiscounts[$v->product_id_key])) {
			foreach($this->itemproductdiscounts[$v->product_id_key] as $k3 => $v3) {

				
				echo '<tr>';
				//echo '<td></td>';
				echo '<td colspan="2" align="right">'.$v3->title.': </td>';
				echo '<td>'.$r->itemCalc($v3->id, 'netto', PhocacartPrice::cleanPrice($v3->netto), 'dform').'</td>';
				echo '<td>'.$r->itemCalc($v3->id, 'tax', PhocacartPrice::cleanPrice($v3->tax), 'dform').'</td>';
				echo '<td>'.$r->itemCalc($v3->id, 'brutto', PhocacartPrice::cleanPrice($v3->brutto), 'dform').'</td>';
				echo '<td align="center">'.$r->itemCalcCheckBox($v3->id, 'published', $v3->published, 'dform').'</td>';
				echo '<td class="ph-col-add-cur">( '.$this->pr->getPriceFormat($v3->brutto).' )</td>';
				echo '</tr>';

			}

		}
		
		/*if ($v->dnetto != '' || $v->dbrutto != '' || $v->dtax != '') {
			echo '<tr>';
			echo '<td colspan="2" align="right">'.JText::_('COM_PHOCACART_PRICE_AFTER_DISCOUNT').': </td>';
			//echo '<td></td>';
			echo '<td>'.$r->itemCalc($v->id, 'dnetto', PhocacartPrice::cleanPrice($v->dnetto)).'</td>';
			echo '<td>'.$r->itemCalc($v->id, 'dtax', PhocacartPrice::cleanPrice($v->dtax)).'</td>';
			echo '<td>'.$r->itemCalc($v->id, 'dbrutto', PhocacartPrice::cleanPrice($v->dbrutto)).'</td>';
			echo '<td align="center"></td>';
			echo '<td class="ph-col-add-cur">( '.$this->pr->getPriceFormat($v->dbrutto).' )</td>';
			echo '</tr>';
		}*/
		
		if (!empty($v->attributes)) {
			foreach ($v->attributes as $k2 => $v2) {
				echo '<tr>';
				echo '<td align="left">';
				
				echo JText::_('COM_PHOCACART_ATTRIBUTES').': <br />';
				echo $r->itemCalc($v2->id, 'attribute_title', $v2->attribute_title, 'aform', 1).' ';
				echo ''.$r->itemCalc($v2->id, 'option_title', $v2->option_title, 'aform', 1);
				
				echo ''.$r->itemCalc($v2->id, 'option_value', htmlspecialchars(urldecode($v2->option_value)), 'aform', 1);
				
				echo '</td>';
				
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '</tr>';
			}
		
		}
	}
}


$warningCurrency = 0;
if (!empty($this->itemtotal)) {
echo '<tr><td class="ph-order-products-hr" colspan="7">&nbsp;</td></tr>';
echo '<tr><td class="" colspan="7">&nbsp;</td></tr>';
	foreach($this->itemtotal as $k => $v) {
		
		
		echo '<tr class="'.$class.'">';
		echo '<td></td>';
		echo '<td colspan="2">'.$r->itemCalc($v->id, 'title', $v->title, 'tform', 2). '</td>';
		
		
		$typeTxt 	= '';
		$pos 		= strpos($v->type, 'brutto');
		if ($pos !== false) {
		
			$typeTxt = JText::_('COM_PHOCACART_INCL_TAX_SUFFIX');
		}
		$pos2 		= strpos($v->type, 'netto');
		if ($pos2 !== false) {
			$typeTxt = JText::_('COM_PHOCACART_EXCL_TAX_SUFFIX');
		}
	
		echo '<td class="ph-col-add-suffix">'.$typeTxt.'</td>';
		echo '<td>'.$r->itemCalc($v->id, 'amount', PhocacartPrice::cleanPrice($v->amount), 'tform').'</td>';
		echo '<td align="center">'.$r->itemCalcCheckBox($v->id, 'published', $v->published, 'tform').'</td>';
		echo '<td class="ph-col-add-cur">( '.$this->pr->getPriceFormat($v->amount).' )</td>';
		echo '</tr>';
		
		
		// ROUNDING CURRENCY
		if ($v->type == 'rounding' && $v->amount_currency > 0 && $v->amount_currency != $v->amount) {
			$warningCurrency = 1;
			echo '<tr class="ph-currency-row">';
			echo '<td></td>';
			echo '<td colspan="2">'.$r->itemCalc($v->id, 'title', $v->title . ' ('.JText::_('COM_PHOCACART_CURRENCY').')', 'tform', 2). '</td>';
			echo '<td class="ph-col-add-suffix">'.$typeTxt.'</td>';
			echo '<td>'.$r->itemCalc($v->id, 'amount', PhocacartPrice::cleanPrice($v->amount_currency), 'tform').'</td>';
			echo '<td align="center"></td>';
			echo '<td class="ph-col-add-cur"></td>';
			echo '</tr>';
		}
		
		// BRUTTO CURRENCY
		if ($v->type == 'brutto' && $v->amount_currency > 0 && $v->amount_currency != $v->amount) {
			$warningCurrency = 1;
			echo '<tr class="ph-currency-row">';
			echo '<td></td>';
			echo '<td colspan="2">'.$r->itemCalc($v->id, 'title', $v->title . ' ('.JText::_('COM_PHOCACART_CURRENCY').')', 'tform', 2). '</td>';
			echo '<td class="ph-col-add-suffix">'.$typeTxt.'</td>';
			echo '<td>'.$r->itemCalc($v->id, 'amount', PhocacartPrice::cleanPrice($v->amount_currency), 'tform').'</td>';
			echo '<td align="center"></td>';
			echo '<td class="ph-col-add-cur"></td>';
			echo '</tr>';
		}
	}
}


echo '</table>';

if ($warningCurrency == 1) {
	echo '<div>&nbsp;</div>';
	echo '<div class="alert alert-warning">';
	
	echo '<span class="ph-currency-row">&nbsp;&nbsp;&nbsp;</span> '.JText::_('COM_PHOCACART_ROUNDING_CURRENCY_NOT_STORED_IN_DEFAULT_CURRENCY_BUT_ORDER_CURRENCY');
	echo '</div>';

}

echo '</div>';



echo '<div class="tab-pane" id="download">'."\n"; 

if (isset($this->itemcommon->order_token)) {
	if (!empty($this->itemproducts)) {
		/*phocacart import('phocacart.path.route');*/
		echo '<table class="ph-table-download-links">';
		echo '<tr><th class="title">'.JText::_('COM_PHOCACART_PRODUCT').'</th>';
		echo '<th class="link">'.JText::_('COM_PHOCACART_DOWNLOAD_LINK').'</th></tr>';
		foreach($this->itemproducts as $k => $v) {
			if (isset($v->download_token)) {
				echo '<tr><td>'.$v->title.'</td>';
				//$dLink = JRoute::_(PhocacartRoute::getDownloadRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token)
				//. '&d='.htmlspecialchars($v->download_token));
				$link = PhocacartRoute::getDownloadRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token)
				. '&d='.htmlspecialchars($v->download_token);
				
				$dLink = PhocacartPath::getRightPathLink($link);
				
				echo '<td><input type="text" name="" value="'.$dLink.'" style="width: 90%;" /></td></tr>';
			}
		}
		echo '</table>';
	}
}
echo '</div>';

echo '<div class="tab-pane" id="orderlink">'."\n"; 

if (isset($this->itemcommon->order_token)) {
	if (!empty($this->itemproducts)) {
		/*phocacart import('phocacart.path.route');*/
		echo '<table class="ph-table-order-link">';
		echo '<tr><th class="link">'.JText::_('COM_PHOCACART_ORDER_LINK').'</th></tr>';
		
		//$dLink = JRoute::_(PhocacartRoute::getDownloadRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token)
				//. '&d='.htmlspecialchars($v->download_token));
		$link = PhocacartRoute::getOrdersRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token);
		$oLink = PhocacartPath::getRightPathLink($link);
				
		echo '<tr><td><input type="text" name="" value="'.$oLink.'" style="width: 90%;" /></td></tr>';
		echo '</table>';
	}
}
echo '</div>';








echo '<div class="tab-pane" id="billing">'."\n"; 
$formArray = array ('order_number', 'receipt_number', 'invoice_number', 'invoice_prn', 'invoice_date', 'invoice_due_date', 'invoice_spec_top_desc', 'invoice_spec_middle_desc', 'invoice_spec_bottom_desc');
echo $r->group($this->form, $formArray);
echo '</div>';


/*
echo '<div class="tab-pane" id="publishing">'."\n"; 
foreach($this->form->getFieldset('publish') as $field) {
	echo '<div class="control-group">';
	if (!$field->hidden) {
		echo '<div class="control-label">'.$field->label.'</div>';
	}
	echo '<div class="controls">';
	echo $field->input;
	echo '</div></div>';
}
echo '</div>';*/
				
echo '</div>';//end tab content
echo '</div>';//end col-xs-12 col-sm-10 col-md-10
// Second Column
echo '<div class="col-xs-12 col-sm-2 col-md-2">';
echo '<div class="alert alert-error alert-danger">'.JText::_('COM_PHOCACART_WARNING_EDIT_ORDER').'</div>';
echo '</div>';//end span2
echo $r->formInputs();
echo $r->endForm();
?>

