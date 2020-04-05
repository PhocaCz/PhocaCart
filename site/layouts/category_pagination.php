<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d  = $displayData;

echo '<div class="ph-cb"></div>';
echo '<form action="'.$d['t']['action'].'" method="post" name="adminForm">'. "\n";
echo '<div class="'.$d['s']['c']['row'].' ph-pagination">';
if ($d['t']['show_pagination']) {

    echo '<div class="'.$d['s']['c']['col.xs12.sm6.md6'].' ph-center-pagination">';
	if ($d['t']['display_item_ordering']) {
		echo $d['t']['display_pagination_labels']  == 1 ? JText::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;' : '';
        echo str_replace( 'class="inputbox"', 'class="'.$d['s']['c']['inputbox.form-control'].' chosen-select" style="width: 16em" aria-label="'.JText::_('COM_PHOCACART_ORDER_FRONT').'"', $d['t']['ordering']);
	}
	echo '</div>';

    echo '<div class="'.$d['s']['c']['col.xs12.sm6.md6'].' ph-center-pagination">';
	if ($d['t']['show_pagination_limit'] ) {
		echo $d['t']['display_pagination_labels'] == 1 ? JText::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' : '';
        echo str_replace( 'class="inputbox"', 'class="'.$d['s']['c']['inputbox.form-control'].' chosen-select" aria-label="'.JText::_('COM_PHOCACART_DISPLAY_NUM').'"', $d['t']['pagination']->getLimitBox());
	}
    echo '</div>';

	echo '<div class="ph-cb"></div>';

	// .phPaginationBox used for AJAX pagination
	echo '<div class="'.$d['s']['c']['col.xs12.sm12.md12'].' ph-center-pagination pagination phPaginationBox">'. str_replace( 'class="inputbox"', 'class="'.$d['s']['c']['inputbox.form-control'].' chosen-select"', $d['t']['pagination']->getPagesLinks()) . '</div>';

    echo '<div class="ph-cb"></div>';

	echo '<div class="'.$d['s']['c']['col.xs12.sm12.md12'].' ph-center-pagination ph-pagination-mt pagination">';
	echo str_replace( 'class="inputbox"', 'class="'.$d['s']['c']['inputbox.form-control'].' chosen-select"', $d['t']['pagination']->getPagesCounter());
	echo '</div>';

    echo '<div class="ph-cb"></div>';
}
echo '</div>';
if ($d['t']['ajax_pagination_category'] == 1) {
	echo '<input type="hidden" name="format" value="raw" />';
}
echo JHtml::_( 'form.token' );
echo '</form>';

?>
