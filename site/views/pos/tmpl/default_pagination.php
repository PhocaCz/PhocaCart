<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$this->t['action'] = str_replace('&amp;', '&', $this->t['action']);
$this->t['action'] = htmlspecialchars($this->t['action']);


echo '<div class="ph-cb"></div>';


echo '<form action="'.$this->t['action'].'" method="post" name="adminForm" id="phPosPaginationBox">'. "\n";

if (!empty($this->items)) {

	echo '<div class="'.$this->s['c']['row'].' ph-pagination">';
	//if ($this->p->get('show_pagination')) {
	$col = 12;
	//if ($this->p->get('display_item_ordering')) {
		$col = 7;
		echo '<div class="'.$this->s['c']["col.xs12.sm{$col}.md{$col}"].' ph-center-pagination">';
		echo JText::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;'. str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox'].' '.$this->s['c']['form-control'].' chosen-select" style="width: 16em"', $this->t['ordering']);
		echo '</div>';
	//}

	//if ($this->p->get('show_pagination_limit')) {
		$col = 5;
		echo '<div class="'.$this->s['c']["col.xs12.sm{$col}.md{$col}"].' ph-center-pagination">';
		echo JText::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' . str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox'].' '.$this->s['c']['form-control'].' chosen-select"', $this->t['pagination']->getLimitBox(1));
		echo '</div>';
	//}

	echo '<div class="ph-cb"></div>';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-center-pagination pagination  phPaginationBox">'. str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox'].' '.$this->s['c']['form-control'].' chosen-select"', $this->t['pagination']->getPagesLinks()) . '</div>';

	echo '<div class="ph-cb"></div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-center-pagination ph-pagination-mt pagination">';
	echo str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox'].' '.$this->s['c']['form-control'].' chosen-select"', $this->t['pagination']->getPagesCounter());
	echo '</div>';

	echo '<div class="ph-cb"></div>';
	//}
	echo '</div>';
}

//if ($this->p->get('ajax_pagination_category', 0) == 1) {
	echo '<input type="hidden" name="format" value="raw" />';
	echo '<input type="hidden" name="page" value="'.$this->t['page'].'" />';
	echo '<input type="hidden" name="ticketid" value="'.$this->t['ticket']->id.'" />';
	echo '<input type="hidden" name="unitid" value="'.$this->t['unit']->id.'" />';
	echo '<input type="hidden" name="sectionid" value="'.$this->t['section']->id.'" />';
	echo '<input type="hidden" name="date" value="'.$this->state->get('date').'" />';
//}

echo JHtml::_( 'form.token' );
echo '</form>';

?>
