<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
echo '<div id="ph-pc-info-box" class="pc-view pc-info-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo PhocacartRenderFront::renderHeader(array(Text::_('COM_PHOCACART_INFO')));

if ( $this->t['info_view_description'] != '') {
	echo '<div class="ph-desc">'. $this->t['info_view_description']. '</div>';
}


// Run view event, for conversions
$pluginView = PluginHelper::importPlugin('pcv');
if ($pluginView) {
	$eventData               = array();
	$results = Factory::getApplication()->triggerEvent('onPCVonInfoViewDisplayContent', array('com_phocacart.info', &$this->t['infodata'], &$this->t['infoaction'], $eventData));
	if (!empty($results)) {
		foreach ($results as $k => $v) {
			if ($v != false && isset($v['content']) && $v['content'] != '') {
				echo '<div class="ph-info-view-content">'.$v['content'].'</div>';
			}
		}
	}
}





switch($this->t['infoaction']) {

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

// Display Shipping Method Info Description
if (isset($this->t['infodata']['shipping_id']) && (int)$this->t['infodata']['shipping_id'] > 0) {

    $shippingDescription = PhocacartShipping::getInfoDescriptionById((int)$this->t['infodata']['shipping_id']);
    if ($shippingDescription != '') {
        echo '<div class="ph-info-shipping-description">'.HTMLHelper::_('content.prepare', $shippingDescription).'</div>';
    }

}

// Run shipping method event
if (isset($this->t['infodata']['shipping_method']) && $this->t['infodata']['shipping_method'] != '') {
	$pluginView = PluginHelper::importPlugin('pcs');
	if ($pluginView) {

		PluginHelper::importPlugin('pcs', htmlspecialchars(strip_tags($this->t['infodata']['shipping_method'])));
		$eventData               = array();
		$eventData['pluginname'] = htmlspecialchars(strip_tags($this->t['infodata']['shipping_method']));
		$results = Factory::getApplication()->triggerEvent('onPCSonInfoViewDisplayContent', array($this->t['infodata'], $eventData));
		/*if (isset($results[0]['content']) && $results[0]['content'] != '') {
			echo '<div class="ph-info-shipping-content">'.$results[0]['content'].'</div>';
		}*/
		if (!empty($results)) {
			foreach ($results as $k => $v) {
				if ($v != false && isset($v['content']) && $v['content'] != '') {
					echo '<div class="ph-info-shipping-content">'.$v['content'].'</div>';
				}
			}
		}
	}
}

// Display Payment Method Info Description
if (isset($this->t['infodata']['payment_id']) && (int)$this->t['infodata']['payment_id'] > 0) {

    $paymentDescription = PhocacartPayment::getInfoDescriptionById((int)$this->t['infodata']['payment_id']);
    if ($paymentDescription != '') {
        echo '<div class="ph-info-payment-description">'.HTMLHelper::_('content.prepare', $paymentDescription).'</div>';
    }

}

// Run payment method event
if (isset($this->t['infodata']['payment_method']) && $this->t['infodata']['payment_method'] != '') {
	$pluginPayment = PluginHelper::importPlugin('pcp');
	if ($pluginPayment) {



		PluginHelper::importPlugin('pcp', htmlspecialchars(strip_tags($this->t['infodata']['payment_method'])));
		$eventData               = array();
		$eventData['pluginname'] = htmlspecialchars(strip_tags($this->t['infodata']['payment_method']));

		$results = Factory::getApplication()->triggerEvent('onPCPonInfoViewDisplayContent', array($this->t['infodata'], $eventData));

		if (!empty($results)) {
			foreach ($results as $k => $v) {
				if ($v != false && isset($v['content']) && $v['content'] != '') {
					echo '<div class="ph-info-payment-content">'.$v['content'].'</div>';
				}
			}
		}
	}
}

echo '</div>';// end ph-pc-info-box
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>
