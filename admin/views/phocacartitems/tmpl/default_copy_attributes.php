<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;

//$published = $this->state->get('filter.published');
//$published = (int)$this->state->get('filter.published');
$s = array();

$s[] = '
jQuery(document).ready(function() {

    var bsModal = document.getElementById(\'collapseModalCA\');
	bsModal.addEventListener("shown.bs.modal", function (e) {
	//jQuery(\'#collapseModalCA\').on(\'shown.bs.modal\', function (e) {
		
		var phTitleString = \'\';
		var phCheckedValues = jQuery(\'input[name="cid[]"]:checked\');
		jQuery.each(phCheckedValues, function( i, v ) {
			var phIdTitle = \'.phIdTitle\' + v.value;
			phTitleString += \'<li>\' + jQuery(phIdTitle).html() + \'</li>\';
		});
		jQuery(\'#phCopyAttributesTo\').html(\'<ul>\' + phTitleString + \'</ul>\');
	});
	
});';
$document = Factory::getDocument();
$document->addScriptDeclaration(implode("\n", $s));
?>
<div id="collapseModalCA" role="dialog" tabindex="-1" class="joomla-modal modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo Text::_('COM_PHOCACART_COPY_ATTRIBUTES_TO_SELECTED_ITEMS');?></h3>
				    <button type="button" data-focus="false" class="btn-close novalidate" data-bs-dismiss="modal" aria-label="<?php Text::_('COM_PHOCACART_CLOSE'); ?>"></button>
            </div>

            <div class="modal-body">

            <div class="p-3">
	            <div class="row">

			<div class="span5 col-sm-5 col-md-5">
				<h3><?php echo Text::_('COM_PHOCACART_PRODUCT'); ?> (<?php echo Text::_('COM_PHOCACART_COPY_ATTRIBUTES_FROM'); ?>)</h3>


				<p><?php echo Text::_('COM_PHOCACART_SELECT_PRODUCT_FROM_WHICH_ATTRIBUTES_WILL_BE_COPIED'); ?></p>
				<div class="control-group">
					<div class="controls">
						<?php
						FormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
						$item = FormHelper::loadFieldType('PhocaSelectItem', true);
						$itemO = $item->getInputWithoutFormData();
						echo $itemO;
						?>
					</div>
				</div>

                <div class="control-group">
                    <div class="controls">
                        <label id="copy_attributes_download_files-lbl" for="copy_attributes_download_files"><?php echo Text::_('COM_PHOCACART_COPY_ATTRIBUTE_OPTION_DOWNLOAD_FILES') ?></label>

                        <select data-focus="false" name="copy_attributes_download_files" class="form-select" id="copy_attributes_download_files">
                            <option value="1"><?php echo Text::_('COM_PHOCACART_YES'); ?></option>
                            <option value="0" selected><?php echo Text::_('COM_PHOCACART_NO'); ?></option>
                        </select>
                    </div>
                </div>

			</div>

			<div class="span2 col-sm-2 col-md-2 ph-vertical-align-single">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" background-color="#ffffff00"><path d="M28.5 34.8L50 34.8 50 25 73 45.5 50 66 50 56.3 50 56.3 50 56.3 50 56.6 28.5 56.6 28.5 34.8 28.5 34.8Z" fill="#468c00"/></svg>
			</div>

			<div class="span5 col-sm-5 col-md-5">
				<h3><?php echo Text::_('COM_PHOCACART_PRODUCTS'); ?> (<?php echo Text::_('COM_PHOCACART_COPY_ATTRIBUTES_TO'); ?>)</h3>
				<div class="alert alert-error"><?php echo Text::_('COM_PHOCACART_BE_AWARE_COPYING_OF_ATTRIBUTES_CAN_OVERWRITE_CURRENT_ATTRIBUTES_OF_SELECTED_PRODUCTS'); ?></div>
				<div id="phCopyAttributesTo"></div>
			</div>

		</div>
            </div>
            </div>

	<div class="modal-footer">
		<button class="btn btn-primary" type="button" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value=''" data-bs-dismiss="modal">
			<?php echo Text::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('<?php echo $this->t['task'] ?>.copyattributes');">
			<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
        </div>
    </div>
