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
$price	= $this->t['price'];
$order	= new PhocacartOrderRender();


$view = '<a href="#" data-type="1" data-order="'.(int)$this->t['id'].'" class="'.$this->s['c']['btn.btn-default.btn-sm'].' ph-btn phOrderPrintBtn" role="button"><span title="'.Text::_('COM_PHOCACART_VIEW_ORDER').'" class="'.$this->s['i']['order'].' ph-icon-success"></span></a>';
$view .= ' <a href="#" data-type="4" data-order="'.(int)$this->t['id'].'" class="'.$this->s['c']['btn.btn-default.btn-sm'].' ph-btn phOrderPrintBtn" role="button"><span title="'.Text::_('COM_PHOCACART_VIEW_RECEIPT').'" class="'.$this->s['i']['receipt'].' ph-icon-success"></span></a>';
$view .= ' <a href="#" data-type="2" data-order="'.(int)$this->t['id'].'" class="'.$this->s['c']['btn.btn-default.btn-sm'].' ph-btn phOrderPrintBtn" role="button"><span title="'.Text::_('COM_PHOCACART_VIEW_INVOICE').'" class="'.$this->s['i']['invoice'].' ph-icon-danger"></span></a>';
$view .= ' <a href="#" data-type="3" data-order="'.(int)$this->t['id'].'" class="'.$this->s['c']['btn.btn-default.btn-sm'].' ph-btn phOrderPrintBtn" role="button"><span title="'.Text::_('COM_PHOCACART_VIEW_DELIVERY_NOTE').'" class="'.$this->s['i']['del-note'].'"></span></a>';
$view .= ' <a href="#" data-type="-1" data-order="'.(int)$this->t['id'].'" class="'.$this->s['c']['btn.btn-default.btn-sm'].' ph-btn phOrderPrintBtn" role="button"><span title="'.Text::_('COM_PHOCACART_PRINT').'" class="'.$this->s['i']['print'].'"></span></a>';

// Default document displayed at start
$o = $order->render($this->t['id'], 4, 'raw');

echo '<div class="ph-pos-order-box">';
echo '<div class="ph-pos-order-print-box">'. $view.' </div>';
echo '<div class="ph-cb"></div>';

// class is used for CSS
// data-type and data order for selecting currently displayed document to print (SERVER PRINT)
echo '<div id="phPosOrderPrintBox" class="phType4" data-type="4" data-order="'.(int)$this->t['id'].'">';
$o = str_replace("\n", '', $o);// produce html output in PRE and CODE tag without new rows ("\n");
echo '<div class="phPrintInBox">'.$o.'</div>';// --> components\com_phocacart\views\order\view.raw.php
echo '</div>';

echo '</div>';// end ph-pos-order-box


// Pagination variables only
$this->items = false;
echo $this->loadTemplate('pagination');
?>
