<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

jimport( 'joomla.filesystem.file' );

$ext 	= PhocacartFile::getExtension( $this->_tmp_file->path_without_name_relative );
$group 	= PhocacartUtilsSettings::getManagerGroup($this->manager);

if ($this->manager == 'productimage' || $this->manager == 'categoryimage') {

	/* 	Own function - this function is used for e.g. additional images, etc. for input forms which
	   	are rendered by javascript - addtional images, attribute options (values), etc.
		Do request - create thumbnails = yes
		There are more form fields mostly made by javascript (e.g. add new attribute row)
	*/

	$path 		= PhocacartPath::getPath($this->manager);
	$pathImage 	= Juri::root() . $path['orig_rel_ds'];
	$url 		= 'index.php?option=com_phocacart&view=phocacartthumba&format=json&tmpl=component&'. JSession::getFormToken().'=1';
	$jsParams = '{'
		.' request: 1,'
		.' manager: \''.$this->manager.'\','
		.' pathimage: \''.strip_tags(addslashes($pathImage)).'\','
		.' requesturl: \''.$url.'\','
		.' requestmsg: \''.strip_tags(addslashes(JText::_('COM_PHOCACART_CHECKING_IMAGE_THUMBNAIL_PLEASE_WAIT'))).'\''
		.'}';

	$onclick= 'if (window.parent) window.parent.phAddValueImage(\''.PhocacartText::filterValue($this->field, 'alphanumeric2').'\', \'' .PhocacartText::filterValue($this->_tmp_file->path_with_name_relative_no, 'folderpath').'\', '.$jsParams.')';

} else if ($this->manager == 'attributefile'){

	// Skip doing request - no thumbnails for downloadable files
	// There are more form fields mostly made by javascript (e.g. add new attribute row)

	/*$path 		= PhocacartPath::getPath($this->manager);
	$pathImage 	= Juri::root() . $path['orig_rel_ds'];
	$url 		= 'index.php?option=com_phocacart&view=phocacartthumba&format=json&tmpl=component&'. JSession::getFormToken().'=1';
	$jsParams = '{
		request: 1,
		manager: "'.$this->manager.'",
		pathimage: "'.strip_tags(addslashes($pathImage)).'",
		requesturl: "'.$url.'",
		requestmsg: "'.strip_tags(addslashes(JText::_('COM_PHOCACART_CHECKING_IMAGE_THUMBNAIL_PLEASE_WAIT'))).'"
	}';*/

	$onclick= 'if (window.parent) window.parent.phAddValueFile(\''.PhocacartText::filterValue($this->field, 'alphanumeric2').'\', \'' .PhocacartText::filterValue($this->_tmp_file->path_with_name_relative_no, 'folderpath').'\')';

} else {
	// Form field is one and the function is set for this form field
    // productfile, publicfile
	$onclick= 'if (window.parent) window.parent.phAddValueFile(\''.PhocacartText::filterValue($this->field, 'alphanumeric2').'\', \'' .PhocacartText::filterValue($this->_tmp_file->path_with_name_relative_no, 'folderpath').'\')';
}

if ($this->manager == 'filemultiple') {
	$checked 	= Joomla\CMS\HTML\HTMLHelper::_('grid.id', $this->filei + count($this->folders), $this->files[$this->filei]->path_with_name_relative_no );

	$icon		= PhocacartFile::getMimeTypeIcon($this->_tmp_file->name);
	echo '<tr>'
	.' <td>'. $checked .'</td>'
	.' <td class="ph-img-table">'
	. $icon .'</a></td>'
	.' <td>' . $this->_tmp_file->name . '</td>'
	.'</tr>';


} else {
	if (($group['i'] == 1) && ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'jpeg' || $ext = 'webp') ) {

		echo '<tr>'
		.'<td></td>'
		.'<td>'
		.'<a href="#" onclick="'.$onclick.'">'
		. Joomla\CMS\HTML\HTMLHelper::_( 'image', str_replace( '../', '', $this->_tmp_file->path_without_name_relative), JText::_('COM_PHOCACART_INSERT'), array('title' => JText::_('COM_PHOCACART_INSERT_IMAGE'), 'width' => '16', 'height' => '16'))
		.'</a>'
		.' <td>'
		.'<a href="#" onclick="'.$onclick.'">'
		. $this->_tmp_file->name
		.'</a>'
		.'</td>'
		.'</tr>';

	} else {

		echo '<tr>'
		.'<td></td>'
		.'<td>'
		.'<a href="#" onclick="'.$onclick.'">'
		. Joomla\CMS\HTML\HTMLHelper::_( 'image', $this->t['i'].'icon-16-file-insert.png', '', JText::_('COM_PHOCACART_INSERT_FILENAME'))
		.'</a>'
		.' <td>'
		.'<a href="#" onclick="'.$onclick.'">'
		. $this->_tmp_file->name
		.'</a>'
		.'</td>'
		.'</tr>';
	}
}
?>
