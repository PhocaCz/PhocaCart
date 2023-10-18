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
$d  = $displayData;


echo '<div class="ph-cb"></div>';

echo '<form id="phItemTopBoxForm" action="'.$d['t']['action'].'" method="post" name="phitemstopboxform">'. "\n";
echo '<div class="'.$d['s']['c']['row'].' ph-pagination-top">';

if ($d['t']['show_pagination_top']) {

    echo '<div class="'.$d['s']['c']['col.xs12.sm5.md5'].' ph-pag-top-row">';
	if ($d['t']['display_item_ordering_top']) {

		$ordering = str_replace( 'class="form-control"', 'class="'.$d['s']['c']['inputbox.form-select'].' chosen-select" style="width: 16em;" aria-label="'.Text::_('COM_PHOCACART_ORDER_FRONT').'"', $d['t']['ordering']);
		$ordering = str_replace( 'id="itemordering"', 'id="itemorderingtop" aria-label="'.Text::_('COM_PHOCACART_ORDER_FRONT').'"', $ordering);// possible two the same ID
		echo $d['t']['display_pagination_labels'] == 1 ? Text::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;' : '';
		echo $ordering;

	}
    echo '</div>';

    echo '<div class="'.$d['s']['c']['col.xs12.sm3.md3'].' ph-pag-top-row">';
	if ($d['t']['show_pagination_limit_top']) {

		$limit = str_replace( 'class="form-control"', 'class="'.$d['s']['c']['inputbox.form-select'].' chosen-select" aria-label="'.Text::_('COM_PHOCACART_DISPLAY_NUM').'"', $d['t']['pagination']->getLimitBox());
		$limit = str_replace( 'id="limit"', 'id="limittop" aria-label="'.Text::_('COM_PHOCACART_DISPLAY_NUM').'"', $limit);// possible two the same ID
		echo $d['t']['display_pagination_labels'] == 1 ? Text::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' : '';
		echo $limit;

	}
    echo '</div>';


    echo '<div class="'.$d['s']['c']['col.xs12.sm4.md4'].' ph-pag-top-row">';
	if ($d['t']['show_switch_layout_type']) {

		echo '<button type="button" aria-label="'.Text::_('COM_PHOCACART_GRID').'" class="'.$d['s']['c']['btn.btn-default'].' phItemSwitchLayoutType grid '.$d['t']['layouttypeactive'][0].'" data-layouttype="grid">';
		//echo '<span class="'.$d['s']['i']['grid'].'"></span>';
		echo PhocacartRenderIcon::icon($d['s']['i']['grid']);
		echo '</button> ';

		echo '<button type="button" aria-label="'.Text::_('COM_PHOCACART_GRID_LIST').'" class="'.$d['s']['c']['btn.btn-default'].' phItemSwitchLayoutType gridlist '.$d['t']['layouttypeactive'][1].'" data-layouttype="gridlist">';
		//echo '<span class="'.$d['s']['i']['gridlist'].'"></span>';
		echo PhocacartRenderIcon::icon($d['s']['i']['gridlist']);
		echo '</button> ';

		echo '<button type="button" aria-label="'.Text::_('COM_PHOCACART_LIST').'" class="'.$d['s']['c']['btn.btn-default'].' phItemSwitchLayoutType list '.$d['t']['layouttypeactive'][2].'" data-layouttype="list">';
		//echo '<span class="'.$d['s']['i']['list'].'"></span>';
		echo PhocacartRenderIcon::icon($d['s']['i']['list']);
		echo '</button>';

	}
    echo '</div>';

    echo '<div class="ph-cb"></div>';
}
echo '</div>';
echo '<input type="hidden" name="format" value="raw" />';
echo HTMLHelper::_( 'form.token' );
echo '</form>';
?>
