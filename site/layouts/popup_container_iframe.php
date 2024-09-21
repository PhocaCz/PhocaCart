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

$d          = $displayData;
$s          = $d['s'];
$d['close'] = '<button type="button" class="'.$d['s']['c']['modal-btn-close'].'"'.$d['s']['a']['modal-btn-close'].' aria-label="'.Text::_('COM_PHOCACART_CLOSE').'" '.$d['s']['a']['data-bs-dismiss-modal'].' ></button>';
$modalClass = isset($d['modal-class']) ? $d['modal-class'] : 'modal-lg';


?>
<div id="<?php echo $d['id'] ?>" class="<?php echo $s['c']['modal.zoom'] ?>"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none">
    <div class="<?php echo $s['c']['modal-dialog'] ?> <?php echo $s['c'][$modalClass] ?>">
      <div class="<?php echo $s['c']['modal-content'] ?>">
        <div class="<?php echo $s['c']['modal-header'] ?>">
		  <h5 class="<?php echo $d['s']['c']['modal-title'] ?>"><?php echo PhocacartRenderIcon::icon($d['icon'], '', ' ') .  $d['title'];  ?></h5>
            <?php echo $d['close'] ?>
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
		<div class="<?php echo $s['c']['modal-footer'] ?>"><?php
		    if (isset($d['closebutton']) && $d['closebutton'] == 1) {
              echo '<button type="button" class="'.$s['c']['btn.btn-secondary'].'" '.$d['s']['a']['data-bs-dismiss-modal'].' >'.Text::_('COM_PHOCACART_CLOSE').'</button>';
		    }
		?></div>
	   </div>
    </div>
</div>

