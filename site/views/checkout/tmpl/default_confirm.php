<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


if ($this->a->confirm == 1) {
	
	
	if ($this->t['stock_checking'] == 1 && $this->t['stock_checkout'] == 1 && $this->t['stockvalid'] == 0) {
		// Header
		echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" id="phcheckoutconfirmedit" >';
		echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_PRODUCTS_NOT_AVAILABLE_IN_QUANTITY_OR_NOT_IN_STOCK_UPDATE_QUANTITY_BEFORE_ORDERING').'</div>';
		echo '</div><div class="ph-cb"></div>';
	
	} else if ($this->t['minqtyvalid'] == 0) {
		// Header
		echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
		echo '<div class="alert alert-error">'.JText::_('COM_PHOCACART_MINIMUM_ORDER_QUANTITY_OF_ONE_OR_MORE_PRODUCTS_NOT_MET_UPDATE_QUANTITY_BEFORE_ORDERING').'</div>';
		echo '</div><div class="ph-cb"></div>';
	
	} else {
		// Header
		echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
		
		
		echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
		echo '<div class="ph-checkout-box-action-raw">';
		echo '<div id="ph-request-message" style="display:none"></div>';
		echo '<div class="col-sm-12 col-md-12 ph-checkout-confirm-row" id="phConfirm" >';

		echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_NOTES_AND_REQUESTS_ABOUT_ORDER').'</div>';
		echo '<textarea class="form-control" name="phcomment" rows="3"></textarea>';
		
		echo '</div>';
		
		
		
		echo '<div class="ph-cb">&nbsp;</div>';
		
		
		echo '<div class="col-sm-12 col-md-12 ">';
		
		echo ' <div class="pull-right ph-checkout-confirm">';	
		echo '<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-ok"></span> '.JText::_('COM_PHOCACART_CONFIRM_ORDER').'</button>';
		echo '</div><div class="ph-cb"></div>';
		
		
		$linkTermsHandler= 'onclick="window.open(this.href, \'orderview\', \'width=780,height=560,scrollbars=yes,menubar=no,resizable=yes\');return false;"';
		//$linkTerms 	= JRoute::_( 'index.php?option=com_phocacart&view=terms&tmpl=component' );	
		$linkTerms 	= JRoute::_( PhocaCartRoute::getTermsRoute() . '&tmpl=component');	

		echo '<div class="pull-right checkbox ph-checkout-checkbox-confirm">';
		echo '<label><input type="checkbox" id="phCheckoutConfirmTermsConditions" name="phcheckouttac" required="" aria-required="true"> '.JText::_('COM_PHOCACART_I_HAVE_READ_AND_AGREE_TO_THE'). ' <a href="'.$linkTerms.'" '.$linkTermsHandler.' >' . JText::_('COM_PHOCACART_TERMS_AND_CONDITIONS').'</a>';
		echo '</label>';
		echo '</div> ';
		
		
		
		echo '<div class="ph-cb"></div>';
		
		echo '</div>';
		
		
		echo '<div class="ph-cb"></div>';
		echo '</div>'."\n";// end box action

		echo '<input type="hidden" name="task" value="checkout.order" />'. "\n";
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
		echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
		echo JHtml::_('form.token');
		echo '</form>'. "\n";
		

		echo '</div>';// end checkout box row
		
		
	
	}

} else {

}
?>