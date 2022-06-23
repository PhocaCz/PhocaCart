<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

$price	= $this->t['price'];

echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_SHIPPING_METHODS').'</div>';

echo '<div class="ph-checkout-shipping-cost-box">';

if (!empty($this->t['shippingmethods'])) {

	foreach($this->t['shippingmethods'] as $k => $v) {

		echo '<form action="'.$this->t['linkpos'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form">';

		echo '<div class="'.$this->s['c']['row'].' ph-pos-shipping-method-row">';


		$checked = '';
		if (isset($v->selected) && $v->selected == 1 ) {
			$checked = 'checked="checked"';
		}

		$priceI = $price->getPriceItemsShipping($v->cost, $v->cost_additional, $v->calculation_type, $this->t['total'][0], $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, 0, 1);

		echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm5.md5'].'">';
		if ($v->image != '') {
			echo '<span class="ph-shipping-image"><img src="'.Uri::base(true) .'/'. $v->image.'" alt="'.htmlspecialchars(strip_tags($v->title)).'" /></span>';
		}

		echo '<span class="ph-shipping-title">'.$v->title.'</span>';

		if ($this->t['display_shipping_desc'] && $v->description != '') {
			echo '<div class="ph-checkout-shipping-desc">'.HTMLHelper::_('content.prepare', $v->description).'</div>';
		}
		echo '</div>';


		echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm4.md4'].'"><div class="'.$this->s['c']['row'].'">';

		if ($this->t['zero_shipping_price'] == 0 && $priceI['zero'] == 1) {
			// Display blank price field
		} else if ($this->t['zero_shipping_price'] == 2 && $priceI['zero'] == 1) {
			// Display free text
			echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].'"></div>';
			echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-shipping-tax">'.Text::_('COM_PHOCACART_FREE').'</div>';
		} else {
			if ($priceI['nettoformat'] == $priceI['bruttoformat']) {

			} else if ($priceI['nettoformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].'">'.$priceI['nettotxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-shipping-netto">'.$priceI['nettoformat'].'</div>';
			}

			if ($priceI['taxformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].'">'.$priceI['taxtxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-shipping-tax">'.$priceI['taxformat'].'</div>';
			}

			if ($priceI['bruttoformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].'">'.$priceI['bruttotxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-shipping-brutto">'.$priceI['bruttoformat'].'</div>';
			}
		}

		echo '</div></div>';


		echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm3.md3'].' ph-pos-customer-action">';
		if ((int)$this->t['shippingid'] == (int)$v->id) {
			echo '<button class="'.$this->s['c']['btn.btn-danger'].' editMainContent">'.Text::_('COM_PHOCACART_DESELECT').'</button>';
			echo '<input type="hidden" name="id" value="0" />';
		} else {
			echo '<button class="'.$this->s['c']['btn.btn-success'].' editMainContent">'.Text::_('COM_PHOCACART_SELECT').'</button>';
			echo '<input type="hidden" name="id" value="'.(int)$v->id.'" />';
		}
		echo '</div>';

		echo '<input type="hidden" name="task" value="pos.saveshipping" />'. "\n";
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
		echo '<input type="hidden" name="redirectsuccess" value="main.content.products" />';
		echo '<input type="hidden" name="redirecterror" value="main.content.shippingmethods" />';
		echo HTMLHelper::_('form.token');


		echo '</div>';

		echo '</form>'. "\n";

		echo '<div class="ph-cb ph-pos-hr-sub"></div>';

	}
} else {
	echo '<div class="ph-pos-no-items">'.Text::_('COM_PHOCACART_NO_SHIPPING_METHOD_FOUND').'</div>';
}

echo '</div>';// end shipping cost box

// Pagination variables only
$this->items = false;
echo $this->loadTemplate('pagination');
?>
