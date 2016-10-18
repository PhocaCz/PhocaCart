<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
echo '<div id="ph-pc-info-box" class="pc-info-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo PhocaCartRenderFront::renderHeader(array(JText::_('COM_PHOCACART_INFO')));

switch($this->t['infomessage']) {

	case 1:
		// ORDER PROCESSED - STANDARD PRODUCTS (ORDER/NO DOWNLOAD)
		echo $this->loadTemplate('order_nodownload');
	break;
	
	case 2:
		// ORDER PROCESSED - DOWNLOADABLE ITEMS (No payment made, display only information about possible downloads) (ORDER/DOWNLOAD)
		echo $this->loadTemplate('order_download');
	break;
	
	case 3:
		// ORDER PROCESSED - STANDARD PRODUCTS - PAYMENT MADE (PAYMENT/NO DOWNLOAD)
		echo $this->loadTemplate('payment_nodownload');
	break;

	case 4:
		// ORDER PROCESSED - DOWNLOADABLE ITEMS - PAYMENT MADE (Payment made, link to download could be possible) (PAYMENT/DOWNLOAD)
		echo $this->loadTemplate('payment_download');
	break;
	
	case 5:
		// PAYMENT CANCELED
		echo $this->loadTemplate('payment_canceled');
	break;

}

echo '</div>';// end ph-pc-checkout-box
echo '<div>&nbsp;</div>';
echo PhocaCartUtils::getInfo();
?>