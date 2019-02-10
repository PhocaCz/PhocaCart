<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

?><script type="text/javascript">
jQuery(document).ready(function (){
	jQuery('#phSubmitDownload').on('click', function () {
		jQuery(".circle").attr('class', 'circle-active');
	})
});
</script><?php


$class		= $this->t['n'] . 'RenderAdminviews';
$r 			=  new $class();
echo '<div id="'.$this->t['tasks'].'"><div class="row-fluid ph-admin-box">';
echo $r->startFilter();
echo $r->endFilter();
echo $r->startMainContainer();


$url 	= JRoute::_('index.php?option=com_phocacart&view=phocacartexports');
$c1 	= $c2 = 'circle';
$msg	= '';

echo '<div class="import-export">';

if ((int)$this->t['count'] < 1) { 

	$msg .= '<div class="alert alert-success">';
	$msg .= JText::_('COM_PHOCACART_THERE_ARE_NO_ITEMS_READY_TO_EXPORT');
	$msg .= '</div>';
	?>
	
	<div class="row-fluid import-export-row-message">
		<div class="col-xs-12 col-sm-12 col-md-12">
			<div id="phMessageBox"><?php echo $msg ?></div>
		</div>
	</div>
	<?php
} else {
	
	// Prouducts count > 0
	if ((int)$this->t['count'] > 0) {
		$url2 	= 'index.php?option=com_phocacart&task=phocacartexport.export&format=json&tmpl=component&'. JSession::getFormToken().'=1';
		PhocacartRenderAdminjs::renderImportExportItems($url2, 'phMessageBox', 'phFormExport', (int)$this->t['count_pagination'], JText::_('COM_PHOCACART_ALL_PRODUCTS_EXPORTED'), 1);
	}
	
	// Products were exported to export table
	if ((int)$this->t['countexport'] > 0) {
		$c1 = 'circle-active';
	}
	
	// Products were exported to export table and then the browser reloaded the page so we need to inform the user about success
	if ((int)$this->t['countexport'] == (int)$this->t['count']) {
		$msg .= '<div class="alert alert-success">';
		$msg .= JText::_('COM_PHOCACART_ALL_PRODUCTS_EXPORTED');
		$msg .= '</div>';
	}
	
	?><div class="row-fluid import-export-row-message">
		<div class="col-xs-12 col-sm-12 col-md-12">
			<div id="phMessageBox"><?php echo $msg ?></div>
		</div>
	</div>
	
	
	
	<div class="row-fluid import-export-row">
		<div class="col-xs-12 col-sm-6 col-md-6">
			<div class="import-export-box">
			<?php if ((int)$this->t['count'] > 0) { ?>
				
					<div class="<?php echo $c1; ?>">1</div>
					<h2><?php echo JText::_('COM_PHOCACART_EXPORT'); ?></h2>
			
					<?php if ((int)$this->t['countexport'] > 0) { 
					
					} else { ?>
					
					<div class="import-export-desc"><?php echo JText::_('COM_PHOCACART_THERE_ARE_ITEMS_READY_TO_EXPORT'); ?>: <?php echo $this->t['count']; ?><br /><?php echo JText::_('COM_PHOCACART_CLICK_EXPORT_BUTTON_TO_EXPORT_THEM_TO_FILE'); ?></div>
				<?php } ?>
				
					<p>&nbsp;</p>
					<form class="form-inline" id="phFormExport" action="<?php echo $url2; ?>" method="post" data-message="phMessageBox">
					  <div class="form-group">
						<label for="file_import"><?php echo JText::_('COM_PHOCACART_EXPORT'); ?>:</label>
					  <input class="btn btn-primary" type="submit"  name="submit" value="<?php echo JText::_('COM_PHOCACART_EXPORT');?>">
					  </div>
					</form>
			
					<div class="progress progress-striped active" >
						<div id="phProgressBar" class="bar"></div>
					</div>
			<?php } else { ?>
					
				<div class="import-export-desc"><?php echo JText::_('COM_PHOCACART_THERE_ARE_NO_ITEMS_READY_TO_EXPORT'); ?></div>
			
			<?php } ?>

			</div>
		</div>

		<div class="col-xs-12 col-sm-6 col-md-6">
		
			<?php if ((int)$this->t['countexport'] > 0) { ?>
			<div class="import-export-box">
				<div class="<?php echo $c2; ?>">2</div>
				<h2><?php echo JText::_('COM_PHOCACART_DOWNLOAD'); ?></h2>
				<div class="import-export-desc"><?php echo JText::_('COM_PHOCACART_EXPORT_FILE_IS_READY_TO_DOWNLOAD'); ?><br /><?php echo JText::_('COM_PHOCACART_CLICK_DOWNLOAD_BUTTON_TO_DOWNLOAD_FILE'); ?></div>
				<p>&nbsp;</p>
				<form class="form-inline" id="phFormUpload" action="<?php echo $url; ?>" enctype="multipart/form-data" method="post" data-message="phMessageBox">
			  		<div class="form-group">
						<label for="file_download"><?php echo JText::_('COM_PHOCACART_FILE'); ?>:</label>
				
						<input id="phSubmitDownload" class="btn btn-primary" type="submit" name="submit" value="<?php echo JText::_('COM_PHOCACART_DOWNLOAD');?>">
						<input type="hidden" name="task" value="phocacartexport.download" />
				<?php echo JHtml::_('form.token'); ?>  
			  		</div>
				</form>
			
			</div>
			<?php } ?>
		</div>
	</div><?php
}

echo '</div>';// end import-export

echo $r->endMainContainer();
echo '</div></div>';
?>