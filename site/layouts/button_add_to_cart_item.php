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
    <input type="hidden" name="catid" value="<?php echo (int)$d['catid']; ?>"><?php

    if (isset($d['sku']) && $d['sku'] != ''){
       echo '<input type="hidden" name="sku" value="'.$d['sku'].'">';
    }
    if (isset($d['ean']) && $d['ean'] != ''){
       echo '<input type="hidden" name="ean" value="'.$d['ean'].'">';
    }
    if (isset($d['basepricenetto']) && $d['basepricenetto'] != ''){
       echo '<input type="hidden" name="basepricenetto" value="'.$d['basepricenetto'].'">';
    }
    if (isset($d['basepricetax']) && $d['basepricetax'] != ''){
       echo '<input type="hidden" name="basepricetax" value="'.$d['basepricetax'].'">';
    }
    if (isset($d['basepricebrutto']) && $d['basepricebrutto'] != ''){
       echo '<input type="hidden" name="basepricebrutto" value="'.$d['basepricebrutto'].'">';
    }
    if (isset($d['title']) && $d['title'] != ''){
       echo '<input type="hidden" name="title" value="'.$d['title'].'">';
    }

    ?><input type="hidden" name="task" value="checkout.add">
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="option" value="com_phocacart" />
	<input type="hidden" name="return" value="<?php echo $d['return']; ?>" />
	<div class="<?php echo $d['s']['c']['form-group'] ?> ph-form-quantity phProductAddToCart<?php echo $d['typeview']. (int)$d['id'] .' '.$d['class_btn']; ?>">
	<label><?php echo Text::_('COM_PHOCACART_QTY'); ?>: </label>
	<input class="<?php echo $d['s']['c']['form-control'] ?> ph-input-quantity" type="text" name="quantity" value="1" />
	</div>
	 <div class="<?php echo $d['s']['c']['form-group'] ?> ph-form-button"><?php
	if ($d['addtocart'] == 1) {
		?><button type="submit" class="<?php echo $d['s']['c']['btn.btn-primary'] ?> ph-btn phProductAddToCart<?php echo $d['typeview'] . (int)$d['id'] .' '.$d['class_btn']; ?>"><?php echo PhocacartRenderIcon::icon($d['s']['i']['shopping-cart'], '', ' ') . Text::_('COM_PHOCACART_ADD_TO_CART'); ?></button><?php
	} else if ($d['addtocart'] == 4) {
		?><button type="submit" class="<?php echo $d['s']['c']['btn.btn-primary'] ?> ph-btn phProductAddToCart<?php echo $d['typeview']. (int)$d['id'] .' '.$d['class_btn']; ?>" title="<?php echo Text::_('COM_PHOCACART_ADD_TO_CART'); ?>"><?php echo PhocacartRenderIcon::icon($d['s']['i']['shopping-cart']) ?></button><?php
	} ?>
	<?php /* <input type="submit" value="submit" name="submit" role="button" /> */ ?>
	</div>
	<div class="ph-cb"></div>
<?php echo HTMLHelper::_('form.token'); ?>
</div>

