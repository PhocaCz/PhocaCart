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
use Phoca\PhocaCart\I18n\I18nHelper;

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
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $inputType       Options available for this field.
 * @var   string   $accept          File types that are accepted.
 * @var   boolean  $charcounter     Does this field support a character counter?
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*.
 * @var   boolean  $i18n            I18n support?
 * @var   array    $languages       Languages list for i18n.
 * @var   string   $defLanguage     Default language for i18n.
 */

// Initialize some field attributes.
if ($charcounter) {
    // Load the js file
    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wa->useScript('short-and-sweet');

    // Set the css class to be used as the trigger
    $charcounter = ' charcount';
    // Set the text
    $counterlabel = 'data-counter-label="' . $this->escape(Text::_('JFIELD_META_DESCRIPTION_COUNTER')) . '"';
}

$attributes = [
    $columns ?: '',
    $rows ?: '',
    !empty($class) ? 'class="form-control ' . $class . $charcounter . '"' : 'class="form-control' . $charcounter . '"',
    !empty($description) ? 'aria-describedby="' . ($id ?: $name) . '-desc"' : '',
    strlen($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : '',
    $disabled ? 'disabled' : '',
    $readonly ? 'readonly' : '',
    $onchange ? 'onchange="' . $onchange . '"' : '',
    $onclick ? 'onclick="' . $onclick . '"' : '',
    $required ? 'required' : '',
    !empty($autocomplete) ? 'autocomplete="' . $autocomplete . '"' : '',
    $autofocus ? 'autofocus' : '',
    $spellcheck ? '' : 'spellcheck="false"',
    $maxlength ?: '',
    !empty($counterlabel) ? $counterlabel : '',
    $dataAttribute,
];
?>
<?php if($i18n) : ?>
    <?php echo HTMLHelper::_('uitab.startTabSet', $id . '_i18nTabs', ['recall' => true, 'breakpoint' => 768]); ?>
<?php endif; ?>
<?php foreach($languages as $language) : ?>
    <?php if($i18n) : ?>
        <?php
          $tabTitle = HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', ['class' => 'me-1'], true)
              . $language->title
              . ' ' . I18nHelper::getEditorIcon($language->lang_code, $value);
          $tabTitle = '<span title="' . I18nHelper::getEditorIconTitle($language->lang_code, $value) . '">' . $tabTitle . '</span>';

          echo HTMLHelper::_('uitab.addTab', $id . '_i18nTabs', $language->lang_code, $tabTitle);
        ?>
    <?php endif; ?>

    <textarea name="<?php echo $name .  ($i18n ? '[' . $language->lang_code . ']' : ''); ?>"
        id="<?php echo $id . ($i18n ? '-' . $language->lang_code : ''); ?>" <?php echo implode(' ', $attributes); ?>
    ><?php echo htmlspecialchars($i18n ? $value[$language->lang_code] ?? '' : $value, ENT_COMPAT, 'UTF-8'); ?></textarea>

    <?php if($i18n) : ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>
<?php endforeach; ?>

<?php if($i18n) : ?>
    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
<?php endif; ?>
