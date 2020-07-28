<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$group 	= PhocacartUtilsSettings::getManagerGroup($this->manager);

if ($this->currentFolder === '' || ($this->currentFolder == $this->downloadFolder)) {
    // Don't display arrow up when we are in root or download file root (download file root = download token folder)

} else {
    $link = 'index.php?option='.$this->t['o'].'&amp;view='. PhocacartText::filterValue($this->t['task'], 'alphanumeric').'&amp;manager='.PhocacartText::filterValue($this->manager, 'alphanumeric') . $group['c'] .'&amp;folder='.PhocacartText::filterValue($this->folderstate->parent, 'folderpath') .'&amp;field='. PhocacartText::filterValue($this->field, 'alphanumeric2');
echo '<tr><td>&nbsp;</td>'
.'<td class="ph-img-table">'
.'<a href="'.$link.'" >'
. Joomla\CMS\HTML\HTMLHelper::_( 'image', $this->t['i'].'icon-16-up.png', '').'</a>'
.'</td>'
.'<td><a href="'.$link.'" >..</a></td>'
.'</tr>';
}

