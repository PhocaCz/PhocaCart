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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
/*
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
HTMLHelper::_('formbehavior.chosen', 'select');
*/

echo '<div id="phEditStockAdvancedBox" class="ph-edit-stock-advanced-box">';

echo '<div class="alert alert-info alert-dismissible fade show" role="alert">'
	. '<ul><li>'.Text::_('COM_PHOCACART_TO_SEE_ALL_ATTRIBUTES_LISTED_CLOSE_WINDOW_SAVE_THE_PRODUCT_FIRST') . '</li>'
	. '<li>'. Text::_('COM_PHOCACART_CHECK_LIST_EVERY_TIME_ATTRIBUTES_CHANGE').'</li>'
	. '<li><b>'. Text::_('COM_PHOCACART_ADVANCED_STOCK_MANAGEMENT').'</b>: '.Text::_('COM_PHOCACART_ONLY_STOCK_VALUES_ARE_ACTIVE').'</li>'
	. '<li><b>'. Text::_('COM_PHOCACART_ADVANCED_STOCK_AND_PRICE_MANAGEMENT').'</b>: '.Text::_('COM_PHOCACART_STOCK_VALUES_AND_PRICE_VALUES_ARE_ACTIVE').'. '.Text::_('COM_PHOCACART_PRICE_VALUES_OVERWRITE_CUSTOMER_GROUP_PRICES').'</li>'
	.'</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="'.Text::_('COM_PHOCACART_CLOSE').'"></button></div>';

$app 		= Factory::getApplication();
$input 		= $app->getInput();
$class		= $this->t['n'] . 'RenderAdminview';
$r 			=  new PhocacartRenderAdminview();
$isModal    = $input->get('layout') == 'modal' ? true : false;
$layout     = $isModal ? 'modal' : 'edit';
$tmpl       = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? 'component' : '';


//phocacartitem-form => adminForm
echo $r->startForm($this->t['o'], $this->t['task'], (int)$this->id, 'adminForm', 'adminForm', '', $layout, $tmpl);

echo '<div class="span12 form-horizontal">';

echo '<div class="tab-pane" id="product_stock">'. "\n";
//echo '<h3>'.Text::_($this->t['l'].'_PRODUCT_STOCK').'</h3>';
$formArray = array ('product_stock');
echo $r->group($this->form, $formArray);
echo '</div>'. "\n";

//echo $r->formInputs($this->t['task']);
echo '<input type="hidden" name="id" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="task" value="phocacarteditstockadvanced.save">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<button class="btn btn-success btn-sm ph-btn pull-right ph-right"><span class="icon-ok ph-icon-white"></span> '.Text::_('COM_PHOCACART_SAVE').'</button>';
echo HTMLHelper::_('form.token');

echo '</div>';

echo $r->endForm();

echo '</div>';// end ph-edit-stock-advanced-box


/*
$formArray = array ('product_stock');


$link		= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditstockadvanced&tmpl=component&id='.(int)$this->id);


echo '<div class="ph-edit-stock-advanced-box">';

echo '<div class="alert alert-info"><button type="button" class="close" data-bs-dismiss="alert">&times;</button>'
	. '<ul><li>'.Text::_('COM_PHOCACART_TO_SEE_ALL_ATTRIBUTES_LISTED_CLOSE_WINDOW_SAVE_THE_PRODUCT_FIRST') . '</li>'
	. '<li>'. Text::_('COM_PHOCACART_CHECK_LIST_EVERY_TIME_ATTRIBUTES_CHANGE').'</li>'
	. '<li><b>'. Text::_('COM_PHOCACART_ADVANCED_STOCK_MANAGEMENT').'</b>: '.Text::_('COM_PHOCACART_ONLY_STOCK_VALUES_ARE_ACTIVE').'</li>'
	. '<li><b>'. Text::_('COM_PHOCACART_ADVANCED_STOCK_AND_PRICE_MANAGEMENT').'</b>: '.Text::_('COM_PHOCACART_STOCK_VALUES_AND_PRICE_VALUES_ARE_ACTIVE').'. '.Text::_('COM_PHOCACART_PRICE_VALUES_OVERWRITE_CUSTOMER_GROUP_PRICES').'</li>'
	.'</ul></div>';

if (!empty($this->t['product'])) {

	echo '<div class="ph-attribute-box">';

	echo '<form action="'.$link.'" method="post">';


	if (!empty($this->t['combinations'])) {
		ksort($this->t['combinations']);
		echo '<table class="ph-attribute-option-box">';

		echo '<tr>';
		echo '<th>'.Text::_('COM_PHOCACART_TITLE').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_ATTRIBUTES').'</th>';
		//echo '<th>'.Text::_('COM_PHOCACART_PRODUCT_KEY').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_IN_STOCK').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_PRICE').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_SKU').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_EAN').'</th>';
		echo '<th>'.Text::_('COM_PHOCACART_IMAGE').'</th>';
		echo '</tr>';


		foreach($this->t['combinations'] as $k => $v) {

			echo '<tr>';
			echo '<td>'.$v['product_title'].'</td>';
			echo '<td>'.$v['title'].'</td>';
			//echo '<td><input type="text" class="input-large" name="jform['.$v['product_key'].'][product_key]" value="'.$v['product_key'].'" /></td>';

			// Set value from database
			$stock = 0;
			if (isset($this->t['combinations_data'][$v['product_key']]['stock'])) {
				$stock = $this->t['combinations_data'][$v['product_key']]['stock'];
			}
			$price = '';
			if (isset($this->t['combinations_data'][$v['product_key']]['price'])) {
				$price = $this->t['combinations_data'][$v['product_key']]['price'];
				$price = PhocacartPrice::cleanPrice($price);
			}
			$sku ='';
			if (isset($this->t['combinations_data'][$v['product_key']]['sku'])) {
				$sku = $this->t['combinations_data'][$v['product_key']]['sku'];
			}
			$ean = '';
			if (isset($this->t['combinations_data'][$v['product_key']]['ean'])) {
				$ean = $this->t['combinations_data'][$v['product_key']]['ean'];
			}
			$image = '';
			if (isset($this->t['combinations_data'][$v['product_key']]['image'])) {
				$image = $this->t['combinations_data'][$v['product_key']]['image'];
			}
			echo '<td><input type="text" class="input-mini" name="jform['.$v['product_key'].'][stock]" value="'.$stock.'" />';


			echo '<input type="hidden" name="jform['.$v['product_key'].'][product_key]" value="'.$v['product_key'].'" />';
			echo '<input type="hidden" name="jform['.$v['product_key'].'][product_id]" value="'.$v['product_id'].'" />';
			echo '<input type="hidden" name="jform['.$v['product_key'].'][attributes]" value="'.PhocacartProduct::getProductKey($v['product_id'], $v['attributes'], 0).'" />';
			echo '</td>';


			echo '<td><input type="text" class="input-mini" name="jform['.$v['product_key'].'][price]" value="'.$price.'" /></td>';
			echo '<td><input type="text" class="input-mini" name="jform['.$v['product_key'].'][sku]" value="'.$sku.'" /></td>';
			echo '<td><input type="text" class="input-mini" name="jform['.$v['product_key'].'][ean]" value="'.$ean.'" /></td>';
			echo '<td><input type="text" class="input-mini" name="jform['.$v['product_key'].'][image]" value="'.$image.'" /></td>';

			echo '</tr>';

		}

		echo '<tr><td colspan="6"></td></tr>';

		echo '<tr>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';

		echo '<td>';
		echo '<input type="hidden" name="id" value="'.(int)$this->t['product']->id.'">';
		echo '<input type="hidden" name="task" value="phocacarteditstockadvanced.save">';
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
		echo '<button class="btn btn-success btn-sm ph-btn"><span class="icon-ok ph-icon-white"></span> '.Text::_('COM_PHOCACART_SAVE').'</button>';
		echo HTMLHelper::_('form.token');


		echo '</tr>';

		echo '</table>';
	}
}*/


?>
