<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$published = $this->state->get('filter.published');
?>
<script type="text/javascript">

jQuery(document).ready(function() {


	jQuery('#collapseModalCA').on('shown.bs.modal', function (e) {
		var phTitleString = '';
		var phCheckedValues = jQuery('input[name="cid[]"]:checked');
		jQuery.each(phCheckedValues, function( i, v ) {
			var phIdTitle = '#phIdTitle' + v.value;
			phTitleString += '<li>' + jQuery(phIdTitle).html() + '</li>';
		});
		jQuery('#phCopyAttributesTo').html('<ul>' + phTitleString + '</ul>');
	});


});
</script>
<div class="modal hide fade" id="collapseModalCA">
	<div class="modal-header">
		<button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
		<h3><?php echo JText::_($this->t['l'] . '_COPY_ATTRIBUTES_TO_SELECTED_ITEMS');?></h3>
	</div>
	<div class="modal-body">

		<div class="row-fluid">

			<div class="span5 col-sm-5 col-md-5">
				<h3><?php echo JText::_('COM_PHOCACART_PRODUCT'); ?> (<?php echo JText::_('COM_PHOCACART_COPY_ATTRIBUTES_FROM'); ?>)</h3>


				<p><?php echo JText::_('COM_PHOCACART_SELECT_PRODUCT_FROM_WHICH_ATTRIBUTES_WILL_BE_COPIED'); ?></p>
				<div class="control-group">
					<div class="controls">
						<?php
						JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
						$item = JFormHelper::loadFieldType('PhocaSelectItem', true);
						$itemO = $item->getInputWithoutFormData();
						echo $itemO;
						?>
					</div>
				</div>

                <div class="control-group">
                    <div class="controls">
                        <label id="copy_attributes_download_files-lbl" for="copy_attributes_download_files"><?php echo JText::_('COM_PHOCACART_COPY_ATTRIBUTE_OPTION_DOWNLOAD_FILES') ?></label>

                        <select name="copy_attributes_download_files" class="inputbox" id="copy_attributes_download_files">
                            <option value="1"><?php echo JText::_('COM_PHOCACART_YES'); ?></option>
                            <option value="0" selected><?php echo JText::_('COM_PHOCACART_NO'); ?></option>
                        </select>
                    </div>
                </div>

			</div>

			<div class="span2 col-sm-2 col-md-2 ph-vertical-align-single">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" background-color="#ffffff00"><path d="M28.5 34.8L50 34.8 50 25 73 45.5 50 66 50 56.3 50 56.3 50 56.3 50 56.6 28.5 56.6 28.5 34.8 28.5 34.8Z" fill="#468c00"/></svg>
			</div>

			<div class="span5 col-sm-5 col-md-5">
				<h3><?php echo JText::_('COM_PHOCACART_PRODUCTS'); ?> (<?php echo JText::_('COM_PHOCACART_COPY_ATTRIBUTES_TO'); ?>)</h3>
				<div class="alert alert-error"><?php echo JText::_('COM_PHOCACART_BE_AWARE_COPYING_OF_ATTRIBUTES_CAN_OVERWRITE_CURRENT_ATTRIBUTES_OF_SELECTED_PRODUCTS'); ?></div>
				<div id="phCopyAttributesTo"></div>
			</div>

		</div>

	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('<?php echo $this->t['task'] ?>.copyattributes');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
