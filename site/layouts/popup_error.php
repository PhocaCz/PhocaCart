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
$d = $displayData;
$d['close'] = '<button type="button" class="'.$d['s']['c']['modal-btn-close'].'"'.$d['s']['a']['modal-btn-close'].' aria-label="'.Text::_('COM_PHOCACART_CLOSE').'" '.$d['s']['a']['data-bs-dismiss-modal'].' ></button>';

?>
<div id="phAddToCartPopup" class="<?php echo $d['s']['c']['modal.zoom'] ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="<?php echo $d['s']['c']['modal-dialog'] ?>">
      <div class="<?php echo $d['s']['c']['modal-content'] ?>">
        <div class="<?php echo $d['s']['c']['modal-header'] ?>">
            <h5 class="<?php echo $d['s']['c']['modal-title'] ?>"><span class="<?php echo $d['s']['i']['info-sign'] ?>"></span> <?php echo Text::_('COM_PHOCACART_ERROR'); ?></h5>
            <?php echo $d['close'] ?>
        </div>
        <div class="<?php echo $d['s']['c']['modal-body'] ?>">
			<div class="<?php echo $d['s']['c']['row'] ?>">
                <div class="<?php echo $d['s']['c']['col.xs12.sm12.md12'] ?> ph-center">
            <?php echo $d['info_msg']; ?>
                </div>
            </div>
        </div>
		<div class="<?php echo $d['s']['c']['modal-footer'] ?>"></div>
	   </div>
    </div>
</div>


