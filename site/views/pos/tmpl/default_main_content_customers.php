<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_CUSTOMERS').'</div>';

if (!empty($this->items)) {

	foreach ($this->items as $v) {
		echo '<div class="row ph-pos-customer-row">';
		
		echo '<div class="row-item col-sx-12 col-sm-6 col-md-6">';
		echo '<div class="ph-pos-customer-name">'.$v->name.'</div>';
		echo '</div>';
		
		echo '<div class="row-item ph-pos-customer-action col-sx-12 col-sm-6 col-md-6">';
		echo '<form class="form-inline" action="'.$this->t['linkpos'].'" method="post">';
		echo '<input type="hidden" name="task" value="pos.savecustomer">';

		if ((int)$this->t['user']->id == (int)$v->id) {
			echo '<input type="hidden" name="id" value="0">';
		} else {
			echo '<input type="hidden" name="id" value="'.(int)$v->id.'">';
		}
		echo '<input type="hidden" name="tmpl" value="component" />';
		echo '<input type="hidden" name="option" value="com_phocacart" />';
	
		echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
		echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
		echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';
		//echo '<input type="hidden" name="mainboxdata" value="'.$this->t['mainboxdatabase64'].'" />';
		echo '<input type="hidden" name="redirectsuccess" value="main.content.products" />';
		echo '<input type="hidden" name="redirecterror" value="main.content.products" />';
		echo JHtml::_('form.token');
		
		if ((int)$this->t['user']->id == (int)$v->id) {
			echo '<button class="btn btn-danger editMainContent">'.JText::_('COM_PHOCACART_DESELECT').'</button>';
		} else {
			echo '<button class="btn btn-success editMainContent">'.JText::_('COM_PHOCACART_SELECT').'</button>';
		}
		echo '</form>';
		
		echo '</div>';// end row item
		echo '</div>';// end row
	}
} else {
	echo '<div class="ph-pos-no-items">'.JText::_('COM_PHOCACART_NO_CUSTOMER_FOUND').'</div>';
}

echo $this->loadTemplate('pagination');

?>