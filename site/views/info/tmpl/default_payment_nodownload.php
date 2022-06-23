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
use Joomla\CMS\Layout\FileLayout;

$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));

// ORDER PROCESSED - STANDARD PRODUCTS - PAYMENT MADE (PAYMENT/NO DOWNLOAD)
if (isset($this->t['infomessage']['payment_nodownload']) && $this->t['infomessage']['payment_nodownload'] != '') {
	$msg = $this->t['infomessage']['payment_nodownload'];
} else {
	$msg = Text::_('COM_PHOCACART_ORDER_AND_PAYMENT_SUCCESSFULLY_PROCESSED');
	$msg .= '</br>' . Text::_('COM_PHOCACART_ORDER_PAYMENT_PROCESSED_ADDITIONAL_INFO');
}

echo $layoutAl->render(array('type' => 'success', 'text' => $msg));
?>
