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
use Joomla\CMS\HTML\HTMLHelper;
$d = $displayData;

?>
<div class="<?php echo $d['s']['c']['pull-right'] ?> <?php echo $d['s']['c']['form-group'] ?> ph-item-add-to-cart-box">
	<input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>">
	<input type="hidden" name="catid" value="<?php echo (int)$d['catid']; ?>">
	<input type="hidden" name="task" value="checkout.add">
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="option" value="com_phocacart" />
	<input type="hidden" name="return" value="<?php echo $d['return']; ?>" />
	<div class="<?php echo $d['s']['c']['form-group'] ?> ph-form-quantity phProductAddToCart<?php echo $d['typeview']. (int)$d['id'] .' '.$d['class_btn']; ?>">
	<label><?php echo Text::_('COM_PHOCACART_QTY'); ?>: </label>
	<input class="<?php echo $d['s']['c']['form-control'] ?> ph-input-quantity" type="text" name="quantity" value="1" />
	</div>
	 <div class="<?php echo $d['s']['c']['form-group'] ?> ph-form-button"><?php
	if ($d['addtocart'] == 1) {
		?><button type="submit" class="<?php echo $d['s']['c']['btn.btn-primary'] ?> ph-btn phProductAddToCart<?php echo $d['typeview'] . (int)$d['id'] .' '.$d['class_btn']; ?>"><span class="<?php echo $d['s']['i']['shopping-cart'] ?>"></span> <?php echo Text::_('COM_PHOCACART_ADD_TO_CART'); ?></button><?php
	} else if ($d['addtocart'] == 4) {
		?><button type="submit" class="<?php echo $d['s']['c']['btn.btn-primary'] ?> ph-btn phProductAddToCart<?php echo $d['typeview']. (int)$d['id'] .' '.$d['class_btn']; ?>" title="<?php echo Text::_('COM_PHOCACART_ADD_TO_CART'); ?>"><span class="<?php echo $d['s']['i']['shopping-cart'] ?>"></span></button><?php
	} ?>
	<?php /* <input type="submit" value="submit" name="submit" role="button" /> */ ?>
	</div>
	<div class="ph-cb"></div>
<?php echo HTMLHelper::_('form.token'); ?>
</div>

