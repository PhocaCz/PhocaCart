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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
//HTMLHelper::_('formbehavior.chosen', 'select');


$link		= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditproductpricegroup&tmpl=component&id='.(int)$this->id);

echo '<div id="phAdminEditPopup" class="ph-edit-stock-advanced-box">';

echo '<div class="alert alert-info alert-dismissible fade show" role="alert">'
	. '<ul>'
		.'<li>'.Text::_('COM_PHOCACART_ONLY_GROUPS_WHICH_ARE_SET_IN_PARAMETER_CUSTOMER_GROUP_ARE_LISTED_HERE') . '</li>'
	.'<li>'.Text::_('COM_PHOCACART_TO_SEE_ALL_CUSTOMER_GROUPS_LISTED_CLOSE_WINDOW_SAVE_THE_PRODUCT_FIRST') . '</li>'
	. '<li>'. Text::_('COM_PHOCACART_CHECK_LIST_EVERY_TIME_CUSTOMER_GROUPS_CHANGE').'</li>'
	. '<li>'.Text::_('COM_PHOCACART_IF_YOU_SET_ZERO_AS_PRICE_THEN_PRICE_WILL_BE_ZERO').'</li>'
	.'</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="'.Text::_('COM_PHOCACART_CLOSE').'"></button></div>';

if (!empty($this->t['product'])) {

	$priceDef = PhocacartPrice::cleanPrice($this->t['product']->price);
	$js = '
		document.addEventListener("DOMContentLoaded", () => {
			document.querySelectorAll(".calc-price").forEach(el => {
				el.addEventListener("input", e => {
					const price = el.value,
						percent = (' . $priceDef . ' - price) / (' . $priceDef . ' / 100);
					if (!isNaN(percent))	
						document.getElementById("calc-percent" + el.dataset.priceIndex).value = percent;		
				});
			}); 

			document.querySelectorAll(".calc-percent").forEach(el => {
				el.addEventListener("input", e => {
					const percent = el.value,
						price = ' . $priceDef . ' * (100 - percent) / 100;
					if (!isNaN(price))	
						document.getElementById("calc-price" + el.dataset.priceIndex).value = price;		
				});
			}); 
		});
	';
	Factory::getDocument()->addScriptDeclaration($js);

	echo '<div class="ph-product-customer-group-box">';

	echo '<form action="'.$link.'" method="post">';


	if (!empty($this->t['groups'])) {

		echo '<table class="table table-sm table-responsive ph-product-customer-group-box">';

		echo '<tr>';
		echo '<th>'.Text::_('COM_PHOCACART_CUSTOMER_GROUP').'</th>';
		//echo '<th>'.Text::_('COM_PHOCACART_PRODUCT_KEY').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_PRICE').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_DISCOUNT_IN_PERCENTAGE').'</th>';
		echo '</tr>';


		foreach($this->t['groups'] as $k => $v) {


			echo '<tr>';
			echo '<th>'.Text::_($v['title']).'</th>';

			if ($v['type'] == 1) {

				// Default
				echo '<td><input type="text" class="input-small form-control" name="jform['.$v['id'].'][price]" value="'.PhocacartPrice::cleanPrice($this->t['product']->price).'" readonly />';
				echo '<input type="hidden" name="jform['.$v['id'].'][group_id]" value="'.$v['id'].'" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$this->id.'" />';
				echo '</td><td>&nbsp;';

			} else {

				// Set value from database
				$price = '';
				if (isset($this->t['product_groups'][$v['id']]['price'])) {
					$price = $this->t['product_groups'][$v['id']]['price'];
					if ($price > 0 || $price == 0) {
						$price = PhocacartPrice::cleanPrice($price);
					}
				}
				if ($priceDef) {
					$percent = ((float) $priceDef - (float) $price) / ((float) $priceDef / 100);
				} else {
					$percent = 0;
				}
				echo '<td><input type="text" class="input-small form-control calc-price" name="jform['.$v['id'].'][price]" value="'.$price.'" id="calc-price' . $v['id'] . '" data-price-index="' . $v['id'] . '" />';
				echo '</td><td>';
				echo '<input type="text" class="input-small form-control calc-percent" value="'.$percent.'" id="calc-percent' . $v['id'] . '" data-price-index="' . $v['id'] . '" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][group_id]" value="'.$v['id'].'" />';
				echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$this->id.'" />';
				//echo '<input type="hidden" name="jform['.$v['id'].'][product_id]" value="'.$v['product_id'].'" />';
				//echo '<input type="hidden" name="jform['.$v['id'].'][attributes]" value="'.serialize($v['attributes']).'" />';



			}

			echo '</td>';
			echo '</tr>';

		}

		//echo '<tr><td colspan="3"></td></tr>';

		echo '<tr>';
		echo '<td></td><td></td>';

		echo '<td colspan="2" class="ph-right">';
		echo '<input type="hidden" name="id" value="'.(int)$this->t['product']->id.'">';
		echo '<input type="hidden" name="task" value="phocacarteditproductpricegroup.save">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<button class="btn btn-success btn-sm ph-btn"><span class="icon-ok ph-icon-white"></span> '.Text::_('COM_PHOCACART_SAVE').'</button>';
		echo HTMLHelper::_('form.token');
		echo '</td>';


		echo '</tr>';

		echo '</table>';
	}
}

?>
