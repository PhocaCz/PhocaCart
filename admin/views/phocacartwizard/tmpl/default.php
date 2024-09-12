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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
/*
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );
*/

$linkWizard 		= 'index.php?option=com_phocacart&view=phocacartwizard&tmpl=component';
$linkCategoryEdit 	= 'index.php?option=com_phocacart&view=phocacartcategory&layout=edit';
$linkProductEdit 	= 'index.php?option=com_phocacart&view=phocacartitem&layout=edit';
$linkTaxEdit		= 'index.php?option=com_phocacart&view=phocacarttax&layout=edit';
$linkShippingEdit	= 'index.php?option=com_phocacart&view=phocacartshipping&layout=edit';
$linkPaymentEdit	= 'index.php?option=com_phocacart&view=phocacartpayment&layout=edit';
$linkCountryView	= 'index.php?option=com_phocacart&view=phocacartcountries';
$linkRegionView		= 'index.php?option=com_phocacart&view=phocacartregions';
$linkMenus			= 'index.php?option=com_menus&view=items';
$linkModules		= 'index.php?option=com_modules';
$linkOptions		= 'index.php?option=com_config&view=component&component=com_phocacart&path=&return=aHR0cDovL2xvY2FsaG9zdC9KMzYyL2FkbWluaXN0cmF0b3IvaW5kZXgucGhwP29wdGlvbj1jb21fcGhvY2FjYXJ0';

$urlAjax 			= 'index.php?option=com_phocacart&task=phocacartwizard.updatestatus&format=json&'. Session::getFormToken().'=1';
$urlAjaxSkipWizard 	= 'index.php?option=com_phocacart&task=phocacartwizard.skipwizard&format=json&'. Session::getFormToken().'=1';

PhocacartRenderAdminjs::renderAjaxDoRequestWizard(); // Event what must happen to run renderAjaxDoRequestWizardAfterChange
PhocacartRenderAdminjs::renderAjaxDoRequestWizardAfterChange($urlAjax, 'phClickBtn');// Ajax to render changes
PhocacartRenderAdminjs::renderAjaxDoRequestWizardController($urlAjaxSkipWizard, 'phCloseWizard');




if ($this->page == 0) { ?>




<div class="ph-wizard-start-page-window firstpage" data-page="0">
	<h1 class="ph-modal-content-header"><?php echo Text::_('COM_PHOCACART_WIZARD_WELCOME'); ?></h1>
	<div class="ph-wizard-top-text"><?php echo Text::_('COM_PHOCACART_WIZARD_THANK_YOU_FOR_CHOOSING_PHOCA_CART'); ?></div>
	<div class="row">
		<div class="span6 col-sm-6 col-md-6">
			<div class="ph-wizard-start-page-box">

				<div><?php echo Text::_('COM_PHOCACART_WIZARD_DOWNLOAD_DEMO_DATA'); ?>:</div>

				<div class="ph-wizard-center-button"><a class="btn btn-primary ph-btn"href="https://www.phoca.cz/download/category/100-phoca-cart-component" target="_blank"><span class="fa fa-download fa-fw icon-download"></span> <?php echo Text::_('COM_PHOCACART_DOWNLOAD'); ?></a></div>

				<ol>
					<li><?php echo Text::_('COM_PHOCACART_WIZARD_INSTALL_BOTH_PACKAGES'); ?></li>
					<li><?php echo Text::_('COM_PHOCACART_WIZARD_CREATE_OR_IMPORT_COUNTRIES'); ?></li>
					<li><?php echo Text::_('COM_PHOCACART_WIZARD_CREATE_MENU_LINK'); ?></li>

					<li><?php echo Text::_('COM_PHOCACART_WIZARD_BOOTSTRAP_TEMPLATE_RECOMMENDED'); ?> <a href="https://www.phoca.cz/joomla-templates" target="_blank"><?php echo Text::_('COM_PHOCACART_WIZARD_DOWNLOAD_BOOTSTRAP_TEMPLATE'); ?></a>.</li>
				</ol>


				<div><?php echo Text::_('COM_PHOCACART_FOR_MORE_INFORMATION_FOLLOW_THIS_GUIDE'); ?>: <a href="https://www.phoca.cz/documentation/115-phoca-cart/116-phoca-cart-component/807-installing-sample-data" target="_blank"><?php echo Text::_('COM_PHOCACART_INSTALLING_SAMPLE_DATA_IN_PHOCA_CART'); ?></a>.</div>

			</div>
		</div>

		<div class="span6  col-sm-6 col-md-6">
			<div class="ph-wizard-start-page-box">
				<div><?php echo Text::_('COM_PHOCACART_WIZARD_START_QUICK_SETUP_WIZARD');?>.</div>
				<div>&nbsp;</div>
				<div class="ph-wizard-center-button"><a class="btn btn-warning ph-btn" href="<?php echo Route::_($linkWizard.'&page=1'); ?>"><span class="glyphicon glyphicon-ok share-alt icon-share"></span> <?php echo Text::_('COM_PHOCACART_START_WIZARD'); ?></a></div>
			</div>
		</div>
	</div>
</div><?php

} else if ($this->page == 1) {
?>

<div class="ph-wizard-start-page-window nextpage" data-page="1">

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span12 col-sm-12 col-md-12">
			<div id="phResultWizardAll" class="ph-center" style="display:none;">
				<div><?php echo Text::_('COM_PHOCACART_YOUR_STORE_IS_READY'); ?></div>
				<div><button id="phCloseWizard" class="btn btn-success ph-btn"><span class="icon-delete"></span> <?php echo Text::_('COM_PHOCACART_CLOSE_WIZARD'); ?></button></div>
			</div>
		</div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_CREATE_AT_LEAST_ONE_CATEGORY'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkCategoryEdit);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_CREATE_CATEGORY'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardCategory"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_CREATE_TAX_IN_CASE_TAXABLE_PRODUCTS_WILL_BE_SOLD_IN_SHOP'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkTaxEdit);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_CREATE_TAX'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardTax"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_CREATE_AT_LEAST_ONE_PRODUCT'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkProductEdit);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_CREATE_PRODUCT'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardProduct"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_CREATE_AT_LEAST_ONE_SHIPPING_METHOD'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkShippingEdit);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_CREATE_SHIPPING_METHOD'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardShipping"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_CREATE_AT_LEAST_ONE_PAYMENT_METHOD'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkPaymentEdit);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_CREATE_PAYMENT_METHOD'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardPayment"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_ADD_OR_IMPORT_COUNTRIES'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkCountryView);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_ADD_OR_IMPORT_COUNTRY'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardCountry"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_ADD_OR_IMPORT_REGIONS'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkRegionView);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_ADD_OR_IMPORT_REGION'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardRegion"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_CREATE_MENU_LINK_TO_PHOCA_CART'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkMenus);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_CREATE_MENU_LINK'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardMenu"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_ADD_OR_EDIT_PHOCA_CART_MODULES'); ?> (<a href="https://www.phoca.cz/download/category/100-phoca-cart-component" target="_blank"><?php echo Text::_('COM_PHOCACART_DOWNLOAD_PHOCA_CART_MODULES'); ?></a>)</div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkModules);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_ADD_OR_EDIT_MODULES'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardModule"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span6 col-sm-6 col-md-6"><?php echo Text::_('COM_PHOCACART_EDIT_PHOCA_CART_OPTIONS'); ?></div>
		<div class="span3 col-sm-3 col-md-3 "><a class="btn btn-primary ph-btn phClickBtn" href="<?php echo Route::_($linkOptions);?>" target="_parent"><?php echo Text::_('COM_PHOCACART_OPTIONS'); ?></a></div>
		<div class="span3 col-sm-3 col-md-3"><div id="phResultWizardOption"></div></div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span12 col-sm-12 col-md-12"><?php echo Text::_('COM_PHOCACART_WIZARD_BOOTSTRAP_TEMPLATE_RECOMMENDED'); ?> <a href="https://www.phoca.cz/joomla-templates" target="_blank"><?php echo Text::_('COM_PHOCACART_WIZARD_DOWNLOAD_BOOTSTRAP_TEMPLATE'); ?></a>.</div>
	</div>

	<div class="row ph-vertical-align ph-wizard-row">
		<div class="span12 col-sm-12 col-md-12"><?php echo Text::_('COM_PHOCACART_DISCOVER'); ?> <a href="https://www.phoca.cz/phocacart-extensions" target="_blank"><?php echo Text::_('COM_PHOCACART_PHOCA_CART_EXTENSIONS'); ?></a>.</div>
	</div>

</div><?php

}




/*
$link	= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacartedittax&tmpl=component&id='.(int)$this->id);

if (isset($this->item->id) && (int)$this->item->id > 0 && isset($this->item->title) && $this->item->title != '') {

	$flag = '';
	if (isset($this->item->code2) && $this->item->code2 != '') {
		$flag = PhocacartUtils::getCountryFlag($this->item->code2);
	}
	echo '<h1 class="ph-modal-header">'.$flag .' '.$this->item->title.'</h1>';

	echo '<form action="'.$link.'" method="post">';

	//echo '<table class="ph-tax-edit">';
	echo '<div class="row ph-tax-edit-header">';
	echo '<div class="span4 col-sm-4 col-md-4">'.Text::_('COM_PHOCACART_TAX_NAME').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_TAX_RATE').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_TITLE').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_ALIAS').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_TAX_RATE_COUNTRY').'</div>';
	echo '</div>';

	if (!empty($this->itemcountrytax)) {
		foreach($this->itemcountrytax as $k => $v) {
			echo '<div class="row ph-tax-edit-item">';
			echo '<div class="span4 col-sm-4 col-md-4">'.Text::_($v->title).'</div>';
			echo '<div class="span2 col-sm-2 col-md-2">'.PhocacartPrice::cleanPrice($v->tax_rate).'</div>';

			echo '<div class="span2 col-sm-2 col-md-2">';

			echo '<input class="form-control input-sm" type="text" name="jform['.(int)$v->id.'][title]" value="'.htmlspecialchars($v->tc_title).'">';
			echo '<input type="hidden" name="jform['.(int)$v->id.'][tax_id]" value="'.(int)$v->id.'">';
			echo '</div>';

			echo '<div class="span2 col-sm-2 col-md-2"><input class="form-control input-sm" type="text" name="jform['.(int)$v->id.'][alias]" value="'.htmlspecialchars($v->tc_alias).'"></div>';

			// cleanPrice method add 0 to empty values which is wrong in this case as we have:
			// VAT = 0 (valid VAT)
			// VAT = '' (vat not set)
			$tcTaxRate  = '';
			if ($v->tc_tax_rate != '') {
				$tcTaxRate = PhocacartPrice::cleanPrice($v->tc_tax_rate);
			}
			echo '<div class="span2 col-sm-2 col-md-2"><input class="form-control input-sm" type="text" name="jform['.(int)$v->id.'][tax_rate]" value="'.htmlspecialchars($tcTaxRate).'"></div>';
			echo '</div>';

		}
	}
	//echo '</table>';





	echo '<input type="hidden" name="jform[id]" value="'.(int)$this->id.'">';
	echo '<input type="hidden" name="task" value="phocacartedittax.edittax">';
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />';
	echo '<div class="ph-tax-edit-button"><button class="btn btn-success btn-sm ph-btn"><span class="icon-edit"></span> '.Text::_('COM_PHOCACART_SAVE').'</button></div>';

	echo HTMLHelper::_('form.token');
	echo '</form>';
}



echo '<p>&nbsp;</p>';

echo '<form action="'.$link.'" method="post">';
echo '<input type="hidden" name="jform[id]" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="task" value="phocacartedittax.emptyinformation">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<button class="btn btn-primary btn-sm ph-btn"><span class="icon-delete"></span> '.Text::_('COM_PHOCACART_EMPTY_TAX_INFORMATION').'</button>';
echo '</div>';
echo HTMLHelper::_('form.token');
echo '</form>';

	*/
?>
