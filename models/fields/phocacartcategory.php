<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (! class_exists('PhocacartCategory')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/category/category.php');
}
if (! class_exists('PhocacartCategoryMultiple')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/category/multiple.php');
}

$lang = JFactory::getLanguage();
$lang->load('com_phocacart');

class JFormFieldPhocacartCategory extends JFormField
{
	protected $type 		= 'PhocacartCategory';

	protected function getInput() {
		
		$db = JFactory::getDBO();
		
		$javascript	= '';
		$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		$multiple	= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$attr		= '';
		$attr		.= 'class="inputbox" ';
		if ($multiple) {
			$attr		.= 'size="4" multiple="multiple" ';
		}
		if ($required) {
			$attr		.= 'required aria-required="true" ';
		}
		$attr		.= $javascript . ' ';
		
		
		// Multiple load more values
		$activeCats = array();
		$id 		= 0;
		if ($multiple) {
			$id = (int) $this->form->getValue('id');
			if ((int)$id > 0) {
				$activeCats	= PhocacartCategoryMultiple::getCategories($id, 1);
			}
		}

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value, a.parent_id as parentid'
		. ' FROM #__phocacart_categories AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();
	
		// TO DO - check for other views than category edit
		$view 	= JFactory::getApplication()->input->get( 'view' );
		$catId	= -1;
		if ($view == 'phocacartcategory') {
			$id 	= $this->form->getValue('id'); // id of current category
			if ((int)$id > 0) {
				$catId = $id;
			}
		}
		
		
		
		
		$tree = array();
		$text = '';
		$tree = PhocacartCategory::CategoryTreeOption($data, $tree, 0, $text, $catId);
		
		if ($multiple) {
		
		} else {
			array_unshift($tree, JHTML::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_CATEGORY').' -', 'value', 'text'));
		}

		if (!empty($activeCats)) {
			return JHTML::_('select.genericlist',  $tree,  $this->name, $attr, 'value', 'text', $activeCats, $this->id );
		
		} else {
			return JHTML::_('select.genericlist',  $tree,  $this->name, $attr, 'value', 'text', $this->value, $this->id );
		}
		
	}
}
?>