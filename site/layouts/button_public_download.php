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
?>
<form action="<?php echo $d['linkdownload']; ?>" method="post" id="phPublicDownload<?php echo (int)$d['id']; ?>" class="phItemPublicDownloadBoxForm">
	<input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>" />
	<input type="hidden" name="task" value="download.downloadpublic" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="option" value="com_phocacart" />
	<input type="hidden" name="return" value="<?php echo $d['return']; ?>" />
	<div class="ph-pull-right">
		<div class="ph-category-item-wishlist">
		<button type="submit" class="btn btn-primary ph-btn"><span class="glyphicon glyphicon-download"></span><?php
		if ($d['title'] != '') {
			echo ' '. $d['title'];
		} else {
			echo ' '. JText::_('COM_PHOCACART_DOWNLOAD');
		}
		?></button>
		</div>
	</div>
	<div class="clearfix"></div>
	<?php echo JHtml::_('form.token'); ?>
</form>