<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string|Array $value       Value attribute of the field.
 * @var   array    $options         Options available for this field.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*
 * @var   array    $splitCategoryTypes Show multiple inputs, one per category type
 * @var   array    $categoryTypes   Category types array
 */

Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');

Factory::getApplication()->getDocument()->getWebAssetManager()
    ->usePreset('choicesjs')
    ->useScript('webcomponent.field-fancy-select');

$attr = '';

// Initialize the field attributes.
$attr .= !empty($size) ? ' size="' . $size . '"' : '';
$attr .= $multiple ? ' multiple' : '';
$attr .= $autofocus ? ' autofocus' : '';
$attr .= $onchange ? ' onchange="' . $onchange . '"' : '';
$attr .= $dataAttribute;

// To avoid user's confusion, readonly="readonly" should imply disabled="disabled".
if ($readonly || $disabled) {
    $attr .= ' disabled="disabled"';
}

$attr2  = '';
$attr2 .= ' placeholder="' . $this->escape($hint ?: Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS')) . '" ';

if ($splitCategoryTypes) {
    $name = preg_replace('~\[]$~', '', $name);
    $attr2 .= !empty($class) ? ' class="form-floating ' . $class . '"' : ' class="form-floating"';
} else {
    $categoryTypes = [''];
    $attr2 .= !empty($class) ? ' class="' . $class . '"' : '';
}

foreach ($categoryTypes as $index => $categoryType) {
    $html = [];
    $requiredAttr  = '';
    $requiredAttr2 = '';
    if ($required && $index === 0) {
        $requiredAttr  .= ' required class="required"';
        $requiredAttr2 .= ' required';
    }

    if ($splitCategoryTypes) {
        $nameExt = '[' . $categoryType->id . '][]';
        $idExt = '-' . $categoryType->id;
        $inputOptions = $options[$categoryType->id];
        $inputValue = $value[$categoryType->id] ?? null;
    } else {
        $nameExt = '';
        $idExt = '';
        $inputOptions = $options;
        $inputValue = $value;
    }

    if ($readonly) {
        // Create a read-only list (no name) with hidden input(s) to store the value(s).
        $html[] = HTMLHelper::_('select.genericlist', $inputOptions, '', trim($attr . $requiredAttr), 'value', 'text', $inputValue, $id . $idExt);

        // E.g. form field type tag sends $this->value as array
        if ($multiple && is_array($inputValue)) {
            if (!count($inputValue)) {
                $inputValue[] = '';
            }

            foreach ($inputValue as $val) {
                $html[] = '<input type="hidden" name="' . $name . $nameExt . '" value="' . htmlspecialchars($val, ENT_COMPAT, 'UTF-8') . '">';
            }
        } else {
            $html[] = '<input type="hidden" name="' . $name . $nameExt . '" value="' . htmlspecialchars($inputValue, ENT_COMPAT, 'UTF-8') . '">';
        }
    } else {
        // Create a regular list.
        $html[] = HTMLHelper::_('select.genericlist', $inputOptions, $name . $nameExt, trim($attr . $requiredAttr), 'value', 'text', $inputValue, $id . $idExt);

        if ($refreshPage === true && $hasCustomFields) {
            $attr2 .= ' data-refresh-catid="' . $refreshCatId . '" data-refresh-section="' . $refreshSection . '"';
            $attr2 .= ' onchange="Joomla.categoryHasChanged(this)"';

            Factory::getApplication()->getDocument()->getWebAssetManager()
                ->registerAndUseScript('field.category-change', 'layouts/joomla/form/field/category-change.min.js', [], ['defer' => true], ['core'])
                ->useScript('webcomponent.core-loader');

            // Pass the element id to the javascript
            Factory::getApplication()->getDocument()->addScriptOptions('category-change', $id . $idExt);
        } else {
            $attr2 .= $onchange ? ' onchange="' . $onchange . '"' : '';
        }
    }

    if ($splitCategoryTypes) {
?>
      <div class="input-group">
        <span class="input-group-text bg-light text-dark border-primary-subtle"><?php echo Text::_($categoryType->title); ?></span>
        <joomla-field-fancy-select <?php echo $attr2 . $requiredAttr2; ?>><?php echo implode($html); ?></joomla-field-fancy-select>
      </div>
<?php
    } else {
?>
  <joomla-field-fancy-select <?php echo $attr2 . $requiredAttr2; ?>><?php echo implode($html); ?></joomla-field-fancy-select>
<?php
    }
}
