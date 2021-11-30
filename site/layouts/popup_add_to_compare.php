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
use Joomla\CMS\Factory;
$d = $displayData;
$d['comparison_view_href']    = $d['s']['a']['data-bs-dismiss-modal'];
$d['close']                 = '<button type="button" class="'.$d['s']['c']['modal-btn-close'].'"'.$d['s']['a']['modal-btn-close'].' aria-label="'.Text::_('COM_PHOCACART_CLOSE').'" '. $d['comparison_view_href'].' ></button>';
if (isset($d['comparison_view']) && $d['comparison_view'] == 1) {
	$d['comparison_view_href'] = 'href="'.$d['link_comparison'].'"';
	$d['close']             = '<a role="button" class="'.$d['s']['c']['modal-btn-close'].'"'.$d['s']['a']['modal-btn-close'].' aria-label="'.Text::_('COM_PHOCACART_CLOSE').'" '. $d['comparison_view_href'].' ></a>';
}
?>
<div id="phAddToComparePopup" class="<?php echo $d['s']['c']['modal.zoom'] ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="<?php echo $d['s']['c']['modal-dialog'] ?>">
      <div class="<?php echo $d['s']['c']['modal-content'] ?>">
        <div class="<?php echo $d['s']['c']['modal-header'] ?>">
		  <h5 class="<?php echo $d['s']['c']['modal-title'] ?>"><span class="<?php echo $d['s']['i']['info-sign'] ?>"></span> <?php echo $d['info_msg'] ?></h5>
            <?php echo $d['close'] ?>
        </div>
        <div class="<?php echo $d['s']['c']['modal-body'] ?>">
            <div class="<?php echo $d['s']['c']['row'] ?>">
                <div class="<?php echo $d['s']['c']['col.xs12.sm12.md12'] ?> ph-center">
            <?php if (isset($d['info_msg_additional']) && $d['info_msg_additional'] != '') { ?>
			<div><?php echo $d['info_msg_additional']; ?></div>
		  <?php } ?>
                </div>
            </div>

		<div class="<?php echo $d['s']['c']['row'] ?>">
<div class="<?php echo $d['s']['c']['col.xs12.sm6.md6'] ?> ph-center">
<a class="<?php echo $d['s']['c']['btn.btn-primary'].' '.$d['s']['c']['modal-common-close'] ?> ph-btn" role="button" <?php echo $d['comparison_view_href']; ?> ><span class="<?php echo $d['s']['i']['shopping-cart'] ?>"></span> <?php echo Text::_('COM_PHOCACART_CONTINUE_SHOPPING'); ?></a>
</div>

<div class="<?php echo $d['s']['c']['col.xs12.sm6.md6'] ?> ph-center">
<a class="<?php echo $d['s']['c']['btn.btn-success'] ?> ph-btn" role="button" href="<?php echo $d['link_comparison']; ?>" ><span class="<?php echo $d['s']['i']['int-link'] ?>"></span> <?php echo Text::_('COM_PHOCACART_PROCEED_TO_COMPARISON_LIST'); ?></a>
</div>
		</div>
        </div>
		<div class="<?php echo $d['s']['c']['modal-footer'] ?>"></div>
	   </div>
    </div>
</div>


