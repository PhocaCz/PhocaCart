<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
echo '<div id="ph-pc-payment-box" class="pc-payment-view'.$this->p->get( 'pageclass_sfx' ).'">';


echo '<h1>';
if ($this->p->get('show_page_heading')) { 
	 $this->escape($this->p->get('page_heading'));
}
echo JText::_('COM_PHOCACART_PAYMENT');
echo '</h1>';




echo $this->t['o'];

echo '</div>';// end ph-pc-payment-box
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>