<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

$fieldSets = $this->form->getFieldsets('aidata');

foreach ($fieldSets as $name => $fieldSet) :
	?>
	<fieldset class="panelform">
	    <div class="adminform">
		<?php foreach ($this->form->getFieldset($name) as $field) :

            $description = Text::_($field->description);
            $descriptionOutput = '';
            if ($description != '') {
                $descriptionOutput = '<div role="tooltip">'.$description.'</div>';
            }

            ?>
			<div class="control-group">
			<div class="control-label"><?php echo $field->label . $descriptionOutput; ?></div>
			<div class="controls"><?php echo $field->input; ?></div></div>
		<?php endforeach; ?>
		</div>
	</fieldset>
<?php endforeach;

echo '<div class="ph-ai-question-box" id="phAiQuestionBox">';

$fields = [
    'description' => 'COM_PHOCACART_FIELD_DESCRIPTION_LABEL',
    'description_long' => 'COM_PHOCACART_FIELD_DESCRIPTION_LONG_LABEL',
    'features' => 'COM_PHOCACART_FIELD_FEATURES_LABEL',
    'metadesc' => 'JFIELD_META_DESCRIPTION_LABEL',
];

foreach ($fields as $k => $v) {

    echo '<h3>'.Text::_($v).'</h3>';

    echo '<div class="ph-ai-question-item">';
    echo '<input name="ai_question_'.$k.'" id="ai_question_'.$k.'" class="form-control ph-ai-question-item-field" type="text" value="" />';
    echo '<input name="ai_button_generate_'.$k.'" id="ai_button_generate_'.$k.'" data-typeid="'.$k.'" type="button" class="btn btn-primary phBtnGenerate" value="'.Text::_('COM_PHOCACART_GENERATE_CONTENT').'" />';
    echo '</div>';

    echo '<div class="ph-ai-content-item">';
    echo '<textarea name="ai_content_'.$k.'" id="ai_content_'.$k.'" class="form-control"></textarea>';
    echo '</div>';

    echo '<div class="ph-ai-paste-item">';
    echo '<div class="ph-ai-paste-item-text">'.Text::_('COM_PHOCACART_PASTE_THE_CONTENT_INTO_FOLLOWING_FIELD').': '.Text::_($v).'</div>';
    echo '<input name="ai_button_paste_'.$k.'" id="ai_button_paste_'.$k.'" data-typeid="'.$k.'" type="button" class="btn btn-primary phBtnPaste" value="'.Text::_('COM_PHOCACART_PASTE').'" />';
    echo '</div>';

    echo '<div id="ai_message_'.$k.'" class="ph-ai-message-box"></div>';
    echo '<hr>';

}

echo '</div>';
