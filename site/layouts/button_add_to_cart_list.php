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
// One of the attributes is required, cannot add to cart
if ($d['attrrequired'] == 1) { ?>			
	<div class="pull-right">
		<a href="<?php echo $d['link']; ?>" class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-shopping-cart"></span> <?php echo JText::_('COM_PHOCACART_ADD_TO_CART'); ?></a>
	</div>
<?php } else { ?>
	<form class="phItemCartBoxForm" action="<?php echo $d['linkch']; ?>" method="post">
	<input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>">
	<input type="hidden" name="catid" value="<?php echo (int)$d['catid']; ?>">
	<input type="hidden" name="quantity" value="1">
	<input type="hidden" name="task" value="checkout.add">
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="option" value="com_phocacart" />
	<input type="hidden" name="return" value="<?php echo $d['return']; ?>" />
	<div class="pull-right">
	<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-shopping-cart"></span> <?php echo JText::_('COM_PHOCACART_ADD_TO_CART'); ?></button>
	</div>
	<?php echo JHtml::_('form.token'); ?>
	</form>
	
<?php } ?>