<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$group 	= PhocaCartSettings::getManagerGroup($this->manager);
$link = 'index.php?option='.$this->t['o'].'&amp;view='.$this->t['task'].'&amp;manager='.$this->manager . $group['c'] .'&amp;folder='.$this->folderstate->parent .'&amp;field='. $this->field;
echo '<tr><td>&nbsp;</td>'
.'<td class="ph-img-table">'
.'<a href="'.$link.'" >'
. JHTML::_( 'image', $this->t['i'].'icon-16-up.png', '').'</a>'
.'</td>'
.'<td><a href="'.$link.'" >..</a></td>'
.'</tr>';