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
$d['checkout_view_href']    = 'data-dismiss="modal"';
$d['close']                 = '<button type="button" class="close" aria-label="'.JText::_('COM_PHOCACART_CLOSE').'" '. $d['checkout_view_href'].' ><span aria-hidden="true">&times;</span></button>';
if (isset($d['checkout_view']) && $d['checkout_view'] == 1) {
	$d['checkout_view_href'] = 'href="'.$d['link_checkout'].'"';
	$d['close']             = '<a role="button" class="close" aria-label="'.JText::_('COM_PHOCACART_CLOSE').'" '. $d['checkout_view_href'].' ><span aria-hidden="true">&times;</span></a>';
}
?>
<div id="phAddToCartPopup" class="<?php echo $d['s']['c']['modal.zoom'] ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="<?php echo $d['s']['c']['modal-dialog'] ?>">
      <div class="<?php echo $d['s']['c']['modal-content'] ?>">
        <div class="<?php echo $d['s']['c']['modal-header'] ?>">
            <?php echo $d['s']['c']['class-type'] != 'bs4' ? $d['close'] : '' ?>
		    <h4><span class="<?php echo $d['s']['i']['info-sign'] ?>"></span> <?php echo $d['info_msg'] ?></h4>
            <?php echo $d['s']['c']['class-type'] == 'bs4' ? $d['close'] : '' ?>
        </div>
        <div class="<?php echo $d['s']['c']['modal-body'] ?>">


            <div class="<?php echo $d['s']['c']['row'] ?>">
                <div class="<?php echo $d['s']['c']['col.xs12.sm12.md12'] ?> ph-center">
                    <?php

                    JPluginHelper::importPlugin('pcv');
                    $results = \JFactory::getApplication()->triggerEvent('PCVonPopupAddToCartAfterHeader', array('com_phocacart.popupaddtocart', $d['product'], $d['products'], $d['total']));
                    echo trim(implode("\n", $results));

                    ?>
                </div>
            </div>

		<div class="<?php echo $d['s']['c']['row'] ?>">
<div class="<?php echo $d['s']['c']['col.xs12.sm6.md6'] ?> ph-center">
<a class="<?php echo $d['s']['c']['btn.btn-primary'] ?> ph-btn" role="button" <?php echo $d['checkout_view_href']; ?> ><span class="<?php echo $d['s']['i']['shopping-cart'] ?>"></span> <?php echo JText::_('COM_PHOCACART_CONTINUE_SHOPPING'); ?></a>
</div>

<div class="<?php echo $d['s']['c']['col.xs12.sm6.md6'] ?> ph-center">
<a class="<?php echo $d['s']['c']['btn.btn-success'] ?> ph-btn" role="button" href="<?php echo $d['link_checkout']; ?>" ><span class="<?php echo $d['s']['i']['int-link'] ?>"></span> <?php echo JText::_('COM_PHOCACART_PROCEED_TO_CHECKOUT'); ?></a>
</div>
		</div>
        </div>
		<div class="<?php echo $d['s']['c']['modal-footer'] ?>"></div>
	   </div>
    </div>
</div>


