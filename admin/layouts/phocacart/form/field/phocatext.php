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
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*.
 * @var   string   $dirname         The directory name
 * @var   string   $addonBefore     The text to use in a bootstrap input group prepend
 * @var   string   $addonAfter      The text to use in a bootstrap input group append
 * @var   boolean  $charcounter     Does this field support a character counter?
 * @var   boolean  $showCopyButton  Show copy button?
 * @var   boolean  $showLinkButton  Show link button?
 * @var   boolean  $showTranslation Show value translation?
 * @var   boolean  $i18n            I18n support?
 * @var   array    $languages       Languages list for i18n.
 * @var   string   $defLanguage     Default language for i18n.
 */

$list = '';

if ($options) {
    $list = 'list="' . $id . '_datalist"';
}

$charcounterclass = '';

if ($charcounter) {
    // Load the js file
    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wa->useScript('short-and-sweet');

    // Set the css class to be used as the trigger
    $charcounterclass = ' charcount';

    // Set the text
    $counterlabel = 'data-counter-label="' . $this->escape(Text::_('JFIELD_META_DESCRIPTION_COUNTER')) . '"';
}

if ($class) {
    $class = trim(str_replace('required', '', $class));
}

$attributes = [
    !empty($size) ? 'size="' . $size . '"' : '',
    !empty($description) ? 'aria-describedby="' . ($id ?: $name) . '-desc"' : '',
    $disabled ? 'disabled' : '',
    $readonly ? 'readonly' : '',
    $dataAttribute,
    $list,
    strlen($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : '',
    $onchange ? ' onchange="' . $onchange . '"' : '',
    !empty($maxLength) ? $maxLength : '',
    !empty($autocomplete) ? 'autocomplete="' . $autocomplete . '"' : '',
    $autofocus ? ' autofocus' : '',
    $spellcheck ? '' : 'spellcheck="false"',
    !empty($inputmode) ? $inputmode : '',
    !empty($counterlabel) ? $counterlabel : '',
    !empty($pattern) ? 'pattern="' . $pattern . '"' : '',

    // @TODO add a proper string here!!!
    !empty($validationtext) ? 'data-validation-text="' . $validationtext . '"' : '',
];

$defLangAttributes = $attributes;
$attributes[] = !empty($class) ? 'class="form-control ' . $class . $charcounterclass . '"' : 'class="form-control' . $charcounterclass . '"';

$requiredClass = '';
if ($required) {
    $requiredClass = ' required';
}
$defLangAttributes[] = !empty($class) ? 'class="form-control ' . $class . $charcounterclass . $requiredClass . '"' : 'class="form-control' . $charcounterclass . $requiredClass . '"';
$defLangAttributes[] = $required ? 'required' : '';

$addonBeforeHtml = '';
if ($addonBefore) {
    $addonBeforeHtml = '<span class="input-group-text">' . Text::_($addonBefore) . '</span>';
}
$addonAfterHtml  = '';
if ($addonAfter) {
    $addonAfterHtml = '<span class="input-group-text">' . Text::_($addonAfter) . '</span>';
}
if ($showCopyButton) {
    $addonAfterHtml .= '<button type="button" class="btn btn-primary" onclick="let el = document.getElementById(\'' . $id . '\'); el.select(); el.setSelectionRange(0, 99999); document.execCommand(\'copy\'); return false;"><span class="icon icon-copy"></span></button>';
}
if ($showLinkButton) {
    $addonAfterHtml .= '<a href="#" class="btn btn-primary" target="_blank" onclick="let el = document.getElementById(\'' . $id . '\'); this.setAttribute(\'href\', el.value); return true;"><span class="icon icon-link"></span></a>';
}
if ($showTranslation) {
    $addonAfterHtml .= '<span class="input-group-text bg-light text-dark border-primary-subtle"><span class="icon icon-language me-2"></span> ' . Text::_($value) . '</span>';
}
?>

<?php foreach($languages as $language) : ?>
  <?php if ($addonBeforeHtml || $addonAfterHtml || $i18n) : ?>
  <div class="input-group">
  <?php endif; ?>

    <?php if ($i18n) : ?>
      <span class="input-group-text bg-light text-dark border-primary-subtle">
        <?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', ['class' => 'me-1'], true); ?>
      </span>
    <?php endif; ?>

    <?php echo $addonBeforeHtml; ?>

    <input
        type="text"
        name="<?php echo $name . ($i18n ? '[' . $language->lang_code . ']' : ''); ?>"
        id="<?php echo $id . ($i18n ? '-' . $language->lang_code : ''); ?>"
        value="<?php echo htmlspecialchars($i18n ? $value[$language->lang_code] ?? '' : $value, ENT_COMPAT, 'UTF-8'); ?>"
        <?php echo $dirname; ?>
        <?php echo implode(' ', !$i18n || $language->lang_code !== $defLanguage ? $attributes : $defLangAttributes); ?>>

    <?php if ($i18n) : ?>
      <span class="input-group-text input-group-i18n bg-light text-dark border-primary-subtle" title="<?php echo I18nHelper::getEditorIconTitle($language->lang_code, $value); ?>">
        <?php echo I18nHelper::getEditorIcon($language->lang_code, $value); ?>
      </span>
    <?php endif;  ?>

    <?php echo $addonAfterHtml; ?>

  <?php if ($addonBeforeHtml || $addonAfterHtml || $i18n) : ?>
  </div>
  <?php endif; ?>

  <?php if ($options) : ?>
    <datalist id="<?php echo $id . ($i18n ? '-' . $language->lang_code : ''); ?>_datalist">
        <?php foreach ($options as $option) : ?>
            <?php if (!$option->value) : ?>
                <?php continue; ?>
            <?php endif; ?>
            <option value="<?php echo $option->value; ?>"><?php echo $option->text; ?></option>
        <?php endforeach; ?>
    </datalist>
  <?php endif; ?>
<?php endforeach; ?>
