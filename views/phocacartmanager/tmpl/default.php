<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$r 			=  new PhocaCartRenderAdminView();

echo '<div id="phocacartmanager">';
echo '<div class="span12 form-horizontal">';
echo '<div class="ph-admin-path">' . JText::_('COM_PHOCACART_PATH'). ': '.JPath::clean($this->t['path']['orig_abs_ds']. $this->folderstate->folder) .'</div>';
//$countFaF =  count($this->images) + count($this->folders);
echo '<table class="table table-hover table-condensed ph-multiple-table">'
.'<thead>'
.'<tr>';
echo '<th class=" ph-check">'. "\n";
//echo '<input type="checkbox" name="checkall-toggle" value="" title="'.JText::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />'. "\n";
echo '</th>'. "\n";
echo '<th width="20">&nbsp;</th>'
.'<th width="95%">'.JText::_( $this->t['l'].'_FILENAME' ).'</th>'
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
	.'<td>'.JText::_( $this->t['l'].'_THERE_IS_NO_FILE' ).'</td>'
	.'</tr>';			
}
echo '</tbody>'
.'</table>';

echo '<div style="border-bottom:1px solid #cccccc;margin-bottom: 10px">&nbsp;</div>';

echo '<ul class="nav nav-tabs" id="configTabs">';

$label = JHTML::_( 'image', $this->t['i'].'icon-16-upload.png','') . '&nbsp;'.JText::_($this->t['l'].'_UPLOAD');
echo '<li><a href="#upload" data-toggle="tab">'.$label.'</a></li>';

if((int)$this->t['enablemultiple']  >= 0) {
	$label = JHtml::_( 'image', $this->t['i'].'icon-16-upload-multiple.png','') . '&nbsp;'.JText::_($this->t['l'].'_MULTIPLE_UPLOAD');
	echo '<li><a href="#multipleupload" data-toggle="tab">'.$label.'</a></li>';
}

$label = JHtml::_( 'image', $this->t['i'].'icon-16-folder.png','') . '&nbsp;'.JText::_($this->t['l'].'_CREATE_FOLDER');
echo '<li><a href="#createfolder" data-toggle="tab">'.$label.'</a></li>';

echo '</ul>';


echo '<div class="tab-content">'. "\n";

echo '<div class="tab-pane" id="upload">'. "\n";
echo $this->loadTemplate('upload');
echo '</div>'. "\n";
echo '<div class="tab-pane" id="multipleupload">'. "\n";
echo $this->loadTemplate('multipleupload');
echo '</div>'. "\n";

echo '<div class="tab-pane" id="createfolder">'. "\n";
echo PhocaCartFileUpload::renderCreateFolder($this->session->getName(), $this->session->getId(), $this->currentFolder, 'phocacartmanager', 'manager='.$this->manager.'&amp;tab=createfolder&amp;field='. $this->field );
echo '</div>'. "\n";

echo '</div>'. "\n";

echo '</div>';
echo '</div>';

if ($this->t['tab'] != '') {$jsCt = 'a[href=#'.$this->t['tab'] .']';} else {$jsCt = 'a:first';}
echo '<script type="text/javascript">';
echo '   jQuery(\'#configTabs '.$jsCt.'\').tab(\'show\');'; // Select first tab
echo '</script>';
?>
