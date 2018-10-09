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
<div id="phAddToCartPopup" class="modal zoom" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <a role="button" class="close" data-dismiss="modal" >&times;</a>
		  <h4><span class="<?php echo PhocacartRenderIcon::getClass('info-sign') ?>"></span> <?php echo JText::_('COM_PHOCACART_ERROR'); ?></h4>
        </div>
        <div class="modal-body">
			<?php echo $d['info_msg']; ?>
        </div>
		<div class="modal-footer"></div>
	   </div>
    </div>
</div> 


 