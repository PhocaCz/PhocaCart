<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

//namespace Joomla\CMS\Form\Field;

defined('_JEXEC') or die();


use Joomla\CMS\Factory;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if (! class_exists('PhocacartCategory')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/category/category.php');
}
if (! class_exists('PhocacartCategoryMultiple')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/category/multiple.php');
}

$lang = Factory::getLanguage();
$lang->load('com_phocacart');

class JFormFieldPhocacartCategory extends ListField
{
	protected $type 		= 'PhocacartCategory';
	protected $layout   = 'phocacart.form.field.category';

	protected function getRenderer($layoutId = 'default')
	{
		// Make field usable outside of Phoca Cart component
		$renderer = parent::getRenderer($layoutId);
		$renderer->addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/layouts');
		return $renderer;
	}

	protected function getInput() {

		$db = Factory::getDBO();

		//$javascript		= '';
		$required		= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		$multiple		= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$class			= ((string) $this->element['class'] != '') ? 'class="'.$this->element['class'].'"' : 'class="form-select"';
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
		$view 	= Factory::getApplication()->input->get( 'view' );
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
				array_unshift($tree, HTMLHelper::_('select.option', '0', Text::_('COM_PHOCACART_NONE'), 'value', 'text'));
				array_unshift($tree, HTMLHelper::_('select.option', '-1', Text::_('COM_PHOCACART_ALL'), 'value', 'text'));
			}
		} else {

			// in filter we need zero value for canceling the filter
			if ($typeMethod == 'filter') {
				array_unshift($tree, HTMLHelper::_('select.option', '', '- ' . Text::_('COM_PHOCACART_SELECT_CATEGORY') . ' -', 'value', 'text'));
			} else {
				array_unshift($tree, HTMLHelper::_('select.option', '0', '- '.Text::_('COM_PHOCACART_SELECT_CATEGORY').' -', 'value', 'text'));
			}
		}


		$data            = $this->getLayoutData();
		$data['options'] = (array)$tree;

		if (!empty($activeCats)) {
			$data['value'] = $activeCats;
		} else {
			$data['value'] = $this->value;
		}

		$data['refreshPage']    = (bool) $this->element['refresh-enabled'];
		$data['refreshCatId']   = (string) $this->element['refresh-cat-id'];
		$data['refreshSection'] = (string) $this->element['refresh-section'];
		$data['hasCustomFields']= !empty(FieldsHelper::getFields('com_phocacart.phocacartitem'));

		return $this->getRenderer($this->layout)->render($data);

	}
}
?>
