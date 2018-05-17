<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!empty($this->t['categories'])) {
	
	// this function is triggered in js.php (searchPosByCategory())
	echo '<form class="form-inline ph-pos-checkbox-form" action="'.$this->t['linkpos'].'" id="phPosCategory" method="post">';
	echo '<div class="ph-pos-checkbox-box" data-toggle="buttons"  >';// data-toggle="buttons" - changes the standard checkbox to graphical checkbox
	
	foreach ($this->t['categories'] as $k => $v) {
		$active	= '';
		$attrO	= '';
		if (in_array((int)$v->id, $this->t['categoryarray'])) {
			$active	= ' active';
			$attrO	= ' checked="checked"';
		}
		
		echo '<label class="btn phCheckBoxButton phCheckBoxCategory '.$active.'" ><input type="checkbox" '.$attrO.' class="phPosCategoryCheckbox" name="c['.$v->id.']" value="'.$v->id.'" autocomplete="off"  /><span class="glyphicon glyphicon-ok" title="'.htmlspecialchars($v->title).''.'"></span> '.htmlspecialchars($v->title).'</label> ';
		
	}
	
	echo '<input type="hidden" name="type" value="products">';
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />';
	echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
	echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
	echo '<input type="hidden" name="section" value="'.(int)$this->t['section']->id.'" />';
	echo JHtml::_('form.token');
	
	echo '</div>';
	echo '</form>';
}

?>