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

		//$javascript		= '';
		$required		= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		$multiple		= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$class			= ((string) $this->element['class'] != '') ? 'class="'.$this->element['class'].'"' : 'class="inputbox"';
		$typeMethod		= $this->element['typemethod'];
		$categoryType	= $this->element['categorytype'];// 0 all, 1 ... online shop, 2 ... pos
		$attr		= '';
		$attr		.= $class . ' ';
		if ($multiple) {
			$attr		.= 'size="4" multiple="multiple" ';
		}
		if ($required) {
			$attr		.= 'required aria-required="true" ';
		}

		$attr 		.= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'" ' : ' ';
		//$attr		.= $javascript . ' ';


		// Multiple load more values
		$activeCats = array();
		$id 		= 0;
		// Active cats can be selected in administration item view
		// but this function is even called in module so ignore this part for module administration
		if ($multiple && $this->form->getName() == 'com_phocacart.phocacartitem') {
			$id = (int) $this->form->getValue('id');// Product ID
			if ((int)$id > 0) {
				$activeCats	= PhocacartCategoryMultiple::getCategories($id, 1);

			}
		}


		// Filter language
        //$whereLang = '';
		$wheres = array();
        if (!empty($this->element['language'])) {
            if (strpos($this->element['language'], ',') !== false)
            {
                $language = implode(',', $db->quote(explode(',', $this->element['language'])));
            }
            else
            {
                $language = $db->quote($this->element['language']);
            }

            $wheres[] = ' '.$db->quoteName('a.language') . ' IN (' . $language . ')';
        }


       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value, a.parent_id as parentid'
		. ' FROM #__phocacart_categories AS a';

        // don't lose information about category when it will be unpublished - you should still be able to edit product with such category in administration
		//. ' WHERE a.published = 1';
		switch($categoryType) {

			case 1:
				$wheres[] = ' a.type IN (0,1)';
			break;

			case 2:
				$wheres[] = ' a.type IN (0,2)';
			break;


			case 0:
			default:

			break;

		}

		$query .= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );


		$query .= ' ORDER BY a.ordering';

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
			if ($typeMethod == 'allnone') {
				array_unshift($tree, Joomla\CMS\HTML\HTMLHelper::_('select.option', '0', JText::_('COM_PHOCACART_NONE'), 'value', 'text'));
				array_unshift($tree, Joomla\CMS\HTML\HTMLHelper::_('select.option', '-1', JText::_('COM_PHOCACART_ALL'), 'value', 'text'));
			}
		} else {
			array_unshift($tree, Joomla\CMS\HTML\HTMLHelper::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_CATEGORY').' -', 'value', 'text'));
		}

		if (!empty($activeCats)) {
			return Joomla\CMS\HTML\HTMLHelper::_('select.genericlist',  $tree,  $this->name, $attr, 'value', 'text', $activeCats, $this->id );

		} else {
			return Joomla\CMS\HTML\HTMLHelper::_('select.genericlist',  $tree,  $this->name, $attr, 'value', 'text', $this->value, $this->id );
		}

	}
}
?>
