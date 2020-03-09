<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined('_JEXEC') or die();
class JFormFieldPhocaTags extends JFormField
{
	protected $type 		= 'PhocaTags';

	protected function getInput() {
		
		$id 	= (int) $this->form->getValue('id');
		$class	= ((string) $this->element['class'] != '') ? 'class="'.$this->element['class'].'"' : 'class="inputbox"';

		$activeTags = array();
		if ((int)$id > 0) {
			$activeTags	= PhocacartTag::getTags($id, 1);
		}
		
		return PhocacartTag::getAllTagsSelectBox($this->name, $this->id, $activeTags, NULL, 'id', 0, $class );
	}
}
?>