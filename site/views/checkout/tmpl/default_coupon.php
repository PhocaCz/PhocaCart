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
use Joomla\CMS\HTML\HTMLHelper;

if ($this->t['enable_coupons'] > 0 && !$this->t['cartempty']) {


	echo '<div id="ph-request-message" style="display:none"></div>';

	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-apply-coupon">';

	echo '<div class="' . $this->s['c']['col.xs12.sm12.md12'] . '">';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-inline '.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutCoupon">';
	echo '<div class="' . $this->s['c']['form-group'] . '">';

	if ($this->t['couponcodevalue'] != '') {
		// REMOVE COUPON
		echo '<input type="hidden" name="phcoupon" value="" />'. "\n";
		// Make the following input only a design input without name and value - the value for removing is set as empty in hidden field
		// name="phcouponremove" is abstract - not used, named to be ignored when saving form but displayed including the coupon code
		echo '<input class="'.$this->s['c']['form-control'].' ph-input-sm ph-input-apply-coupon" type="text" name="phcouponremove" id="phcoupon" value="' . $this->t['couponcodevalue'] . '" >';
		echo '</div>';// end from-group
		echo '<button class="' . $this->s['c']['btn.btn-primary'] . ' ph-btn">';
		//echo '<span class="' . $this->s['i']['remove'] . '"></span>';
		echo PhocacartRenderIcon::icon($this->s['i']['remove'], '', ' ');
		echo Text::_('COM_PHOCACART_REMOVE_COUPON') . '</button>';
	} else {
		// ADD COUPON
		echo '<input class="'.$this->s['c']['form-control'].' ph-input-sm ph-input-apply-coupon" type="text" name="phcoupon" id="phcoupon" value="' . $this->t['couponcodevalue'] . '" >';
		echo '</div>';// end from-group
		echo '<button class="' . $this->s['c']['btn.btn-primary'] . ' ph-btn">'.PhocacartRenderIcon::icon($this->s['i']['save'], '', ' ') . Text::_('COM_PHOCACART_APPLY_COUPON') . '</button>';
	}

	echo '<input type="hidden" name="task" value="checkout.savecoupon" />'. "\n";
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>'. "\n";


	echo '</div>';
	echo '</div>' . "\n";// end box action



}

?>
