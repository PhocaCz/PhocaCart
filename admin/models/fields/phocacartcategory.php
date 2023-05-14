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

require_once(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php');
Factory::getApplication()->getLanguage()->load('com_phocacart');

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

  private function buildCategoryTree(array &$options, array $categories, string $treeTitle, array $typeFilter, array $langFilter, array $omitIds): void {
    foreach ($categories as $category) {
      if ($typeFilter && !in_array($category->type, $typeFilter)) continue;
      if ($langFilter && !in_array($category->language, $langFilter)) continue;
      if ($omitIds && in_array($category->id, $omitIds)) continue;

      $title = ($treeTitle ? $treeTitle . ' - ' : '') . $category->title;
      $options[] = (object)[
        'text' => $title . ($category->language === '*' ? '' : ' (' . $category->language . ')'),
        'value' => $category->id,
      ];
      if ($category->children)
        $this->buildCategoryTree($options, $category->children, $title, $typeFilter, $langFilter, $omitIds);
    }
  }

	protected function getInput() {
		$db = Factory::getDBO();

		$multiple		= (string)$this->element['multiple'] == 'true';
		$typeMethod		= $this->element['typemethod'];

    switch($this->element['categorytype']) {
      case 1:
        $typeFilter = [0, 1];
        break;
      case 2:
        $typeFilter = [0, 2];
        break;
      case 0:
      default:
        $typeFilter = [];
        break;
    }

    if ($this->element['language']) {
      $langFilter = explode(',', $this->element['language']);
    } elseif ($this->form->getValue('language', 'filter')) {
      $langFilter = [$this->form->getValue('language', 'filter')];
    } else {
      $langFilter = [];
    }

    // TO DO - check for other views than category edit
    $omitIds = [];
    switch (Factory::getApplication()->input->get('view')) {
      case 'phocacartcategory':
        if ($this->form->getValue('id') > 0)
          $omitIds[] = $this->form->getValue('id');
        break;
    }

    $rootCategories = array_filter(PhocacartCategory::getCategories(), function($category) use ($typeFilter, $langFilter, $omitIds) {
      return !$category->parent_id;
    });

    $options = [];
    if ($multiple) {
      if ($typeMethod == 'allnone') {
        $options[] = HTMLHelper::_('select.option', '0', Text::_('COM_PHOCACART_NONE'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '-1', Text::_('COM_PHOCACART_ALL'), 'value', 'text');
      }
    } else {
      // in filter we need zero value for canceling the filter
      if ($typeMethod == 'filter') {
        $options[] = HTMLHelper::_('select.option', '', '- ' . Text::_('COM_PHOCACART_SELECT_CATEGORY') . ' -', 'value', 'text');
      } else {
        $options[] = HTMLHelper::_('select.option', '0', '- '.Text::_('COM_PHOCACART_SELECT_CATEGORY').' -', 'value', 'text');
      }
    }
    $this->buildCategoryTree($options, $rootCategories, '', $typeFilter, $langFilter, $omitIds);

		$data = $this->getLayoutData();
		$data['options'] = $options;

		if (!empty($activeCats)) {
			$data['value'] = $activeCats;
		} else {
			$data['value'] = $this->value;
		}

		$data['refreshPage']    = (bool)$this->element['refresh-enabled'];
		$data['refreshCatId']   = (string)$this->element['refresh-cat-id'];
		$data['refreshSection'] = (string)$this->element['refresh-section'];
		$data['hasCustomFields']= !empty(FieldsHelper::getFields('com_phocacart.phocacartitem'));

		return $this->getRenderer($this->layout)->render($data);
	}
}

