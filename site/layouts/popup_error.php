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
$d['close'] = '<button type="button" class="close" aria-label="'.JText::_('COM_PHOCACART_CLOSE').'" data-dismiss="modal" ><span aria-hidden="true">&times;</span></button>';

?>
<div id="phAddToCartPopup" class="<?php echo $d['s']['c']['modal.zoom'] ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="<?php echo $d['s']['c']['modal-dialog'] ?>">
      <div class="<?php echo $d['s']['c']['modal-content'] ?>">
        <div class="<?php echo $d['s']['c']['modal-header'] ?>">
          <?php echo $d['s']['c']['class-type'] != 'bs4' ? $d['close'] : '' ?>
		  <h4><span class="<?php echo $d['s']['i']['info-sign'] ?>"></span> <?php echo JText::_('COM_PHOCACART_ERROR'); ?></h4>
            <?php echo $d['s']['c']['class-type'] == 'bs4' ? $d['close'] : '' ?>
        </div>
        <div class="<?php echo $d['s']['c']['modal-body'] ?>">
			<?php echo $d['info_msg']; ?>
        </div>
		<div class="<?php echo $d['s']['c']['modal-footer'] ?>"></div>
	   </div>
    </div>
</div>


