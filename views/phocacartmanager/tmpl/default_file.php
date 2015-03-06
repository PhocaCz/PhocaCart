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

$ext 	= PhocaCartFile::getExtension( $this->_tmp_file->path_without_name_relative );
$group 	= PhocaCartSettings::getManagerGroup($this->manager);

if ($this->manager == 'productimage' || $this->manager == 'categoryimage') {
	/* Own function - this function is used for e.g. additional images, etc. for input forms which 
	   are rendered by javascript - addtional images, attribute options (values), etc. */
	$onclick= 'if (window.parent) window.parent.phAddValue(\''.$this->field.'\', \'' .$this->_tmp_file->path_with_name_relative_no.'\')';
} else {
	$onclick= 'if (window.parent) window.parent.'. $this->fce.'(\'' .$this->_tmp_file->path_with_name_relative_no.'\')';
}


if ($this->manager == 'filemultiple') {
	$checked 	= JHTML::_('grid.id', $this->filei + count($this->folders), $this->files[$this->filei]->path_with_name_relative_no );
	
	$icon		= PhocaCartFile::getMimeTypeIcon($this->_tmp_file->name);
	echo '<tr>'
	.' <td>'. $checked .'</td>'
	.' <td class="ph-img-table">'
	. $icon .'</a></td>'
	.' <td>' . $this->_tmp_file->name . '</td>'
	.'</tr>';
	
	
} else {
	if (($group['i'] == 1) && ($ext == 'png' || $ext == 'jpg' || $ext == 'gif' || $ext == 'jpeg') ) {
		
		echo '<tr>'
		.'<td></td>'
		.'<td>'
		.'<a href="#" onclick="'.$onclick.'">'
		. JHTML::_( 'image', str_replace( '../', '', $this->_tmp_file->path_without_name_relative), JText::_('COM_PHOCACART_INSERT'), array('title' => JText::_('COM_PHOCACART_INSERT_IMAGE'), 'width' => '16', 'height' => '16'))
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
		. JHTML::_( 'image', $this->t['i'].'icon-16-file-insert.png', '', JText::_('COM_PHOCACART_INSERT_FILENAME'))
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
