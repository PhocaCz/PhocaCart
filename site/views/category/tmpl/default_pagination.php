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
echo '<div class="pagination ph-pagination">';
if ($this->p->get('show_pagination')) {	
	
	
	$s = 12;
	if ($this->p->get('display_item_ordering')) {
		$s = 6;
		echo '<div class="col-xs-'.$s.' col-sm-'.$s.' col-md-'.$s.' ph-center-pagination">';
		echo JText::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;'.$this->t['ordering'];
		echo '</div>';
		
	}
	
	
	
	
	
	if ($this->p->get('show_pagination_limit')) {
		echo '<div class="col-xs-'.$s.' col-sm-'.$s.' col-md-'.$s.' ph-center-pagination">';
		echo JText::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' .$this->t['pagination']->getLimitBox();
		echo '</div>';
	}
	
	
	
	
	echo '<div class="clearfix"></div>';
	echo '<div class="col-xs-12 col-sm-12 col-md-12 ph-center-pagination">'. $this->t['pagination']->getPagesLinks() . '</div>';
	
	echo '<div class="clearfix"></div>';
	
	echo '<div class="col-xs-12 col-sm-12 col-md-12 ph-center-pagination ph-pagination-mt">';
	echo $this->t['pagination']->getPagesCounter();
	echo '</div>';
	
	echo '<div class="clearfix"></div>';
}
echo '</div>';
echo JHTML::_( 'form.token' );
echo '</form>';
?>