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

echo '<div id="ph-pc-terms-box" class="pc-view pc-terms-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo PhocacartRenderFront::renderHeader(array(Text::_('COM_PHOCACART_TERMS_AND_CONDITIONS')));

echo '<div class="ph-terms-box-in">';
echo $this->t['terms_conditions'];
echo '</div>';

echo '</div>';
?>
