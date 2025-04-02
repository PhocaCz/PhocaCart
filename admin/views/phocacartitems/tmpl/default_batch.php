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
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\CMS\Form\Form $batchForm */
$batchForm = $this->batchForm;
?>
<div id="collapseModal" role="dialog" tabindex="-1" class="joomla-modal modal fade">
  <div class="modal-dialog modal-phoca-batch">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title"><?php echo Text::_('COM_PHOCACART_BATCH_OPTIONS_ITEMS');?></h3>
        <button type="button" class="btn-close novalidate" data-bs-dismiss="modal" aria-label="<?php Text::_('COM_PHOCACART_CLOSE'); ?>">
        </button>
      </div>

      <div class="modal-body">
        <div class="p-3">
          <?php echo $batchForm->renderFieldset('top'); ?>

          <div class="row">
            <div class="col-sm-6">
                <?php echo $batchForm->renderFieldset('basic'); ?>
            </div>

            <div class="col-sm-6">
                <?php echo $batchForm->renderFieldset('params'); ?>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn" type="button" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value=''" data-bs-dismiss="modal">
              <?php echo Text::_('JCANCEL'); ?>
          </button>
          <button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('<?php echo $this->t['task'] ?>.batch');" id="batch-submit-button-id" data-submit-task="<?php echo $this->t['task'] ?>.batch">
              <?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
