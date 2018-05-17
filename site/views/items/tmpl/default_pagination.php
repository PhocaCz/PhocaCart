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

echo '<div class="clearfix"></div>';
echo '<form action="'.$this->t['action'].'" method="post" name="adminForm">'. "\n";
echo '<div class="ph-pagination">';
if ($this->p->get('show_pagination')) {	
	
	
	$s = 12;
	if ($this->p->get('display_item_ordering')) {
		$s = 7;
		echo '<div class="col-xs-12 col-sm-'.$s.' col-md-'.$s.' ph-center-pagination">';
		echo JText::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;'. str_replace( 'class="inputbox"', 'class="inputbox form-control chosen-select" style="width: 16em"', $this->t['ordering']);
		echo '</div>';
		
	}
	
	
	
	
	
	if ($this->p->get('show_pagination_limit')) {
		$s = 5;
		echo '<div class="col-xs-12 col-sm-'.$s.' col-md-'.$s.' ph-center-pagination">';
		echo JText::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' . str_replace( 'class="inputbox"', 'class="inputbox form-control chosen-select"', $this->t['pagination']->getLimitBox());
		echo '</div>';
	}
	
	
	
	
	echo '<div class="clearfix"></div>';
	echo '<div class="col-xs-12 col-sm-12 col-md-12 ph-center-pagination pagination  phPaginationBox">'. str_replace( 'class="inputbox"', 'class="inputbox form-control chosen-select"', $this->t['pagination']->getPagesLinks()) . '</div>';
	
	echo '<div class="clearfix"></div>';
	
	echo '<div class="col-xs-12 col-sm-12 col-md-12 ph-center-pagination ph-pagination-mt pagination">';
	echo str_replace( 'class="inputbox"', 'class="inputbox form-control chosen-select"', $this->t['pagination']->getPagesCounter());
	echo '</div>';
	
	echo '<div class="clearfix"></div>';
}
echo '</div>';
if ($this->p->get('ajax_pagination_category', 0) == 1) {
	echo '<input type="hidden" name="format" value="raw" />';
}
echo JHtml::_( 'form.token' );
echo '</form>';
?>