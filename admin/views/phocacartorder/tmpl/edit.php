<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

$r 			=  new PhocacartRenderAdminview();
$js ='
Joomla.submitbutton = function(task) {
	if (task == "'. $this->t['task'] .'.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
		Joomla.submitform(task, document.getElementById("adminForm"));
	} else {
		Joomla.renderMessages({"error": ["'. Text::_('JGLOBAL_VALIDATION_FORM_FAILED', true).'"]});
	}
}
';
Factory::getDocument()->addScriptDeclaration($js);
echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="col-xs-12 col-sm-12 col-md-12 form-horizontal">';
$tabs = array (
'order' 			=> Text::_($this->t['l'].'_ORDER_OPTIONS'),
'billingaddress' 	=> Text::_($this->t['l'].'_BILLING_ADDRESS'),
'shippingaddress' 	=> Text::_($this->t['l'].'_SHIPPING_ADDRESS'),
'tracking' 			=> Text::_($this->t['l'].'_SHIPMENT_TRACKING_OPTIONS'),
'products' 			=> Text::_($this->t['l'].'_ORDERED_PRODUCTS'),
'download' 			=> Text::_($this->t['l'].'_DOWNLOAD_LINKS'),
'orderlink' 		=> Text::_($this->t['l'].'_ORDER_LINK'),
'billing' 			=> Text::_($this->t['l'].'_BILLING'));

echo $r->navigation($tabs);

echo $r->startTabs();

echo $r->startTab('order', $tabs['order'], 'active');

echo $r->itemText(PhocacartOrder::getOrderNumber($this->itemcommon->id, $this->itemcommon->date, $this->itemcommon->order_number), Text::_('COM_PHOCACART_ORDER_NUMBER'), '', 'order_number');

$guest = 0;
$user = $this->itemcommon->user_name;
if ($this->itemcommon->user_username != '') {
	$user .= ' <small>('.$this->itemcommon->user_username.')</small>';
}
if ($user != '') {
	echo $r->itemText($user, Text::_('COM_PHOCACART_USER'), '', 'user');
} else {
	$guest = 1;
	echo $r->itemText('<span class="label label-info badge bg-info">'.Text::_('COM_PHOCACART_GUEST').'</span>', Text::_('COM_PHOCACART_USER'), '', 'guest');
}

if (isset($this->itemcommon->vendor_name) && $this->itemcommon->vendor_name != '') {
	$vendor = $this->itemcommon->vendor_name;
	if ($this->itemcommon->vendor_username != '') {
		$vendor .= ' <small>('.$this->itemcommon->vendor_username.')</small>';
	}
	echo $r->itemText($vendor, Text::_('COM_PHOCACART_VENDOR'), '', 'vendor');
}
if (isset($this->itemcommon->section_name) && $this->itemcommon->section_name != '') {
	echo $r->itemText($this->itemcommon->section_name, Text::_('COM_PHOCACART_SECTION'), '', 'section');
}
if (isset($this->itemcommon->unit_name) && $this->itemcommon->unit_name != '') {
	echo $r->itemText($this->itemcommon->unit_name, Text::_('COM_PHOCACART_UNIT'), '', 'unit');
}
if (isset($this->itemcommon->ticket_id) && $this->itemcommon->ticket_id != '') {
	echo $r->itemText($this->itemcommon->ticket_id, Text::_('COM_PHOCACART_TICKET'), '', 'ticket');
}


echo $r->itemText($this->itemcommon->ip, Text::_('COM_PHOCACART_USER_IP'), '', 'user_ip');
echo $r->itemText($this->itemcommon->user_agent, Text::_('COM_PHOCACART_USER_AGENT'), '', 'user_agent');
echo $r->itemText($this->itemcommon->user_lang, Text::_('COM_PHOCACART_USER_LANGUAGE'), '', 'user_lang');
echo $r->itemText(HTMLHelper::date($this->itemcommon->date, Text::_('DATE_FORMAT_LC2')), Text::_('COM_PHOCACART_DATE'), '', 'date');
if ($this->itemcommon->currencytitle != '') {
	echo $r->itemText($this->itemcommon->currencytitle, Text::_('COM_PHOCACART_CURRENCY'), '', 'currency');
}

if ($this->itemcommon->discounttitle != '') {
	echo $r->itemText($this->itemcommon->discounttitle, Text::_('COM_PHOCACART_CART_DISCOUNT'), '', 'discount');
}
if ($this->itemcommon->coupontitle != '') {
	echo $r->itemText($this->itemcommon->coupontitle, Text::_('COM_PHOCACART_COUPON'), '', 'coupon');
}
if ($this->itemcommon->shippingtitle != '') {
	echo $r->itemText($this->itemcommon->shippingtitle, Text::_('COM_PHOCACART_SHIPPING_METHOD'), '', 'shipping_method');
}



if (isset($this->itemcommon->shipping_id) && (int)$this->itemcommon->shipping_id > 0 && isset($this->itemcommon->params_shipping)) {
	//$shipping       = new PhocacartShipping();
	//$shippingMethod = $shipping->getShippingMethod((int)$this->itemcommon->shipping_id);

	$paramsShipping = json_decode($this->itemcommon->params_shipping, true);

	if (isset($paramsShipping['method']) && $paramsShipping['method'] != '') {
		$results = Dispatcher::dispatch(new Event\Shipping\GetShippingBranchInfoAdminEdit('com_phocacart.phocacartorder', $this->itemcommon, [
			'pluginname' => $paramsShipping['method'],
			'item' => [
				'id' => (int)$this->itemcommon->id,
				'shipping_id' => (int)$this->itemcommon->shipping_id,
			]
		]));

		if (!empty($results)) {
			foreach ($results as $k => $v) {
				if ($v != false && isset($v['content']) && $v['content'] != '') {
					echo $v['content'];
				}
			}
		}

	}
}

if ($this->itemcommon->paymenttitle != '') {
	echo $r->itemText($this->itemcommon->paymenttitle, Text::_('COM_PHOCACART_PAYMENT_METHOD'), '', 'payment_method');
}

$formArray = array ('id', 'status_id', 'order_token', 'comment', 'terms', 'privacy', 'newsletter');
echo $r->group($this->form, $formArray);
echo $r->endTab();

$data = PhocacartUser::getAddressDataForm($this->formbas, $this->fieldsbas['array'], $this->u, '_phb', '_phs', $guest);


echo $r->startTab('billingaddress', $tabs['billingaddress']);
echo $data['b'];


if (isset($this->itemcommon->params_user)) {

	if (!empty($this->itemcommon)) {
		$results = Dispatcher::dispatch(new Event\Tax\GetUserBillingInfoAdminEdit('com_phocacart.phocacartorder', $this->itemcommon));

		if (!empty($results)) {
			foreach ($results as $k => $v) {
				if ($v != false && isset($v['content']) && $v['content'] != '') {
					echo $v['content'];
				}
			}
		}
	}
}



echo $r->endTab();


echo $r->startTab('shippingaddress', $tabs['shippingaddress']);

if ((isset($this->itembas['b']['ba_sa']) && $this->itembas['b']['ba_sa'] == 1) || (isset($this->itembas['s']['ba_sa']) && $this->itembas['s']['ba_sa'] == 1)) {
	echo '<div class="alert alert-error alert-danger">'.Text::_('COM_PHOCACART_WARNING_USER_SET_SHIPPING_ADDRESS_SAME_AS_BILLING_ADDRESS').'</div>';
}
echo $data['s'];
echo $r->endTab();


echo $r->startTab('tracking', $tabs['tracking']);

if ($this->itemcommon->shippingtrackinglink != '') {
	PhocacartRenderJs::renderJsAddTrackingCode('jform_tracking_number', 'tracking-link');
	echo $r->itemText($this->itemcommon->shippingtrackinglink, Text::_('COM_PHOCACART_TRACKING_LINK'), 'tracking-link', 'tracking_link');
}

$formArray = array ('tracking_number', 'tracking_link_custom', 'tracking_date_shipped', 'tracking_description_custom');
echo $r->group($this->form, $formArray);

if ($this->itemcommon->shippingtrackingdescription != '') {
	echo $r->itemText($this->itemcommon->shippingtrackingdescription, Text::_('COM_PHOCACART_TRACKING_DESCRIPTION'), '', 'tracking_description');
}

echo $r->endTab();


echo $r->startTab('products', $tabs['products']);

echo '<table class="ph-order-products" id="phAdminEditProducts">';

echo '<div class="alert alert-error alert-danger">'.Text::_('COM_PHOCACART_WARNING_EDIT_ORDER').'</div>';

if (!empty($this->itemproducts)) {
	echo '<tr>';
	echo '<th>'.Text::_('COM_PHOCACART_TITLE').'</th>';
	echo '<th>'.Text::_('COM_PHOCACART_QUANTITY').'</th>';
	echo '<th>'.Text::_('COM_PHOCACART_PRICE_EXCL_TAX').'</th>';
	echo '<th>'.Text::_('COM_PHOCACART_TAX').'</th>';
	echo '<th>'.Text::_('COM_PHOCACART_PRICE_INCL_TAX').'</td>';
	echo '<th>'.Text::_('COM_PHOCACART_PUBLISHED').'</td>';
	echo '<th>'.Text::_('COM_PHOCACART_AMOUNT').'</td>';
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
			echo '<td colspan="2" align="right">'.Text::_('COM_PHOCACART_PRICE_AFTER_DISCOUNT').': </td>';
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

				echo Text::_('COM_PHOCACART_ATTRIBUTES').': <br />';
				echo $r->itemCalc($v2->id, 'attribute_title', $v2->attribute_title, 'aform', 1).' ';
				echo ''.$r->itemCalc($v2->id, 'option_title', $v2->option_title, 'aform', 1);

				$size = 1;

				if ($v2->type == 10 || $v2->type == 11) {
				    $size  = 3;
                }
				echo ''.$r->itemCalc($v2->id, 'option_value', htmlspecialchars(urldecode($v2->option_value)), 'aform', $size);

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


		echo '<tr class="PhocacartRenderAdminview">';

		// Language Variables
        if ($this->p['order_language_variables'] == 1) {
            echo '<td colspan="3">'.$r->itemCalc($v->id, 'title_lang', $v->title_lang, 'tform', 1). '';
            echo ''.$r->itemCalc($v->id, 'title_lang_suffix', $v->title_lang_suffix, 'tform', 1). '';
            echo ''.$r->itemCalc($v->id, 'title_lang_suffix2', $v->title_lang_suffix2, 'tform', 0). '<br>';
            echo '<span class="ph-col-title-small">'.PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' '), 1 => array($v->title_lang_suffix2, ' '))).'</span></td>';

        } else {
            echo '<td></td>';
            echo '<td colspan="2">'.$r->itemCalc($v->id, 'title', $v->title, 'tform', 2). '</td>';
        }




		$typeTxt 	= '';
		$pos 		= strpos($v->type, 'brutto');
		if ($pos !== false) {

			$typeTxt = Text::_('COM_PHOCACART_INCL_TAX_SUFFIX');
		}
		$pos2 		= strpos($v->type, 'netto');
		if ($pos2 !== false) {
			$typeTxt = Text::_('COM_PHOCACART_EXCL_TAX_SUFFIX');
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
			echo '<td colspan="2">'.$r->itemCalc($v->id, 'title', $v->title . ' ('.Text::_('COM_PHOCACART_CURRENCY').')', 'tform', 2). '</td>';
			echo '<td class="ph-col-add-suffix">'.$typeTxt.'</td>';
			echo '<td>'.$r->itemCalc($v->id, 'amount_currency', PhocacartPrice::cleanPrice($v->amount_currency), 'tform').'</td>';
			echo '<td align="center"></td>';
			echo '<td class="ph-col-add-cur"></td>';
			echo '</tr>';
		}

		// BRUTTO CURRENCY
		if ($v->type == 'brutto' && $v->amount_currency > 0 && $v->amount_currency != $v->amount) {
			$warningCurrency = 1;
			echo '<tr class="ph-currency-row">';
			echo '<td></td>';
			echo '<td colspan="2">'.$r->itemCalc($v->id, 'title', $v->title . ' ('.Text::_('COM_PHOCACART_CURRENCY').')', 'tform', 2). '</td>';
			echo '<td class="ph-col-add-suffix">'.$typeTxt.'</td>';
			echo '<td>'.$r->itemCalc($v->id, 'amount_currency', PhocacartPrice::cleanPrice($v->amount_currency), 'tform').'</td>';
			echo '<td align="center"></td>';
			echo '<td class="ph-col-add-cur"></td>';
			echo '</tr>';
		}
	}
}


echo '</table>';


echo '<div>&nbsp;</div>';
echo '<div class="ph-order-products-hr"></div>';
echo '<div>&nbsp;</div>';
echo '<h3>'.Text::_('COM_PHOCACART_TAX_RECAPITULATION').'</h3>';


// Tax Recapitulation

if (!empty($this->itemtaxrecapitulation)) {


	// First we render the body of the table to know if there is some currency value
	// If yes then add specific column even add specific column to header
	$oTr = array();
	$totalCurrency = 0;
	foreach($this->itemtaxrecapitulation as $k => $v) {

		// Tax recapitulation rounding included rounding (Tax recapitulation rounding = Tax recapitulation rounding + calculation rounding)

		$oTr[] = '<tr>';

		// Language Variables
        if ($this->p['order_language_variables'] == 1) {
            $oTr[] =  '<td>'.$r->itemCalc($v->id, 'title_lang', $v->title_lang, 'tcform', 1). '';
            $oTr[] =  ''.$r->itemCalc($v->id, 'title_lang_suffix', $v->title_lang_suffix, 'tcform', 1). '';
            $oTr[] =  ''.$r->itemCalc($v->id, 'title_lang_suffix2', $v->title_lang_suffix2, 'tcform', 0). '<br>';
            $oTr[] =  '</td>';

        } else {
            $oTr[] = '<td>'.$r->itemCalc($v->id, 'title', $v->title, 'tcform', 2).'</td>';
        }



		$oTr[] = '<td>'.$r->itemCalc($v->id, 'amount_netto', PhocacartPrice::cleanPrice($v->amount_netto), 'tcform', 0, 'ph-right').'</td>';
		$oTr[] = '<td>'.$r->itemCalc($v->id, 'amount_tax', PhocacartPrice::cleanPrice($v->amount_tax), 'tcform', 0, 'ph-right').'</td>';
		$oTr[] = '<td>'.$r->itemCalc($v->id, 'amount_brutto', PhocacartPrice::cleanPrice($v->amount_brutto), 'tcform', 0, 'ph-right').'</td>';
		if ($v->amount_brutto_currency > 0) {
			$oTr[] = '<td class="ph-col-add-cur ph-currency-col">'.$r->itemCalc($v->id, 'amount_brutto_currency', PhocacartPrice::cleanPrice($v->amount_brutto_currency), 'tcform', 0, 'ph-right').'</td>';
			$totalCurrency = 1;
		} else {
			$oTr[] = '<td class=""></td>';
		}
		$oTr[] = '</tr>';

		$oTr[] = '<tr>';

        // Language Variables
        if ($this->p['order_language_variables'] == 1) {
            $oTr[] = '<td class="ph-col-title-small">'.PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' '), 1 => array($v->title_lang_suffix2, ' '))).'</td>';

        } else {
            $oTr[] = '<td class="ph-col-add-cur"></td>';
        }


		$oTr[] = '<td class="ph-col-add-cur">( '. $this->pr->getPriceFormat($v->amount_netto).' )</td>';
		$oTr[] = '<td class="ph-col-add-cur">( '. $this->pr->getPriceFormat($v->amount_tax).' )</td>';
		$oTr[] = '<td class="ph-col-add-cur">( '. $this->pr->getPriceFormat($v->amount_brutto).' )</td>';
		if ($v->amount_brutto_currency > 0) {
			$oTr[] = '<td class="ph-col-add-cur ph-currency-col"></td>';
		} else {
			$oTr[] = '<td class=""></td>';
		}
		$oTr[] = '</tr>';
	}


	echo '<table class="ph-order-tax-recapitulation" id="phAdminEditTaxRecapitulation">';

	echo '<tr>';
	echo '<th>'.Text::_('COM_PHOCACART_TITLE').'</th>';
	echo '<th>'.Text::_('COM_PHOCACART_TAX_BASIS').'</th>';
	echo '<th>'.Text::_('COM_PHOCACART_TAX').'</th>';
	echo '<th>'.Text::_('COM_PHOCACART_TOTAL').'</th>';
	if ($totalCurrency == 1) {
		echo '<th class="ph-currency-col">'.Text::_('COM_PHOCACART_TOTAL').' '.Text::_('COM_PHOCACART_CURRENCY').'</td>';
	}
	echo '</tr>';

	echo implode("\n", $oTr);

	echo '</table>';
}

if ($warningCurrency == 1) {

	echo '<div>&nbsp;</div>';
echo '<div class="ph-order-products-hr"></div>';
	echo '<div>&nbsp;</div>';
	echo '<div class="alert alert-warning">';

	echo '<span class="ph-currency-row">&nbsp;&nbsp;&nbsp;</span> '.Text::_('COM_PHOCACART_ROUNDING_CURRENCY_NOT_STORED_IN_DEFAULT_CURRENCY_BUT_ORDER_CURRENCY');
	echo '</div>';

}


echo '<div>&nbsp;</div>';
echo '<div class="ph-order-products-hr"></div>';

echo $r->endTab();



echo $r->startTab('download', $tabs['download']);

if (!empty($this->itemproducts)) {
    /*phocacart import('phocacart.path.route');*/

    foreach($this->itemproducts as $k => $v) {


        echo '<div class="ph-admin-download-links">';
        echo '<h3>'.$v->title.'</h3>';
        echo '<table class="ph-table-download-links">';
        echo '<tr><td width="10%">&nbsp;</td><td width="90%">&nbsp;</td></tr>';

        if (!empty($v->downloads)) {

            foreach ($v->downloads as $k2 => $v2) {

                if ($v2->download_token) {


                    if ($v2->type == 0 || $v2->type == 1) {
                        $type = '<span class="label label-success badge bg-success">' . Text::_('COM_PHOCACART_DOWNLOAD_FILE') . '</span>';
                    } else if ($v2->type == 2) {
                        $type = '<span class="label label-info badge bg-info">' . Text::_('COM_PHOCACART_ADDITIONAL_DOWNLOAD_FILE') . '</span>';
                    } /*else {
                    $type = '<span class="label label-warning badge bg-warning">'.Text::_('COM_PHOCACART_DOWNLOAD_FILE_ATTRIBUTE').'</span>';
                    }*/

                    echo '<tr><td>' . $type. '</td>';
                    echo '<td>'.htmlspecialchars($v2->download_file) . '</td></tr>';

                    //$dLink = Route::_(PhocacartRoute::getDownloadRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token)
                    //. '&d='.htmlspecialchars($v->download_token));
                    $link = PhocacartRoute::getDownloadRoute() . '&o=' . htmlspecialchars($this->itemcommon->order_token)
                        . '&d=' . htmlspecialchars($v2->download_token);

                    $dLink = PhocacartPath::getRightPathLink($link);


                    echo '<tr><td>'.Text::_('COM_PHOCACART_DOWNLOAD_LINK').': </td>';
                    echo '<td><input type="text" name="" value="' . $dLink . '" class="form-control" style="width: 90%;" /></td></tr>';

                }

            }

            if (!empty($v->attributes)) {
                foreach ($v->attributes as $k2 => $v2) {

                    if ($v2->download_token) {


                        $type = '<span class="label label-warning badge bg-warning">'.Text::_('COM_PHOCACART_DOWNLOAD_FILE_ATTRIBUTE').'</span>';
                        echo '<tr><td>'.$type.'</td>';
                        echo '<td>'.htmlspecialchars($v2->download_file).'</td></tr>';

                        $link = PhocacartRoute::getDownloadRoute() . '&o='.$this->itemcommon->order_token.'&d='.htmlspecialchars($v2->download_token);
                        $dLink = PhocacartPath::getRightPathLink($link);



                        echo '<tr><td>'.$v2->attribute_title.': '.$v2->option_title.'</td>';

                        echo '<td><input type="text" name="" value="'.$dLink.'" class="form-control" style="width: 90%;" /></td></tr>';


                    }
                }
            }

        }
        echo '</table>';
        echo '</div>';
/*

        if (isset($v->download_token)) {
            echo '<tr><td>'.$v->title.'</td>';
            //$dLink = Route::_(PhocacartRoute::getDownloadRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token)
            //. '&d='.htmlspecialchars($v->download_token));
            $link = PhocacartRoute::getDownloadRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token)
            . '&d='.htmlspecialchars($v->download_token);

            $dLink = PhocacartPath::getRightPathLink($link);

            echo '<td><input type="text" name="" value="'.$dLink.'" style="width: 90%;" /></td></tr>';

            if ($v->download_type == 0 || $v->download_type == 1) {
                $type = '<span class="label label-success badge bg-success">'.Text::_('COM_PHOCACART_DOWNLOAD_FILE').'</span>';
            } else if ($v->download_type == 2) {
                $type = '<span class="label label-info badge bg-info">'.Text::_('COM_PHOCACART_ADDITIONAL_DOWNLOAD_FILE').'</span>';
            } /*else {
                $type = '<span class="label label-warning badge bg-warning">'.Text::_('COM_PHOCACART_DOWNLOAD_FILE_ATTRIBUTE').'</span>';
            }*/
        /*	echo '<tr><td>'.$type.'</td>';
            echo '<td><small>('.htmlspecialchars($v->download_file).')</small></td></tr>';




            // Product Attribute Option Download File
            if (!empty($v->attributes)) {
                foreach ($v->attributes as $k2 => $v2) {

                    if ($v2->download_token) {
                        echo '<tr><td>&nbsp;</td>';
                        echo '<td>&nbsp;</td></tr>';

                        echo '<tr><td>'.$v->title.'('.$v2->attribute_title.': '.$v2->option_title.')</td>';
                        $link = PhocacartRoute::getDownloadRoute() . '&o='.$this->itemcommon->order_token.'&d='.htmlspecialchars($v2->download_token);
                        $dLink = PhocacartPath::getRightPathLink($link);
                        echo '<td><input type="text" name="" value="'.$dLink.'" style="width: 90%;" /></td></tr>';

                        $type = '<span class="label label-warning badge bg-warning">'.Text::_('COM_PHOCACART_DOWNLOAD_FILE_ATTRIBUTE').'</span>';
                        echo '<tr><td>'.$type.'</td>';
                        echo '<td><small>('.htmlspecialchars($v2->download_file).')</small></td></tr>';

                    }
                }
            }

            echo '<tr><td>&nbsp;</td>';
            echo '<td>&nbsp;</td></tr>';
        }*/
    }
    //echo '</table>';*/

}
echo $r->endTab();

echo $r->startTab('orderlink', $tabs['orderlink']);

if (isset($this->itemcommon->order_token)) {
	if (!empty($this->itemproducts)) {
		/*phocacart import('phocacart.path.route');*/
        echo '<div class="ph-admin-order-link">';
		echo '<table class="ph-table-order-link">';
        echo '<tr><td width="10%">&nbsp;</td><td width="90%">&nbsp;</td></tr>';
		//$dLink = Route::_(PhocacartRoute::getDownloadRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token)
				//. '&d='.htmlspecialchars($v->download_token));
		$link = PhocacartRoute::getOrdersRoute() . '&o='.htmlspecialchars($this->itemcommon->order_token);
		$oLink = PhocacartPath::getRightPathLink($link);

		echo '<tr><td>'.Text::_('COM_PHOCACART_ORDER_LINK').': </td><td><input type="text" name="" value="'.$oLink.'" class="form-control" style="width: 90%;" /></td></tr>';
		echo '</table>';
		echo '</div>';
	}
}
echo $r->endTab();




echo $r->startTab('billing', $tabs['billing']);
$formArray = array ('order_number', 'order_number_id', 'receipt_number', 'receipt_number_id', 'invoice_number', 'invoice_number_id', 'invoice_prn', 'queue_number', 'queue_number_id', 'invoice_date', 'invoice_due_date', 'invoice_time_of_supply', 'date', 'modified', 'invoice_spec_top_desc', 'invoice_spec_middle_desc', 'invoice_spec_bottom_desc', 'oidn_spec_billing_desc', 'oidn_spec_shipping_desc');
echo $r->group($this->form, $formArray);
echo $r->endTab();


/*
echo $r->startTab('publishing', $tabs['publishing']);
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

echo $r->endTabs();
echo '</div>';//end col-xs-12 col-sm-12 col-md-12
// Second Column
//echo '<div class="col-xs-12 col-sm-2 col-md-2">';
//echo '<div class="alert alert-error alert-danger">'.Text::_('COM_PHOCACART_WARNING_EDIT_ORDER').'</div>';
//echo '</div>';//end span2
echo $r->formInputs();
echo $r->endForm();
?>

