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

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
HTMLHelper::_('formbehavior.chosen', 'select');


//- $link		= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacarteditproductpricehistory&tmpl=component&id='.(int)$this->id);


echo '<div id="phAdminEditPopup" class="ph-edit-price-history-box">';

/*
echo '<div class="alert alert-info"><button type="button" class="close" data-bs-dismiss="alert">&times;</button>'
	. '<ul><li>'.Text::_('COM_PHOCACART_TO_SEE_ALL_CUSTOMER_GROUPS_LISTED_CLOSE_WINDOW_SAVE_THE_PRODUCT_FIRST') . '</li>'
	.'</ul></div>';
*/


//- echo '<div class="ph-product-price-history-box">';

//- echo '<form action="'.$link.'" method="post">';


	/*
echo '<div class="ph-product-price-history-box">';

echo '<div class="row ph-row">'."\n";
echo '<div class="col-xs-12 col-sm-3 col-md-3">'.Text::_('COM_PHOCACART_DATE').'</div>';
echo '<div class="col-xs-12 col-sm-2 col-md-2">'.Text::_('COM_PHOCACART_PRICE').'</div>';
echo '<div class="col-xs-12 col-sm-1 col-md-1"></div>';
echo '<div class="col-xs-12 col-sm-6 col-md-6"></div>';
echo '</div>';
echo '<div class="ph-cb"></div>'."\n";


$r 						= new PhocacartRenderAdminview();

if (!empty($this->t['history'])) {
	$i = 0;
	foreach($this->t['history'] as $k => $v) {
		echo $r->additionalPricehistoryRow((int)$i, (int)$v['id'], $v['price'], $v['date'], $this->id, 0);
		$i++;
	}

	$newRow = $r->additionalPricehistoryRow('\' + phRowCountPricehistory +  \'', '', '', '', $this->id, 1);
	$newRow = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newRow);
	PhocacartRenderAdminjs::renderJsManageRowPricehistory($i, $newRow);
} else {
	$newRow = $r->additionalPricehistoryRow('\' + phRowCountPricehistory +  \'', '', '', '', $this->id, 1);
	$newRow = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newRow);
	PhocacartRenderAdminjs::renderJsManageRowPricehistory(0, $newRow);

}
echo $r->addRowButton(Text::_('COM_PHOCACART_ADD_PRICE'), 'pricehistory');
	//echo '</td></tr>';



echo '<div class="col-xs-12 col-sm-3 col-md-3"></div>';

echo '<div class="col-xs-12 col-sm-2 col-md-2">';
echo '<input type="hidden" name="id" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="task" value="phocacarteditproductpricehistory.save">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<button class="btn btn-success btn-sm ph-btn"><span class="icon-ok ph-icon-white"></span> '.Text::_('COM_PHOCACART_SAVE').'</button>';
echo HTMLHelper::_('form.token');
echo '</div>';

echo '<div class="col-xs-12 col-sm-1 col-md-1"></div>';
echo '<div class="col-xs-12 col-sm-6 col-md-6"></div>';


*/


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

echo '<div class="tab-pane" id="price_history">'. "\n";
echo '<h3>'.Text::_($this->t['l'].'_PRICE_HISTORY').'</h3>';
$formArray = array ('price_history');
echo $r->group($this->form, $formArray);
echo '</div>'. "\n";

//echo $r->formInputs($this->t['task']);
echo '<input type="hidden" name="id" value="'.(int)$this->id.'">';
echo '<input type="hidden" name="task" value="phocacarteditproductpricehistory.save">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<button class="btn btn-success btn-sm ph-btn pull-right ph-right"><span class="icon-ok ph-icon-white"></span> '.Text::_('COM_PHOCACART_SAVE').'</button>';
echo HTMLHelper::_('form.token');

echo '</div>';

echo $r->endForm();

echo '</div>';// end ph-product-price-history-box
?>
