<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$r = $this->r;
echo $r->startCp();

echo '<div class="ph-box-info">';

echo '<div style="float:right;margin:10px;">' . HTMLHelper::_('image', $this->t['i'] . 'logo-phoca.png', 'Phoca.cz' ) .'</div>'
	. '<div class="ph-cpanel-logo">'.HTMLHelper::_('image', $this->t['i'] . 'logo-'.str_replace('phoca', 'phoca-', $this->t['c']).'.png', 'Phoca.cz') . '</div>'
	.'<h3>'.Text::_($this->t['component_head']).' - '. Text::_($this->t['l'].'_INFORMATION').'</h3>'
	.'<div style="clear:both;"></div>';

echo '<h3>'.  Text::_($this->t['l'].'_HELP').'</h3>';

echo '<div>';
if (!empty($this->t['component_links'])) {
	foreach ($this->t['component_links'] as $k => $v) {
	    echo '<div><a href="'.$v[1].'" target="_blank">'.$v[0].'</a></div>';
	}
}
echo '</div>';

echo '<h3>'.  Text::_($this->t['l'] . '_VERSION').'</h3>'
.'<p>'.  $this->t['version'] .'</p>';

echo '<h3>'.  Text::_($this->t['l'] . '_COPYRIGHT').'</h3>'
.'<p>© 2007 - '.  date("Y"). ' Jan Pavelka</p>'
.'<p><a href="https://www.phoca.cz/" target="_blank">www.phoca.cz</a></p>';

echo '<h3>'.  Text::_($this->t['l'] . '_LICENSE').'</h3>'
.'<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>';

echo '<h3>'.  Text::_($this->t['l'] . '_TRANSLATION').': '. Text::_($this->t['l'] . '_TRANSLATION_LANGUAGE_TAG').'</h3>'
        .'<p>© 2007 - '.  date("Y"). ' '. Text::_($this->t['l'] . '_TRANSLATER'). '</p>'
        .'<p>'.Text::_($this->t['l'] . '_TRANSLATION_SUPPORT_URL').'</p>';

echo '<input type="hidden" name="task" value="" />'
.'<input type="hidden" name="option" value="'.$this->t['o'].'" />'
.'<input type="hidden" name="controller" value="'.$this->t['c'].'info" />';

echo HTMLHelper::_('image', $this->t['i'] . 'logo.png', 'Phoca.cz');

echo '<p>&nbsp;</p>';


$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->addInlineStyle('

.upBox {
    display: flex;
    flex-wrap: wrap;
    margin-top:1em;
    margin-bottom: 2em;
}

.upItemText {
    margin-bottom: 1em;
}

.upItem {
    padding: 1em;
    text-align: center;
    width: calc(50% - 0.4em);
    margin: 0.2em;
    border-radius: 0.3em;
}

.upItemD {
    background: #F5D042;
    color: #000;
    border: 2px solid #F5D042;

}
.upItemPh {
    background: rgba(255,255,255,0.7);
    color: #000;
    border: 2px solid #000;
}
.upItemDoc {
    background: rgba(255,255,255,0.7);
    color: #000;
    border: 2px solid #000;
}
.upItemJ {
    background: rgba(255,255,255,0.7);
    color: #000;
    border: 2px solid #000;
}

a.upItemLink {
    padding: 0.5em 1em;
    border-radius: 9999px;
    margin: 1em;
    display: inline-block;
}

a.upItemLink::before {
    content: none;
}
.upItemPh a.upItemLink {
    background: #000;
    color: #fff;
}
.upItemDoc a.upItemLink {
    background: #000;
    color: #fff;
}
.upItemJ a.upItemLink {
    background: #000;
    color: #fff;
}

.phTemplateItems {
    display: flex;
    flex-wrap: wrap;
    margin-top:1em;
    margin-bottom: 2em;
}

.phTemplateItem {
    padding: 1em;
    text-align: center;
    width: calc(33% - 0.4em);
    margin: 0.2em;
    border-radius: 0.3em;
}

.phTemplateItem img{
    width: 100%;
    height: auto;
}

.phTemplateItemsInfo {
    margin: 1em auto;
}
.phTemplateItemTitle {
    font-size: small;
}
.phTemplateItem a::before {
    content: none;
}
');

$upEL = 'https://extensions.joomla.org/extension/phoca-cart/';
$upE = 'Phoca Cart';

$o = '<div class="upBox">';

$o .=  '<div class="upItem upItemD">';
$o .=  '<div class="upItemText">'.Text::_('COM_PHOCACART_ADMIN_PROJECT_INFO1'). '</div>';
$o .=  '<form action="https://www.paypal.com/donate" method="post" target="_top">';
$o .=  '<input type="hidden" name="hosted_button_id" value="ZVPH25SQ2DDBY" />';
$o .=  '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />';
$o .=  '<img alt="" border="0" src="https://www.paypal.com/en_CZ/i/scr/pixel.gif" width="1" height="1" />';
$o .=  '</form>';
$o .=  '</div>';

$o .=  '<div class="upItem upItemJ">';
$o .=  '<div class="upItemText">'.Text::_('COM_PHOCACART_ADMIN_PROJECT_INFO2'). '</div>';
$o .=  '<a class="upItemLink" target="_blank" href="'. $upEL.'">'. $upE.' (JED '.Text::_('COM_PHOCACART_WEBSITE').')</a>';
$o .=  '</form>';
$o .=  '</div>';

$o .=  '<div class="upItem upItemDoc">';
$o .=  '<div class="upItemText">'.Text::_('COM_PHOCACART_ADMIN_PROJECT_INFO3'). '</div>';
$o .=  '<a class="upItemLink" target="_blank" href="https://www.phoca.cz/documentation">Phoca documentation '.Text::_('COM_PHOCACART_WEBSITE').'</a>';
$o .=  '<div class="upItemText">'.Text::_('COM_PHOCACART_ADMIN_PROJECT_INFO5'). '</div>';
$o .=  '<a class="upItemLink" target="_blank" href="https://www.phoca.cz/forum">Phoca forum '.Text::_('COM_PHOCACART_WEBSITE').'</a>';
$o .=  '</div>';

$o .=  '<div class="upItem upItemPh">';
$o .=  '<div class="upItemText">'.Text::_('COM_PHOCACART_ADMIN_PROJECT_INFO4'). '</div>';
$o .=  '<a class="upItemLink" target="_blank" href="https://www.phoca.cz">Phoca '.Text::_('COM_PHOCACART_WEBSITE').'</a>';
$o .=  '</div>';

$o .=  '</div>';


$pathImg = 'media/com_phocacart/images/administrator/';

$o .= '<div class="phTemplateItemsBox">';
$o .= '<div class="phTemplateItemsInfo">'.Text::_('COM_PHOCACART_ADMIN_TEMPLATE_INFO1'). '</div>';

$o .= '<div class="phTemplateItemsInfo">'.Text::_('COM_PHOCACART_ADMIN_TEMPLATE_INFO2'). '</div>';

$o .= '<div class="phTemplateItems">';

$o .= '<div class="phTemplateItem"><a href="https://www.phoca.cz/phocacart-extensions/3-templates" target="_blank">'.HTMLHelper::_('image', $pathImg . 'thumb-template-fashion.jpg', 'Phoca Cart - Cassiopeia - Fashion Child Template' ) .'</a><div class="phTemplateItemTitle">Cassiopeia Child Template - Phoca Cart <b>Fashion</b></div></div>';

$o .= '<div class="phTemplateItem"><a href="https://www.phoca.cz/phocacart-extensions/3-templates" target="_blank">'.HTMLHelper::_('image', $pathImg . 'thumb-template-food.jpg', 'Phoca Cart - Cassiopeia - Food Child Template' ) .'</a><div class="phTemplateItemTitle">Cassiopeia Child Template - Phoca Cart <b>Food</b></div></div>';

$o .= '<div class="phTemplateItem"><a href="https://www.phoca.cz/phocacart-extensions/3-templates" target="_blank">'.HTMLHelper::_('image', $pathImg . 'thumb-template-furniture.jpg', 'Phoca Cart - Cassiopeia - Furniture Child Template' ) .'</a><div class="phTemplateItemTitle">Cassiopeia Child Template - Phoca Cart <b>Furniture</b></div></div>';

$o .= '</div>';


$o .= '<div class="phTemplateItemsInfo">'.Text::_('COM_PHOCACART_ADMIN_TEMPLATE_INFO3'). '</div>';

$o .= '<div class="phTemplateItems">';

$o .= '<div class="phTemplateItem"><a href="https://www.linelab.org/support/lbox-free-joomla-4-template" target="_blank">'.HTMLHelper::_('image', $pathImg . 'thumb-template-lbox.jpg', ' LBOX - Free Joomla Template ' ) .'</a><div class="phTemplateItemTitle">LBOX - Free Joomla Template </div></div>';

$o .= '<div class="phTemplateItem"><a href="https://www.hotjoomlatemplates.com/joomla-templates/electronics-shop" target="_blank">'.HTMLHelper::_('image', $pathImg . 'thumb-template-ht.jpg', 'Hot Themes - Electronics Shop Joomla Template' ) .'</a><div class="phTemplateItemTitle">Hot Themes - Electronics Shop Joomla Template</div></div>';

$o .= '<div class="phTemplateItem"><a href="https://yootheme.com/joomla-templates" target="_blank">'.HTMLHelper::_('image', $pathImg . 'thumb-template-yootheme.jpg', 'YOOtheme - Premium Joomla Templates' ) .'</a><div class="phTemplateItemTitle">YOOtheme - Premium Joomla Templates</div></div>';
$o .= '</div>';

$o .= '</div>';

$o .= '</div>';// end phTemplateItemsBox

echo $o;


echo '<div class="ph-cp-hr"></div>';

echo '<div class="btn-group">';

echo '<a class="btn btn-large btn-primary ph-cp-btn-update" href="https://www.phoca.cz/version/index.php?'.$this->t['c'].'='.  $this->t['version'] .'" target="_blank"><i class="icon-loop icon-white"></i>&nbsp;&nbsp;'.  Text::_($this->t['l'].'_CHECK_FOR_UPDATE') .'</a></div>';


echo '<div class="clearfix"></div>';

echo '<div style="margin-top:30px;height:39px;background: url(\''.Uri::root(true).'/media/com_'.$this->t['c'].'/images/administrator/line.png\') 100% 0 no-repeat;">&nbsp;</div>';

echo '</div>';


echo '</div>';
echo $r->endCp();
