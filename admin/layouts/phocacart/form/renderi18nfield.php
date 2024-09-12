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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Phoca\PhocaCart\Form\FormHelper;
use Phoca\PhocaCart\I18n\I18nHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   array         $options      Optional parameters
 * @var   string        $id           The id of the input this label is for
 * @var   string        $name         The name of the input this label is for
 * @var   string        $label        The html code for the label
 * @var   string|array  $input        The input field html code
 * @var   string        $description  An optional description to use as in–line help text
 * @var   string        $descClass    The class name to use for the description
 * @var   FormField     $field        The Field object
 * @var   boolean       $i18n         I18n support?
 * @var   array         $languages    Languages list for i18n.
 * @var   string        $defLanguage  Default language for i18n.
 */

if (!empty($options['showonEnabled'])) {
    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wa->useScript('showon');

    $options['rel'] = ' data-showon=\'' .
            json_encode(FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) .
            '\''
        ;
}

$class = ['ph-par'];
if ($field->group) {
  $class = explode('.', $field->group);
} else {
  $class = [];
}
$class[] = $field->fieldname;
$class = ' ph-par-' . implode('-', $class);

$class          .= empty($options['class']) ? '' : ' ' . $options['class'];
$rel             = empty($options['rel']) ? '' : ' ' . $options['rel'];
$id              = ($id ?? $name) . '-desc';
$hideLabel       = !empty($options['hiddenLabel']);
$hideDescription = empty($options['hiddenDescription']) ? false : $options['hiddenDescription'];
$descClass       = ($options['descClass'] ?? '') ?: (!empty($options['inlineHelp']) ? 'hide-aware-inline-help d-none' : '');

if (!empty($parentclass)) {
    $class .= ' ' . $parentclass;
}

?>
<div class="control-group<?php echo $class; ?>"<?php echo $rel; ?>>
    <?php if ($hideLabel) : ?>
        <div class="visually-hidden"><?php echo $label; ?></div>
    <?php else : ?>
        <div class="control-label"><?php echo $label; ?></div>
    <?php endif; ?>
    <div class="controls">
        <?php if (is_array($input)) : ?>
          <?php
            echo HTMLHelper::_('uitab.startTabSet', $id . '_i18nTabs', ['recall' => true, 'breakpoint' => 768]);
            foreach ($input as $lang => $singleInput) {
                $language = $languages[$lang];

                $tabTitle = HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', '', ['class' => 'me-1'], true)
                    . $language->title
                    . ' ' . I18nHelper::getEditorIcon($language->lang_code, $value);
                $tabTitle = '<span title="' . I18nHelper::getEditorIconTitle($language->lang_code, $value) . '">' . $tabTitle . '</span>';

                echo HTMLHelper::_('uitab.addTab', $id . '_i18nTabs', $language->lang_code, $tabTitle);

                echo $singleInput;

                echo HTMLHelper::_('uitab.endTab');
            }
            echo HTMLHelper::_('uitab.endTabSet');
          ?>
        <?php else : ?>
            <?php echo $input; ?>
        <?php endif; ?>
        <?php if (!$hideDescription && !empty($description)) : ?>
            <div id="<?php echo $id; ?>" class="<?php echo $descClass ?>">
                <small class="form-text">
                    <?php echo $description; ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
</div>
