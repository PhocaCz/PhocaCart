<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$d          = $displayData;
$s          = $d['s'];
$d['close'] = '<button type="button" class="close" aria-label="'.JText::_('COM_PHOCACART_CLOSE').'" data-dismiss="modal" ><span aria-hidden="true">&times;</span></button>';

?>
<div id="<?php echo $d['id'] ?>" class="<?php echo $s['c']['modal.zoom'] ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none">
    <div class="<?php echo $s['c']['modal-dialog'] ?> <?php echo $s['c']['modal-lg'] ?>">
      <div class="<?php echo $s['c']['modal-content'] ?>">
        <div class="<?php echo $s['c']['modal-header'] ?>">
          <?php echo $d['s']['c']['class-type'] != 'bs4' ? $d['close'] : '' ?>
		  <h4><span class="<?php echo $d['icon'] ?>"></span> <?php echo $d['title']; ?></h4>
            <?php echo $d['s']['c']['class-type'] == 'bs4' ? $d['close'] : '' ?>
        </div>
        <div class="<?php echo $s['c']['modal-body'] ?>">
			<?php
            /* We paste the iframe dynamically per Javascript so it does not include previous instance at start
                <iframe frameborder="0"></iframe>
                administrator/components/com_phocacart/libraries/phocacart/render/js.php
                public static function renderAjaxAskAQuestion($options = array()) {
             */
			?>
        </div>
		<div class="<?php echo $s['c']['modal-footer'] ?>"></div>
	   </div>
    </div>
</div>

