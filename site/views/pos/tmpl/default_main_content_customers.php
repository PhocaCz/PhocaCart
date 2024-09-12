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

echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_CUSTOMERS').'</div>';

if (!empty($this->items)) {

	foreach ($this->items as $v) {
		echo '<div class="'.$this->s['c']['row'].' ph-pos-customer-row">';

		echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm6.md6'].'">';
		echo '<div class="ph-pos-customer-name">'.$v->name.'</div>';
		echo '</div>';

		echo '<div class="'.$this->s['c']['row-item'].' ph-pos-customer-action '.$this->s['c']['col.xs12.sm6.md6'].'">';
		//echo '<form class="form-inline" action="'.$this->t['linkpos'].'">';
		echo '<form action="'.$this->t['linkpos'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form">';
		echo '<input type="hidden" name="task" value="pos.savecustomer">';

		if ((int)$this->t['user']->id == (int)$v->id) {
			echo '<input type="hidden" name="id" value="0">';
		} else {
			echo '<input type="hidden" name="id" value="'.(int)$v->id.'">';
		}
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';

	//	echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
	//	echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
	//	echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';



		//echo '<input type="hidden" name="mainboxdata" value="'.$this->t['mainboxdatabase64'].'" />';
		echo '<input type="hidden" name="redirectsuccess" value="main.content.products" />';
		echo '<input type="hidden" name="redirecterror" value="main.content.customers" />';
		echo HTMLHelper::_('form.token');

		if ((int)$this->t['user']->id == (int)$v->id) {
			echo '<button class="'.$this->s['c']['btn.btn-danger'].' editMainContent">'.Text::_('COM_PHOCACART_DESELECT').'</button>';
		} else {
			echo '<button class="'.$this->s['c']['btn.btn-success'].' editMainContent">'.Text::_('COM_PHOCACART_SELECT').'</button>';
		}
		echo '</form>';

		echo '</div>';// end row item
		echo '</div>';// end row
	}
} else {
	echo '<div class="ph-pos-no-items">'.Text::_('COM_PHOCACART_NO_CUSTOMER_FOUND').'</div>';
}

//echo $this->loadTemplate('pagination');


echo '<form action="'.$this->t['action'].'" method="post" name="adminForm" id="phPosPaginationBox">'. "\n";

if (!empty($this->items)) {

	echo '<div class="'.$this->s['c']['row'].' ph-pagination">';
	//if ($this->p->get('show_pagination')) {
	$col = 12;
	//if ($this->p->get('display_item_ordering')) {
		$col = 7;
		echo '<div class="'.$this->s['c']["col.xs12.sm{$col}.md{$col}"].' ph-center-pagination">';
		echo Text::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;'. str_replace( 'class="form-control"', 'class="'.$this->s['c']['inputbox'].' '.$this->s['c']['form-control'].' chosen-select" style="width: 16em"', $this->t['ordering']);
		echo '</div>';
	//}

	//if ($this->p->get('show_pagination_limit')) {
		$col = 5;
		echo '<div class="'.$this->s['c']["col.xs12.sm{$col}.md{$col}"].' ph-center-pagination">';
		echo Text::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' . str_replace( 'class="form-control"', 'class="'.$this->s['c']['inputbox'].' '.$this->s['c']['form-control'].' chosen-select"', $this->t['pagination']->getLimitBox(1));
		echo '</div>';
	//}

	echo '<div class="ph-cb"></div>';
	echo '<div class="col-xs-12 col-sm-12 col-md-12 ph-center-pagination pagination  phPaginationBox">'. str_replace( 'class="form-control"', 'class="'.$this->s['c']['inputbox'].' '.$this->s['c']['form-control'].' chosen-select"', $this->t['pagination']->getPagesLinks()) . '</div>';

	echo '<div class="ph-cb"></div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-center-pagination ph-pagination-mt pagination">';

	if (!empty($this->t['pagination']->getPagesCounter())) {
		echo str_replace('class="form-control"', 'class="' . $this->s['c']['inputbox'] . ' ' . $this->s['c']['form-control'] . ' chosen-select"', $this->t['pagination']->getPagesCounter());
	}

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

echo HTMLHelper::_( 'form.token' );
echo '</form>';
?>
