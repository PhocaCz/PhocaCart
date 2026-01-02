<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$layoutPC 	= new FileLayout('form_privacy_checkbox', null, array('component' => 'com_phocacart'));
$layoutNC 	= new FileLayout('form_newsletter_checkbox', null, array('component' => 'com_phocacart'));
$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));

if ($this->a->confirm == 1) {


	if ($this->t['stock_checking'] == 1 && $this->t['stock_checkout'] == 1 && $this->t['stockvalid'] == 0) {
		// Header
		echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" id="phcheckoutconfirmedit" >';
		echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_PRODUCTS_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK_UPDATE_QUANTITY_BEFORE_ORDERING')));
		echo '</div>';

	} else if ($this->t['minqtyvalid'] == 0 || $this->t['minmultipleqtyvalid'] == 0) {
		// Header
		if ($this->t['minqtyvalid'] == 0) {
			echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING')));
			echo '</div>';
		}

		if ($this->t['minmultipleqtyvalid'] == 0) {
			echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
			echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MINIMUM_MULTIPLE_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING')));
			echo '</div>';
		}

	} else if ($this->t['maxqtyvalid'] == 0) {
		echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
		echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_MAXIMUM_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING')));
		echo '</div>';
	} else {
		// Header
		echo '<div class="ph-checkout-confirm-box-row">';


		echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutAddress">';

		echo '<div class="ph-checkout-box-action-raw">';

		echo '<div id="ph-request-message" style="display:none"></div>';

		echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
		echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-confirm-row" id="phConfirm" >';
		echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_NOTES_AND_REQUESTS_ABOUT_ORDER').'</div>';
		echo '<textarea class="'.$this->s['c']['inputbox.textarea'].'" name="phcomment" rows="3"></textarea>';
		echo '</div>';
		echo '</div>';


		echo '<div class="ph-cb"></div>';

		if ($this->t['enable_captcha_checkout']) {
			echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row">';

			echo '<div class="'.$this->s['c']['control-group'].'">';

			echo '<div class="'.$this->s['c']['control-label'].'">';
			echo '<label id="jform_question_captcha-lbl" for="jform_question_captcha" class="hasPopover required" title="" data-content="'.Text::_('COM_PHOCACART_PLEASE_PROVE_THAT_YOU_ARE_HUMAN').'" data-original-title="'.Text::_('COM_PHOCACART_SECURITY_CHECK').'">'.Text::_('COM_PHOCACART_SECURITY_CHECK').'<span class="star">&nbsp;*</span></label>';
			echo '</div>';

			echo '<div class="'.$this->s['c']['controls'].'">'.PhocacartCaptchaRecaptcha::render().'</div>';

			echo '</div>';// end group

			echo '</div>';// end col

			echo '<div class="ph-cb"></div>';
		}


		echo '<div class="ph-checkout-confirm-box-row-checkboxes">';

		if ($this->t['display_checkout_toc_checkbox'] > 0) {

			$d					= array();
			$d['s']			    = $this->s;
			$d['label_text']	= $this->t['terms_conditions_label_text'];
			$d['id']			= 'phCheckoutConfirmTermsConditions';
			$d['name']			= 'phcheckouttac';
			$d['class']			= 'ph-pull-right checkbox ph-checkout-checkbox-confirm';
			$d['display']		= $this->t['display_checkout_toc_checkbox'];

			echo '<div class="ph-cb"></div>';
			echo $layoutPC->render($d);
		}


		if ($this->t['display_checkout_privacy_checkbox'] > 0) {

			$d					= array();
			$d['s']			    = $this->s;
			$d['label_text']	= $this->t['checkout_privacy_checkbox_label_text'];
			$d['id']			= 'phCheckoutPrivacyCheckbox';
			$d['name']			= 'privacy';
			$d['class']			= 'ph-pull-right checkbox ph-checkout-checkbox-confirm';
			$d['display']		= $this->t['display_checkout_privacy_checkbox'];

			echo '<div class="ph-cb"></div>';
			echo $layoutPC->render($d);
		}

		if ($this->t['display_checkout_newsletter_checkbox'] > 0) {

			$d					= array();
			$d['s']			    = $this->s;
			$d['label_text']	= $this->t['checkout_newsletter_checkbox_label_text'];
			$d['id']			= 'phCheckoutNewsletterCheckbox';
			$d['name']			= 'newsletter';
			$d['class']			= 'ph-pull-right checkbox ph-checkout-checkbox-confirm';
			$d['display']		= $this->t['display_checkout_newsletter_checkbox'];

			echo '<div class="ph-cb"></div>';
			echo $layoutNC->render($d);
		}

		echo '<div class="ph-cb"></div>';

		echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-confirm">';
		echo '<button class="'.$this->s['c']['btn.btn-primary'].' ph-btn">';
		//echo '<span class="'.$this->s['i']['ok'].'"></span> ';
		echo PhocacartRenderIcon::icon($this->s['i']['ok'], '', ' ');
		echo $this->t['confirm_order_text'];
		echo '</button>';
		echo '</div>';

		echo '<div class="ph-cb"></div>';


		echo '</div>';// end

		echo '</div>'."\n";// end ph-checkout-box-action-raw

		echo '<input type="hidden" name="task" value="checkout.order" />'. "\n";
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
		echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
		echo HTMLHelper::_('form.token');
		echo '</form>'. "\n";


		echo '</div>';// end ph-checkout-confirm-box-row


	}

} else {

}
?>
