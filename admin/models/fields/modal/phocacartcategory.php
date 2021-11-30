<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('JPATH_BASE') or die;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

use Joomla\CMS\Language\LanguageHelper;


class JFormFieldModal_Phocacartcategory extends FormField
{

	protected $type = 'Modal_Phocacartcategory';
	protected $allowSelect = true;
	protected $allowClear = true;
	protected $allowNew = false;
	protected $allowEdit = false;
	protected $allowPropagate = false;

	public function __get($name)
	{
		switch ($name)
		{
			case 'allowSelect':
			case 'allowClear':
			case 'allowNew':
			case 'allowEdit':
			case 'allowPropagate':
				return $this->$name;
		}

		return parent::__get($name);
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'allowSelect':
			case 'allowClear':
			case 'allowNew':
			case 'allowEdit':
			case 'allowPropagate':
				$value = (string) $value;
				$this->$name = !($value === 'false' || $value === 'off' || $value === '0');
				break;

			default:
				parent::__set($name, $value);
		}
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->allowSelect = ((string) $this->element['select']) !== 'false';
			$this->allowClear = ((string) $this->element['clear']) !== 'false';
			$this->allowPropagate = ((string) $this->element['propagate']) === 'true';

			// Creating/editing menu items is not supported in frontend.
			$isAdministrator = Factory::getApplication()->isClient('administrator');
			$this->allowNew = $isAdministrator ? ((string) $this->element['new']) === 'true' : false;
			$this->allowEdit = $isAdministrator ? ((string) $this->element['edit']) === 'true' : false;
		}

		return $return;
	}


	protected function getInput()
	{

        $clientId    = (int) $this->element['clientid'];
		$languages   = LanguageHelper::getContentLanguages(array(0, 1), false);

		// Load language
		Factory::getLanguage()->load('com_phocacart', JPATH_ADMINISTRATOR);

		// The active article id field.
		$value = (int) $this->value ?: '';

		// Create the modal id.
		$modalId = 'Phocacartcategory_' . $this->id;

		/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

		// Add the modal field script to the document head.
		$wa->useScript('field.modal-fields');

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($this->allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				$wa->addInlineScript("
				window.jSelectPhocacartcategory_" . $this->id . " = function (id, title, object) {
					window.processModalSelect('Phocacartcategory', '" . $this->id . "', id, title, '', object);
				}",
					[],
					['type' => 'module']
				);

				Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkCategories = 'index.php?option=com_phocacart&amp;view=phocacartcategories&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
		$linkCategory  = 'index.php?option=com_phocacart&amp;view=phocacartcategory&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
		$modalTitle   = Text::_('COM_PHOCACART_CHANGE_CATEGORY');

		if (isset($this->element['language']))
		{
			$linkCategories .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkCategory   .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle     .= ' &#8212; ' . $this->element['label'];
		}

		$urlSelect = $linkCategories . '&amp;function=jSelectPhocacartcategory_' . $this->id;
		$urlEdit   = $linkCategory . '&amp;task=phocacartcategory.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
		$urlNew    = $linkCategory . '&amp;task=phocacartcategory.add';

		if ($value)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__phocacart_categories'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		// Placeholder if option is present or not
		if (empty($title))
		{
			if ($this->element->option && (string) $this->element->option['value'] == '')
			{
				$title_holder = Text::_($this->element->option);
			}
			else
			{
				$title_holder = Text::_('COM_PHOCACART_SELECT_A_CATEGORY');
			}
		}

		$title = empty($title) ? $title_holder : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current product display field.
		//$html  = '<span class="input-append input-group">';
		//$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// The current menu item display field.
		$html  = '';

		if ($this->allowSelect || $this->allowNew || $this->allowEdit || $this->allowClear)
		{
			$html .= '<span class="input-group">';
		}

		$html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35">';

		// Select category button
		if ($this->allowSelect)
		{
			$html .= '<button'
				. ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-bs-toggle="modal"'
				. ' type="button"'
				. ' data-bs-target="#ModalSelect' . $modalId . '">'
				. '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
				. '</button>';
		}

		// New category button
		if ($this->allowNew)
		{
			$html .= '<button'
				. ' class="btn btn-secondary' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-bs-toggle="modal"'
				. ' type="button"'
				. ' data-bs-target="#ModalNew' . $modalId . '" >'
				. '<span class="icon-plus" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
				. '</button>';
		}

		// Edit category button
		if ($this->allowEdit)
		{
			$html .= '<button'
				. ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-bs-toggle="modal"'
				. ' type="button"'
				. ' data-bs-target="#ModalEdit' . $modalId . '">'
				. '<span class="icon-pen-square" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
				. '</button>';
		}

		// Clear category button
		if ($this->allowClear)
		{
			$html .= '<button'
				. ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' type="button"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
				. '</button>';
		}

		// Propagate category button
		if ($this->allowPropagate && count($languages) > 2)
		{
			// Strip off language tag at the end
			$tagLength = (int) strlen($this->element['language']);
			$callbackFunctionStem = substr("jSelectPhocacartcategory_" . $this->id, 0, -$tagLength);

			$html .= '<button'
			. ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
			. ' type="button"'
			. ' id="' . $this->id . '_propagate"'
			. ' title="' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP') . '"'
			. ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
			. '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON')
			. '</button>';
		}

		if ($this->allowSelect || $this->allowNew || $this->allowEdit || $this->allowClear)
		{
			$html .= '</span>';
		}

		// Select category modal
		if ($this->allowSelect)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'       => $modalTitle,
					'url'         => $urlSelect,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
										. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
				)
			);
		}

		// New category modal
        // phocacartcategory-form => adminForm
		if ($this->allowNew)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalNew' . $modalId,
				array(
					'title'       => Text::_('COM_PHOCACART_NEW_CATEGORY'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<button type="button" class="btn btn-secondary"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'phocacartcategory\', \'cancel\', \'adminForm\'); return false;">'
							. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
							. '<button type="button" class="btn btn-primary"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'phocacartcategory\', \'save\', \'adminForm\'); return false;">'
							. Text::_('JSAVE') . '</button>'
							. '<button type="button" class="btn btn-success"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'phocacartcategory\', \'apply\', \'adminForm\'); return false;">'
							. Text::_('JAPPLY') . '</button>',
				)
			);
		}

		// Edit category modal.
        // phocacartcategory-form => adminForm
		if ($this->allowEdit)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalEdit' . $modalId,
				array(
					'title'       => Text::_('COM_PHOCACART_EDIT_CATEGORY'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlEdit,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<button type="button" class="btn btn-secondary"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'phocacartcategory\', \'cancel\', \'adminForm\'); return false;">'
							. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
							. '<button type="button" class="btn btn-primary"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'phocacartcategory\', \'save\', \'adminForm\'); return false;">'
							. Text::_('JSAVE') . '</button>'
							. '<button type="button" class="btn btn-success"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'phocacarcategory\', \'apply\', \'adminForm\'); return false;">'
							. Text::_('JAPPLY') . '</button>',
				)
			);
		}

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		// Placeholder if option is present or not when clearing field
		if ($this->element->option && (string) $this->element->option['value'] == '')
		{
			$title_holder = Text::_($this->element->option);
		}
		else
		{
			$title_holder = Text::_('COM_PHOCACART_SELECT_A_CATEGORY');
		}

		$html .= '<input type="hidden" id="' . $this->id . '_id"' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars($title_holder, ENT_COMPAT, 'UTF-8') . '" value="' . $value . '">';

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.4
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_name', parent::getLabel());
	}
}
