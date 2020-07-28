<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Layout\LayoutHelper;

$published = $this->state->get('filter.published');
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
		<h3><?php echo JText::_($this->t['l'] . '_BATCH_OPTIONS_ITEMS');?></h3>
	</div>
	<div class="modal-body">

		<div class="row" style="margin:0;padding:0;">
			<div class="col-sm-6 col-md-6">
				<p><?php /* echo JText::_('COM_CONTENT_BATCH_TIP');*/ ?></p>
				<div class="control-group">
					<div class="controls">
						<?php echo LayoutHelper::render('joomla.html.batch.access', []);?>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
					</div>
				</div>
				<?php if ($published >= 0) : ?>
				<div class="control-group">
					<div class="controls">
						<?php
						$class	=	$this->t['n'].'HtmlBatch';
						echo $class::item($published, 0);
						?>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<div class="col-sm-6 col-md-6">
				<h4><?php echo JText::_('COPY_FUNCTION_PARAMETERS'); ?></h4>
				<div class="control-group">
					<div class="controls">
						<label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_('COM_PHOCACART_COPY_ALL_CATEGORIES_FROM_SOURCE') ?></label>

						<select name="batch[copy_all_cats]" class="inputbox" id="batch-category-copy-all-cats">
							<option value="1"><?php echo JText::_('COM_PHOCACART_YES'); ?></option>
							<option value="0" selected><?php echo JText::_('COM_PHOCACART_NO'); ?></option>
						</select>
					</div>
				</div>

				<div class="control-group">
					<div class="controls">
                        <label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_('COM_PHOCACART_SKIP_CREATING_UNIQUE_NAME') ?></label>

						<select name="batch[skip_creating_unique_name]" class="inputbox" id="batch-category-batch[skip_creating_unique_name]">
							<option value="1"><?php echo JText::_('COM_PHOCACART_YES'); ?></option>
							<option value="0" selected><?php echo JText::_('COM_PHOCACART_NO'); ?></option>
						</select>
					</div>
				</div>

                <div class="control-group">
                    <div class="controls">
                        <label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_('COM_PHOCACART_COPY_DOWNLOAD_FILES') ?></label>

                        <select name="batch[copy_download_files]" class="inputbox" id="batch[copy_download_files]">
                            <option value="1"><?php echo JText::_('COM_PHOCACART_YES'); ?></option>
                            <option value="0" selected><?php echo JText::_('COM_PHOCACART_NO'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_('COM_PHOCACART_COPY_ATTRIBUTE_OPTION_DOWNLOAD_FILES') ?></label>

                        <select name="batch[copy_attributes_download_files]" class="inputbox" id="batch[copy_attributes_download_files]">
                            <option value="1"><?php echo JText::_('COM_PHOCACART_YES'); ?></option>
                            <option value="0" selected><?php echo JText::_('COM_PHOCACART_NO'); ?></option>
                        </select>
                    </div>
                </div>


			</div>
		</div>

	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('<?php echo $this->t['task'] ?>.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
