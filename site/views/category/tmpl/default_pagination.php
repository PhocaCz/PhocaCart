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
echo '<form action="'.$this->t['action'].'" method="post" name="adminForm">'. "\n";
echo '<div class="'.$this->s['c']['row'].' ph-pagination">';
if ($this->p->get('show_pagination')) {

    echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-center-pagination">';
	if ($this->p->get('display_item_ordering')) {
		echo JText::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;'. str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox.form-control'].' chosen-select" style="width: 16em"', $this->t['ordering']);
	}
	echo '</div>';

    echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-center-pagination">';
	if ($this->p->get('show_pagination_limit')) {
		echo JText::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' . str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox.form-control'].' chosen-select"', $this->t['pagination']->getLimitBox());
	}
    echo '</div>';

	echo '<div class="ph-cb"></div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-center-pagination pagination">'. str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox.form-control'].' chosen-select"', $this->t['pagination']->getPagesLinks()) . '</div>';

    echo '<div class="ph-cb"></div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-center-pagination ph-pagination-mt pagination">';
	echo str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox.form-control'].' chosen-select"', $this->t['pagination']->getPagesCounter());
	echo '</div>';

    echo '<div class="ph-cb"></div>';
}
echo '</div>';
if ($this->p->get('ajax_pagination_category', 0) == 1) {
	echo '<input type="hidden" name="format" value="raw" />';
}
echo JHtml::_( 'form.token' );
echo '</form>';
?>
