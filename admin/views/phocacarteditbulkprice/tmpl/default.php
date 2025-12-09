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
use Joomla\CMS\Session\Session;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
//HTMLHelper::_('formbehavior.chosen', 'select');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );


$link	= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacartedittax&tmpl=component&id='.(int)$this->id);

if (isset($this->item->id) && (int)$this->item->id > 0 && isset($this->item->title) && $this->item->title != '') {


	echo '<h1 class="ph-modal-header">'.$this->item->title.'</h1>';
	if (isset($this->item->description) && $this->item->description != '') {
		echo '<div>'.$this->item->description.'</div>';
	}

	// Params
	$amount = $this->item->params->get('amount', '');
	$operator = $this->item->params->get('operator', '');
	$calculation_price = $this->item->params->get('calculation_type', '');

	if ($calculation_price == 1) {
		$calculation_price = '%';
	} else {
		$calculation_price = '';
	}

	echo '<div>';
	echo '<b>' . Text::_('COM_PHOCACART_CALCULATION') . "</b>: ".$operator . $amount . $calculation_price ;
	echo '</div>';

	$categoriesA = array();
	if (!empty($this->item->categories)) {
		foreach ($this->item->categories as $k => $v) {

			$categoriesA[] = $v->title;
		}
		echo '<div>';
		echo '<b>' . Text::_('COM_PHOCACART_CATEGORIES') . "</b>: " . implode(', ', $categoriesA);
		echo '<div>';
	}

	echo '<div><b>'.Text::_('COM_PHOCACART_NUMBER_OF_AFFECTED_PRODUCTS').'</b>: '.$this->item->productcount.'</div>';

	if (isset($this->item->status) && (int)$this->item->status == 1) {
		// REVERT
		echo '<form class="form-inline" id="phBulkPriceRevert" action="" method="post">';
		echo '<div class="form-group">';
		//echo '<label for="file_import">'. Text::_('COM_PHOCACART_RUN').':</label>';
		echo '<input class="btn btn-danger" type="submit"  name="submit" value="'. Text::_('COM_PHOCACART_REVERT').'">';

		echo '<input type="hidden" name="id" value="'.(int)$this->item->id.'" />';
		echo '<input type="hidden" name="task" value="phocacartbulkprice.revert" />';
		echo '<input type="hidden" name="token" value="'.Session::getFormToken().'" />';
		echo '</div>';
		echo '</form>';
	} else {
		// RUN
		echo '<form class="form-inline" id="phBulkPriceRun" action="" method="post">';
		echo '<div class="form-group">';
		//echo '<label for="file_import">'. Text::_('COM_PHOCACART_RUN').':</label>';
		echo '<input class="btn btn-success" type="submit"  name="submit" value="'. Text::_('COM_PHOCACART_RUN').'">';

		echo '<input type="hidden" name="id" value="'.(int)$this->item->id.'" />';
		echo '<input type="hidden" name="task" value="phocacartbulkprice.run" />';
		echo '<input type="hidden" name="token" value="'.Session::getFormToken().'" />';
		echo '</div>';
		echo '</form>';
	}





	echo '<div id="phBulkPriceOutputBox" class="phAjaxOutputBox"></div>';



	/*$flag = '';
	if (isset($this->item->code2) && $this->item->code2 != '') {
		$flag = PhocacartCountry::getCountryFlag($this->item->code2, 0, $this->item->image, '20px');
	}

	echo '<h1 class="ph-modal-header">'.$flag .' '.$this->item->title.'</h1>';

	echo '<form action="'.$link.'" method="post">';

	//echo '<table class="ph-tax-edit">';
	echo '<div class="row ph-tax-edit-header">';
	echo '<div class="span4 col-sm-4 col-md-4">'.Text::_('COM_PHOCACART_TAX_NAME').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_TAX_RATE').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_TITLE').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_ALIAS').'</div>';
	echo '<div class="span2 col-sm-2 col-md-2">'. ($this->type == 2 ? Text::_('COM_PHOCACART_TAX_RATE_REGION') : Text::_('COM_PHOCACART_TAX_RATE_COUNTRY')).'</div>';
	echo '</div>';

	if (!empty($this->itemcountrytax)) {
		foreach($this->itemcountrytax as $k => $v) {
			echo '<div class="row ph-tax-edit-item">';
			echo '<div class="span4 col-sm-4 col-md-4">'.Text::_($v->title).'</div>';
			echo '<div class="span2 col-sm-2 col-md-2">'.PhocacartPrice::cleanPrice($v->tax_rate).'</div>';

			echo '<div class="span2 col-sm-2 col-md-2">';

			echo '<input class="form-control input-sm" type="text" name="jform['.(int)$v->id.'][title]" value="'.htmlspecialchars($v->tcr_title).'">';
			echo '<input type="hidden" name="jform['.(int)$v->id.'][tax_id]" value="'.(int)$v->id.'">';
			echo '</div>';

			echo '<div class="span2 col-sm-2 col-md-2"><input class="form-control input-sm" type="text" name="jform['.(int)$v->id.'][alias]" value="'.htmlspecialchars($v->tcr_alias).'"></div>';

			// cleanPrice method add 0 to empty values which is wrong in this case as we have:
			// VAT = 0 (valid VAT)
			// VAT = '' (vat not set)
			$tcTaxRate  = '';
			if ($v->tcr_tax_rate != '') {
				$tcTaxRate = PhocacartPrice::cleanPrice($v->tcr_tax_rate);
			}
			if ($v->tcr_tax_rate == -1) {
				$tcTaxRate = '';// -1 means, it was not active but we still hold the ID of such tax for comparison in reports
			}
			echo '<div class="span2 col-sm-2 col-md-2"><input class="form-control input-sm" type="text" name="jform['.(int)$v->id.'][tax_rate]" value="'.htmlspecialchars($tcTaxRate).'"></div>';
			echo '</div>';

		}
	}
	//echo '</table>';





	echo '<input type="hidden" name="jform[id]" value="'.(int)$this->id.'">';
	echo '<input type="hidden" name="jform[type]" value="'.(int)$this->type.'">';
	echo '<input type="hidden" name="task" value="phocacartedittax.edittax">';
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />';
	echo '<div class="ph-tax-edit-button"><button class="btn btn-success btn-sm ph-btn"><span class="icon-edit"></span> '.Text::_('COM_PHOCACART_SAVE').'</button></div>';

	echo HTMLHelper::_('form.token');
	echo '</form>';*/

}


/*
echo '<p>&nbsp;</p>';

echo '<form action="'.$link.'" method="post">';
echo '<input type="hidden" name="jform[id]" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="jform[type]" value="'.(int)$this->type.'">';
echo '<input type="hidden" name="task" value="phocacartedittax.emptyinformation">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<button class="btn btn-primary btn-sm ph-btn"><span class="icon-delete"></span> '.Text::_('COM_PHOCACART_EMPTY_TAX_INFORMATION').'</button>';
echo '</div>';
echo HTMLHelper::_('form.token');
echo '</form>';
*/

?>
