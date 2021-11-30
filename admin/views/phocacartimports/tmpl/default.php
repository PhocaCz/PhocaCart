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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
$r 			= $this->r;

echo '<div id="'.$this->t['tasks'].'">';
//echo $r->startFilter();
//echo $r->endFilter();
echo $r->startMainContainer();


$url 	= Route::_('index.php?option=com_phocacart&view=phocacartimports');
$c1 = $c2 = 'circle';
if ((int)$this->t['count'] > 0) {
	$c1 = 'circle-active';
	$url2 	= 'index.php?option=com_phocacart&task=phocacartimport.import&format=json&tmpl=component&'. Session::getFormToken().'=1';
	PhocacartRenderAdminjs::renderImportExportItems($url2, 'phMessageBox', 'phFormImport', (int)$this->t['count_pagination'], Text::_('COM_PHOCACART_ALL_PRODUCTS_IMPORTED'));
}
?>




<div class="import-export">

<div class="row import-export-row-message">
	<div class="col-xs-12 col-sm-12 col-md-12">
		<div id="phMessageBox"></div>
	</div>
</div>

<div class="row import-export-row">

<div class="col-xs-12 col-sm-6 col-md-6">
<div class="import-export-box">
<div class="<?php echo $c1; ?>">1</div>
<h2><?php echo Text::_('COM_PHOCACART_UPLOAD'); ?></h2>
<div class="import-export-desc"><?php echo Text::_('COM_PHOCACART_SELECT_FILE_TO_IMPORT_ITEMS'); ?> (CSV, XML)</div>
<p>&nbsp;</p>
<form class="form-inline" id="phFormUpload" action="<?php echo $url; ?>" enctype="multipart/form-data" method="post" data-message="phMessageBox">
  <div class="form-group">
	<label for="file_upload"><?php echo Text::_('COM_PHOCACART_FILE'); ?>:</label>
	<input type="file" name="Filedata" id="file_upload" >
	<input class="btn btn-primary" type="submit" name="submit" value="<?php echo Text::_('COM_PHOCACART_UPLOAD');?>">
	<input type="hidden" name="task" value="phocacartimport.upload" />
	<?php echo HTMLHelper::_('form.token'); ?>
  </div>
</form>

</div></div>

<div class="col-xs-12 col-sm-6 col-md-6">

<?php if ((int)$this->t['count'] > 0) { ?>
<div class="import-export-box">
<div class="<?php echo $c2; ?>">2</div>
<h2><?php echo Text::_('COM_PHOCACART_IMPORT'); ?></h2>

<div class="import-export-desc"><?php echo Text::_('COM_PHOCACART_THERE_ARE_ITEMS_READY_TO_IMPORT'); ?>: <?php echo $this->t['count']; ?><br /><?php echo Text::_('COM_PHOCACART_CLICK_IMPORT_BUTTON_TO_IMPORT_THEM_TO_SHOP'); ?></div>
<div class="alert alert-warning"><?php echo Text::_('COM_PHOCACART_BE_AWARE_IMPORT_CAN_OVERWRITE_CURRENT_ITEMS_IN_SHOP'); ?></div>
<p>&nbsp;</p>
<form class="form-inline" id="phFormImport" action="<?php echo $url2; ?>" method="post" data-message="phMessageBox">
  <div class="form-group">
	<label for="file_import"><?php echo Text::_('COM_PHOCACART_IMPORT'); ?>:</label>
  <input class="btn btn-primary" type="submit" name="submit" value="<?php echo Text::_('COM_PHOCACART_IMPORT');?>">
  </div>
</form>

<div class="progress progress-striped active" >
    <div id="phProgressBar" class="progress-bar"></div>
</div>





</div>
<?php } ?>
</div>


</div>
</div>
<?php
echo $r->endMainContainer();
echo '</div>';
