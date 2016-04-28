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
$d['checkout_view_href'] = 'data-dismiss="modal"';
if (isset($d['checkout_view']) && $d['checkout_view'] == 1) {
	$d['checkout_view_href'] = 'href="'.$d['link_checkout'].'"';
}
?>
<div id="phAddToCartPopup" class="modal zoom" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <a role="button" class="close" <?php echo $d['checkout_view_href']; ?> >&times;</a>
		  <h4><span class="glyphicon glyphicon-info-sign"></span> <?php echo $d['info_msg'] ?></h4>
        </div>
        <div class="modal-body">

		<div class="row">
<div class="col-xs-12 col-sm-6 col-md-6 ph-center">
<a class="btn btn-primary ph-btn" role="button" <?php echo $d['checkout_view_href']; ?> ><span class="glyphicon glyphicon-shopping-cart"></span> <?php echo JText::_('COM_PHOCACART_CONTINUE_SHOPPING'); ?></a>
</div>

<div class="col-xs-12 col-sm-6 col-md-6 ph-center">
<a class="btn btn-success ph-btn" role="button" href="<?php echo $d['link_checkout']; ?>" ><span class="glyphicon glyphicon-share-alt"></span> <?php echo JText::_('COM_PHOCACART_PROCEED_TO_CHECKOUT'); ?></a>
</div>
		</div>
        </div>
		<div class="modal-footer"></div>
	   </div>
    </div>
</div> 


 