<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
$d = $displayData;
$document = Factory::getDocument();
$document->addScript('https://paddle.s3.amazonaws.com/checkout/checkout.js');

?>
<div class="<?php echo $d['s']['c']['pull-right'];?> ph-item-buy-now-box">
	<button type="submit" class="<?php echo $d['s']['c']['btn.btn-primary'];?> ph-btn paddle_button" data-product="<?php echo $d['external_id']; ?>" data-theme="green"><span class="<?php echo $d['s']['i']['shopping-cart']; ?>"></span> <?php echo Text::_('COM_PHOCACART_BUY_NOW'); ?></button>
</div>
<div class="ph-cb"></div>
