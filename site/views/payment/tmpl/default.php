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
echo '<div id="ph-pc-payment-box" class="pc-view pc-payment-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo PhocacartRenderFront::renderHeader(array(Text::_('COM_PHOCACART_PAYMENT')));

echo $this->t['o'];

echo '</div>';// end ph-pc-payment-box
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>
