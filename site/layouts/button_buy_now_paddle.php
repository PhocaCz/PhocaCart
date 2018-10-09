<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d = $displayData;
$document = JFactory::getDocument();
$document->addScript('https://paddle.s3.amazonaws.com/checkout/checkout.js');
		
?>
<div class="ph-pull-right ph-item-buy-now-box">	
	<button type="submit" class="btn btn-primary ph-btn paddle_button" data-product="<?php echo $d['external_id']; ?>" data-theme="green"><span class="<?php echo PhocacartRenderIcon::getClass('shopping-cart') ?>"></span> <?php echo JText::_('COM_PHOCACART_BUY_NOW'); ?></button>
</div>
<div class="clearfix"></div>
