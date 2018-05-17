<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div class="ph-pos-vendor-title">';
	
echo '<div class="dropdown">';

echo '<button class="btn btn-info dropdown-toggle" type="button" id="phdropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
if (isset($this->t['vendor']->image) && $this->t['vendor']->image != '') {
	echo '<div class="ph-img-inside-btn">' . PhocacartImage::getImage($this->t['vendor']->image) . '</div>';
}
echo $this->t['vendor']->name;
echo '</button>';


echo '<div class="dropdown-menu ph-vendor-dropdown" aria-labelledby="phdropdownMenuButton">';
//echo '<a class="dropdown-item btn btn-danger ph-pos-btn-dropdown" href="#">';

// CURRNECY
echo '<div class="ph-dropdown-header">'.JText::_('COM_PHOCACART_CURRENCY').'</div>';
echo $this->loadTemplate('currency');


// ORDERS
echo '<div class="ph-dropdown-header">'.JText::_('COM_PHOCACART_ORDERS').'</div>';
echo '<form action="'.$this->t['action'].'" method="post">';
//echo '<input type="hidden" name="limitstart" value="0" />';//We use more pages, reset for new date, new customer, new products
//echo '<input type="hidden" name="start" value="0" />';
echo '<input type="hidden" name="page" value="main.content.orders">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';
echo JHtml::_('form.token');
echo '<button class="btn btn-primary loadMainContent ph-pos-btn-dropdown"><span class="glyphicon glyphicon-shopping-cart icon-white"></span> &nbsp;'.JText::_('COM_PHOCACART_ORDERS').'</button>';
echo '</form>';


// LOGOUT
echo '<div class="ph-dropdown-header">'.JText::_('COM_PHOCACART_LOGOUT').'</div>';
echo '<form action="'. JRoute::_('index.php?option=com_users&task=user.logout').'" method="post">';
echo '<button type="submit" class="btn btn-danger ph-pos-btn-dropdown"><span class="glyphicon glyphicon-arrow-left icon-white"></span> '.JText::_('JLOGOUT').'</button>';
echo '<input type="hidden" name="return" value="'. base64_encode(PhocacartRoute::getPosRoute()).'" />';
echo JHtml::_('form.token');
echo '</form>';

echo '</div>';// end dropdown-menu
echo '</div>';// end dropdown

echo '</div>';// end ph-pos-vendor-title
?>