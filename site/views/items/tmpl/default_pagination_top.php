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

echo '<form id="phItemTopBoxForm" action="'.$this->t['action'].'" method="post" name="phitemstopboxform">'. "\n";
echo '<div class="'.$this->s['c']['row'].' ph-pagination-top">';

if ($this->p->get('show_pagination_top', 1)) {

    echo '<div class="'.$this->s['c']['col.xs12.sm5.md5'].' ph-pag-top-row">';
	if ($this->p->get('display_item_ordering_top', 1)) {

		$ordering = str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox.form-control'].' chosen-select" style="width: 16em;"', $this->t['ordering']);
		$ordering = str_replace( 'id="itemordering"', 'id="itemorderingtop"', $ordering);// possible two the same ID
		echo JText::_('COM_PHOCACART_ORDER_FRONT') .':&nbsp;'. $ordering;

	}
    echo '</div>';

    echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].' ph-pag-top-row">';
	if ($this->p->get('show_pagination_limit_top', 1)) {

		$limit = str_replace( 'class="inputbox"', 'class="'.$this->s['c']['inputbox.form-control'].' chosen-select"', $this->t['pagination']->getLimitBox());
		$limit = str_replace( 'id="limit"', 'id="limittop"', $limit);// possible two the same ID
		echo JText::_('COM_PHOCACART_DISPLAY_NUM') .':&nbsp;' . $limit;

	}
    echo '</div>';


    echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-pag-top-row">';
	if ($this->p->get('show_switch_layout_type', 1)) {

		echo '<button type="button" class="'.$this->s['c']['btn.btn-default'].' phItemSwitchLayoutType grid '.$this->t['layouttypeactive'][0].'" data-layouttype="grid"><span class="'.$this->s['i']['grid'].'"></span></button> ';
		echo '<button type="button" class="'.$this->s['c']['btn.btn-default'].' phItemSwitchLayoutType gridlist '.$this->t['layouttypeactive'][1].'" data-layouttype="gridlist"><span class="'.$this->s['i']['gridlist'].'"></span></button> ';
		echo '<button type="button" class="'.$this->s['c']['btn.btn-default'].' phItemSwitchLayoutType list '.$this->t['layouttypeactive'][2].'" data-layouttype="list"><span class="'.$this->s['i']['list'].'"></span></button>';

	}
    echo '</div>';

    echo '<div class="ph-cb"></div>';
}
echo '</div>';
echo '<input type="hidden" name="format" value="raw" />';
echo JHtml::_( 'form.token' );
echo '</form>';
?>
