<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<form action="index.php" method="post" name="adminForm" id="'.$this->t['c'].'info-form">';
echo '<div id="phAdminBox" class="row-fluid ph-admin-box">';
echo '<div class="col-xs-12 col-sm-2 col-md-2 ph-admin-box-menu">'. JHtmlSidebar::render().'</div>';
echo '<div id="j-main-container" class="col-xs-12 col-sm-10 col-md-10 ph-admin-box-content">';

echo '<div style="float:right;margin:10px;">'
	. JHTML::_('image', $this->t['i'] . 'logo-phoca.png', 'Phoca.cz' )
	.'</div>'
	. JHTML::_('image', $this->t['i'] . 'logo.png', 'Phoca.cz')
	.'<h3>'.JText::_($this->t['l'].'_PHOCA_CART').' - '. JText::_($this->t['l'].'_INFORMATION').'</h3>'
	.'<div style="clear:both;"></div>';

echo '<h3>'.  JText::_($this->t['l'].'_HELP').'</h3>';

echo '<p>'
.'<a href="http://www.phoca.cz/phocacart/" target="_blank">Phoca Cart Main Site</a><br />'
.'<a href="http://www.phoca.cz/documentation/" target="_blank">Phoca Cart User Manual</a><br />'
.'<a href="http://www.phoca.cz/forum/" target="_blank">Phoca Cart Forum</a><br />'
.'</p>';

echo '<h3>'.  JText::_($this->t['l'] . '_VERSION').'</h3>'
.'<p>'.  $this->t['version'] .'</p>';

echo '<h3>'.  JText::_($this->t['l'] . '_COPYRIGHT').'</h3>'
.'<p>© 2007 - '.  date("Y"). ' Jan Pavelka</p>'
.'<p><a href="http://www.phoca.cz/" target="_blank">www.phoca.cz</a></p>';

echo '<h3>'.  JText::_($this->t['l'] . '_LICENSE').'</h3>'
.'<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>';

echo '<h3>'.  JText::_($this->t['l'] . '_TRANSLATION').': '. JText::_($this->t['l'] . '_TRANSLATION_LANGUAGE_TAG').'</h3>'
        .'<p>© 2007 - '.  date("Y"). ' '. JText::_($this->t['l'] . '_TRANSLATER'). '</p>'
        .'<p>'.JText::_($this->t['l'] . '_TRANSLATION_SUPPORT_URL').'</p>';


echo '<input type="hidden" name="task" value="" />'
.'<input type="hidden" name="option" value="'.$this->t['o'].'" />'
.'<input type="hidden" name="controller" value="'.$this->t['c'].'info" />';

echo '<p>&nbsp;</p>';

echo '<div style="border-top:1px solid #eee"></div><p>&nbsp;</p>'
.'<div>
<a class="btn btn-large btn-primary" href="http://www.phoca.cz/version/index.php?'.$this->t['c'].'='.  $this->t['version'] .'" target="_blank"><i class="icon-loop icon-white"></i>&nbsp;&nbsp;'.  JText::_($this->t['l'].'_CHECK_FOR_UPDATE') .'</a></div>';

echo '<div style="margin-top:30px;height:39px;background: url(\''.JURI::root(true).'/media/com_'.$this->t['c'].'/images/administrator/line.png\') 100% 0 no-repeat;">&nbsp;</div>';

echo '</div>';
echo '</div>';
echo '</div>';
echo '</form>';
?>