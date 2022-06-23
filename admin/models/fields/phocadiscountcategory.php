<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

if (! class_exists('PhocacartCategory')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/phocacart/category/category.php');
}

class JFormFieldPhocaDiscountCategory extends FormField
{
	protected $type 		= 'PhocaDiscountCategory';

	protected function getInput() {

		$db = Factory::getDBO();

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value, a.parent_id as parentid'
		. ' FROM #__phocacart_categories AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();

		$id 	= $this->form->getValue('id');
		$catId	= -1;


		$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		$javascript = '';
		$tree = array();
		$text = '';
		$tree = PhocacartCategory::CategoryTreeOption($data, $tree, 0, $text, $catId);


		$relatedOption = array();
		if ((int)$id > 0) {
			$relatedOption	= PhocacartDiscountCart::getDiscountCatsById((int)$id);
		}


		$data               = $this->getLayoutData();
		$data['options']    = (array)$tree;
		$data['value']      = $relatedOption;

		return $this->getRenderer($this->layout)->render($data);


		//return HTMLHelper::_('select.genericlist', $tree, $this->name, 'class="form-select" size="4" multiple="multiple"'. $javascript, 'value', 'text', $relatedOption, $id);
	}
}
?>
