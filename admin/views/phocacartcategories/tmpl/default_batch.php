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

//$published = $this->state->get('filter.published');
//$published = (int)$this->state->get('filter.published');
?>
<div id="collapseModal" role="dialog" tabindex="-1" class="joomla-modal modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
		<h3 class="modal-title"><?php echo Text::_('COM_PHOCACART_BATCH_OPTIONS_CATEGORIES');?></h3>
				<button type="button" class="btn-close novalidate" data-bs-dismiss="modal" aria-label="<?php Text::_('COM_PHOCACART_CLOSE'); ?>">
		</button>
	</div>
	<div class="modal-body">

		<div class="p-3">
	<div class="row">
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
				<div class="control-group">
					<div class="controls">
						<?php
						echo PhocacartHtmlBatch::item('', 1);
						?>
					</div>
				</div>
			</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary" type="button" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value=''" data-bs-dismiss="modal">
			<?php echo Text::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('<?php echo $this->t['task'] ?>.batch');">
			<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
    </div>
