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

echo '<form id="phItemTopBoxForm" action="'.$this->t['action'].'" method="post" name="phitemstopboxform">'. "\n";
echo '<div class="ph-pagination-top">';

if ($this->p->get('show_pagination_top', 1)) {	
	$sN = 12;
	if ($this->p->get('display_item_ordering_top', 1)) {
		$s 	= 5;
		$sN = $sN - $s;
		echo '<div class="col-xs-12 col-sm-'.$s.' col-md-'.$s.' ph-pag-top-row">';
		echo JText::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;'. str_replace( 'class="inputbox"', 'class="inputbox form-control chosen-select" style="width: 16em"', $this->t['ordering']);
		echo '</div>';
		
	}

	if ($this->p->get('show_pagination_limit_top', 1)) {
		$s = 3;
		$sN = $sN - $s;
		echo '<div class="col-xs-12 col-sm-'.$s.' col-md-'.$s.' ph-pag-top-row">';
		echo JText::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' . str_replace( 'class="inputbox"', 'class="inputbox form-control chosen-select"', $this->t['pagination']->getLimitBox());
		echo '</div>';
	}
	
	
	if ($this->p->get('show_switch_layout_type', 1)) {
		$s = $sN;
	
		echo '<div class="col-xs-12 col-sm-'.$s.' col-md-'.$s.' ph-pag-top-row">';
		echo '<button type="button" class="btn btn-default phItemSwitchLayoutType grid '.$this->t['layouttypeactive'][0].'" data-layouttype="grid"><span class="glyphicon glyphicon glyphicon-th-large"></span></button> ';
		echo '<button type="button" class="btn btn-default phItemSwitchLayoutType gridlist '.$this->t['layouttypeactive'][1].'" data-layouttype="gridlist"><span class="glyphicon glyphicon glyphicon-th-list"></span></button> ';
		echo '<button type="button" class="btn btn-default phItemSwitchLayoutType list '.$this->t['layouttypeactive'][2].'" data-layouttype="list"><span class="glyphicon glyphicon glyphicon-align-justify"></span></button>';
		echo '</div>';
	}
	
	echo '<div class="clearfix"></div>';
}
echo '</div>';
echo '<input type="hidden" name="format" value="raw" />';
echo JHTML::_( 'form.token' );
echo '</form>';
?>