<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();


echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_APPLY_BENEFITS').'</div>';

echo '<div class="ph-checkout-payment-cost-box">';


echo '<form action="'.$this->t['linkpos'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form">';

echo '<div class="'.$this->s['c']['row'].' ph-pos-payment-method-row">';
//echo '<form action="'.$this->t['linkpos'].'" method="post" class="form-horizontal form-validate" role="form" id="phPosPaginationBox">';



$activePaymentId = 0;
if (!empty($this->t['paymentmethods'])) {

	foreach($this->t['paymentmethods'] as $k => $v) {

		if ((int)$this->t['paymentid'] == (int)$v->id) {
			$activePaymentId = (int)$this->t['paymentid'];
			break;
		}
	}
}

echo '<input type="hidden" name="id" value="'.(int)$activePaymentId.'" />';

echo '<input type="hidden" name="task" value="pos.savepayment" />'. "\n";
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
echo '<input type="hidden" name="redirectsuccess" value="main.content.products" />';
echo '<input type="hidden" name="redirecterror" value="main.content.applybenefits" />';
echo HTMLHelper::_('form.token');


echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm5.md5'].'">';
echo '<div class="ph-pos-coupon-reward-box">';
// COUPON CODE
if ($this->t['enable_coupons'] > 0) {
	//echo '<div class="col-sm-12 col-md-12 ">';
	echo '<label>'.Text::_('COM_PHOCACART_COUPON_CODE'). ' <small>('.Text::_('COM_PHOCACART_APPLY_COUPON_CODE').')</small><br /><input type="text" name="phcoupon" id="phcoupon" value="'.$this->t['couponcodevalue'].'" autocomplete="off"></label>';
	//echo '</div><div class="ph-cb"></div>';
}

// REWARD POINTS
if ($this->t['rewards']['apply']) {
	//echo '<div class="col-sm-12 col-md-12 ">';
	echo '<label>'.Text::_('COM_PHOCACART_REWARD_POINTS').' '.$this->t['rewards']['text'].'<br /><input type="text" name="phreward" id="phreward" value="'.$this->t['rewards']['usedvalue'].'" autocomplete="off"></label>';
	//echo '</div><div class="ph-cb"></div>';
}

echo '</div>';
echo '</div>';

echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm4.md4'].'"></div>';

echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm3.md3'].' ph-pos-customer-action">';
echo '<button class="'.$this->s['c']['btn.btn-success'].' editMainContent">'.Text::_('COM_PHOCACART_APPLY').'</button>';
echo '</div>';



echo '</div>';

echo '</form>'. "\n";

echo '<div class="ph-cb ph-pos-hr-sub"></div>';


echo '</div>';// end payment cost box

// Pagination variables only
$this->items = false;
echo $this->loadTemplate('pagination');
?>
