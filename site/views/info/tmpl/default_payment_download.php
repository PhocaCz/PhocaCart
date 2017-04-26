<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
// ORDER PROCESSED - DOWNLOADABLE ITEMS - PAYMENT MADE (Payment made, link to download could be possible) (PAYMENT/DOWNLOAD)	
// if user is logged in he/she will get the information about the download link
// if user is a guest - because of security reason we will not display the download link (by security token),
// user should get it per email
echo '<div class="alert alert-success">';
echo JText::_('COM_PHOCACART_ORDER_AND_PAYMENT_SUCCESSFULLY_PROCESSED');
echo '</br>' . JText::_('COM_PHOCACART_ORDER_PAYMENT_PROCESSED_DOWNLOADABLE_ITEMS_ADDITIONAL_INFO');
echo '</div>';
if ($this->u->id > 0) {
	echo '<div><a href="'.PhocacartRoute::getDownloadRoute().'">'.JText::_('COM_PHOCACART_DOWNLOAD_LINK').'</a></div>';
}
?>