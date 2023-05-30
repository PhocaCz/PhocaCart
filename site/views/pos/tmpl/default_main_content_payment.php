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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
$price		= new PhocacartPrice();


echo '<div class="ph-pos-payment-box">';

echo '<div class="'.$this->s['c']['row'].' row-vac">';

echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm3.md3'].'">';
echo '</div>';


echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm3.md3'].'">';
echo '<div class="ph-pos-payment-item-txt">' . Text::_('COM_PHOCACART_TOTAL_TO_PAY') . '</div>';
echo '</div>';

echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm3.md3'].'">';
//$totalAmount = 0;
if (isset($this->t['total'][0]['brutto_currency']) &&  $this->t['total'][0]['brutto_currency'] !== 0) {
	echo '<div class="ph-pos-total-to-pay ph-right">' . $price->getPriceFormat($this->t['total'][0]['brutto_currency'], 0, 1).'</div>';
	//$totalAmount = $this->t['total'][0]['brutto_currency'];
} else if ($this->t['total'][0]['brutto'] !== 0) {
	echo '<div class="ph-pos-total-to-pay ph-right">' . $price->getPriceFormat($this->t['total'][0]['brutto']).'</div>';
	//$totalAmount = $this->t['total'][0]['brutto'];
}
echo '</div>';


echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm3.md3'].'">';
echo '</div>';

echo '</div>'; // end row-vac



echo '<form class="form-inline" action="'.$this->t['action'].'" method="post">';

// PLUGIN
$output 	= '';
$payment	= $this->cart->getPaymentMethod();
if (isset($payment['method'])) {
	//$dispatcher = J EventDispatcher::getInstance();
	JPluginHelper::importPlugin('pcp', htmlspecialchars(strip_tags($payment['method'])));
	$eventData               = array();
	$eventData['pluginname'] = htmlspecialchars(strip_tags($payment['method']));
	JFactory::getApplication()->triggerEvent('onPCPonDisplayPaymentPos', array(&$output, $this->t, $eventData));
	echo $output;
}
// END PLUGIN



echo '<div class="'.$this->s['c']['row'].' row-vac">';

echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm3.md3'].'">';
echo '</div>';

echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm6.md6'].' ph-pos-payment-confirm-box">';

echo '<input type="hidden" name="task" value="pos.order">';
echo '<input type="hidden" name="page" value="main.content.payment">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';
echo '<input type="hidden" name="redirectsuccess" value="main.content.order" />';
echo '<input type="hidden" name="redirecterror" value="main.content.payment" />';
echo HTMLHelper::_('form.token');
echo '<button class="'.$this->s['c']['btn.btn-success.btn-lg'].' btn-extra-lg editMainContent">'.Text::_('COM_PHOCACART_CONFIRM').'</button>';

echo '</div>';

echo '<div class="'.$this->s['c']['row-item'].' '.$this->s['c']['col.xs12.sm3.md3'].'">';
echo '</div>';

echo '</div>';// end row-vac

echo '</form>';


echo '</div>';// end ph-pos-payment-box


// Pagination variables only
$this->items = false;
echo $this->loadTemplate('pagination');
?>
