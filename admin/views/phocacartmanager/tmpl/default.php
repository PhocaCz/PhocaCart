<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Path;
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
HTMLHelper::_('formbehavior.chosen', 'select');

$r 			=  new PhocacartRenderAdminview();

echo '<div id="phocacartmanager">';
echo '<div class="span12 form-horizontal">';
echo '<div class="ph-admin-path">' . Text::_('COM_PHOCACART_PATH'). ': '.Path::clean($this->t['path']['orig_abs_ds']. $this->folderstate->folder) .'</div>';
//$countFaF =  count($this->images) + count($this->folders);
echo '<table class="table table-hover table-condensed ph-multiple-table">'
.'<thead>'
.'<tr>';
echo '<th class=" ph-check">'. "\n";
//echo '<input type="checkbox" name="checkall-toggle" value="" title="'.Text::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />'. "\n";
echo '</th>'. "\n";
echo '<th width="20">&nbsp;</th>'
.'<th width="95%">'.Text::_( $this->t['l'].'_FILENAME' ).'</th>'
.'</tr>'
.'</thead>';

echo '<tbody>';
echo $this->loadTemplate('up');
if (count($this->files) > 0 || count($this->folders) > 0) {

	echo '<div>';
	for ($i=0,$n=count($this->folders); $i<$n; $i++) :
		$this->setFolder($i);
		$this->folderi = $i;
		echo $this->loadTemplate('folder');
	endfor;

	for ($i=0,$n=count($this->files); $i<$n; $i++) :
		$this->setFile($i);
		$this->filei = $i;
		echo $this->loadTemplate('file');
	endfor;
	echo '</div>';

} else {
	echo '<tr>'
	.'<td>&nbsp;</td>'
	.'<td>&nbsp;</td>'
	.'<td>'.Text::_( $this->t['l'].'_THERE_IS_NO_FILE' ).'</td>'
	.'</tr>';
}
echo '</tbody>'
.'</table>';

echo '<div style="border-bottom:1px solid #cccccc;margin-bottom: 10px">&nbsp;</div>';

/*
echo '<ul class="nav nav-tabs" id="configTabs">';


if((int)$this->t['enablemultiple']  >= 0) {
	$label = HTMLHelper::_( 'image', $this->t['i'].'icon-16-upload-multiple.png','') . '&nbsp;'.Text::_($this->t['l'].'_MULTIPLE_UPLOAD');
	echo '<li><a href="#multipleupload" data-bs-toggle="tab">'.$label.'</a></li>';
}

$label = HTMLHelper::_( 'image', $this->t['i'].'icon-16-upload.png','') . '&nbsp;'.Text::_($this->t['l'].'_UPLOAD');
echo '<li><a href="#upload" data-bs-toggle="tab">'.$label.'</a></li>';


$label = HTMLHelper::_( 'image', $this->t['i'].'icon-16-folder.png','') . '&nbsp;'.Text::_($this->t['l'].'_CREATE_FOLDER');
echo '<li><a href="#createfolder" data-bs-toggle="tab">'.$label.'</a></li>';

echo '</ul>';
*/

$activeTab = '';
if (isset($this->t['tab']) && $this->t['tab'] != '') {
	$activeTab = $this->t['tab'];
} else  {
	$activeTab = 'multipleupload';
}

echo $r->startTabs($activeTab);

echo $r->startTabs();

$tabs = array();
$tabs['multipleupload'] = '<span class="ph-cp-item"><i class="phi phi-fs-s phi-fc-bl duotone icon-upload"></i></span>' . '&nbsp;'.Text::_('COM_PHOCACART_MULTIPLE_UPLOAD');
$tabs['upload'] = '<span class="ph-cp-item"><i class="phi phi-fs-s phi-fc-bd duotone icon-upload"></i></span>' . '&nbsp;'.Text::_('COM_PHOCACART_UPLOAD');
$tabs['createfolder'] = '<span class="ph-cp-item"><i class="phi phi-fs-s phi-fc-brd duotone icon-folder"></i></span>' . '&nbsp;'.Text::_('COM_PHOCACART_CREATE_FOLDER');

echo $r->navigation($tabs, $activeTab);

echo $r->startTab('multipleupload', $tabs['multipleupload'], $activeTab == 'multipleupload' ? 'active' : '');
echo $this->loadTemplate('multipleupload');
echo $r->endTab();

echo $r->startTab('upload', $tabs['upload'], $activeTab == 'upload' ? 'active' : '');
echo $this->loadTemplate('upload');
echo $r->endTab();


echo $r->startTab('createfolder', $tabs['createfolder'], $activeTab == 'createfolder' ? 'active' : '');
echo PhocacartFileUpload::renderCreateFolder($this->session->getName(), $this->session->getId(), $this->currentFolder, 'phocacartmanager', 'manager='.$this->manager.'&amp;tab=createfolder&amp;field='. $this->field );
echo $r->endTab();

echo $r->endTabs();

echo '</div>';
echo '</div>';
/*
if ($this->t['tab'] != '') {$jsCt = 'a[href=#'.PhocacartText::filterValue($this->t['tab'], 'alphanumeric') .']';} else {$jsCt = 'a:first';}
echo '<script type="text/javascript">';
echo '   jQuery(\'#configTabs '.$jsCt.'\').tab(\'show\');'; // Select first tab
echo '</script>';
*/

?>

