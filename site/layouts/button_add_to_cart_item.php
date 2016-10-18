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
?>
<div class="pull-right ph-item-add-to-cart-box">	
	<input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>">
	<input type="hidden" name="catid" value="<?php echo (int)$d['catid']; ?>">
	<input type="hidden" name="task" value="checkout.add">
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="option" value="com_phocacart" />
	<input type="hidden" name="return" value="<?php echo $d['return']; ?>" />
	<div class="form-group">
	<label><?php echo JText::_('COM_PHOCACART_QTY'); ?>: </label> 
	<input class="form-control ph-input-quantity" type="text" name="quantity" value="1" />
	</div>
	 <div class="form-group"><?php 
	if ($d['addtocart'] == 1) {
		?><button type="submit" class="btn btn-primary ph-btn"><span class="glyphicon glyphicon-shopping-cart"></span> <?php echo JText::_('COM_PHOCACART_ADD_TO_CART'); ?></button><?php
	} else if ($d['addtocart'] == 4) {
		?><button type="submit" class="btn btn-primary ph-btn" title="<?php echo JText::_('COM_PHOCACART_ADD_TO_CART'); ?>"><span class="glyphicon glyphicon-shopping-cart"></span></button><?php
	} ?>
	<?php /* <input type="submit" value="submit" name="submit" role="button" /> */ ?>
	</div>
<?php echo JHtml::_('form.token'); ?>
</div>