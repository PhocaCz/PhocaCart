<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!class_exists('PhocacartParameter')) {
    require_once(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart/parameter/parameter.php');
}

$lang = JFactory::getLanguage();
$lang->load('com_phocacart');

class JFormFieldPhocacartParameter extends JFormField
{
    protected $type = 'PhocacartParameter';

    protected function getInput() {

        //$activeId = (int) $this->form->getValue('id');

        $attr = '';
        $attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
        $attr .= $this->element['maxlength'] ? ' maxlength="' . (int)$this->element['maxlength'] . '"' : '';
        $attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
        $attr .= ((string)$this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $attr .= ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '" ' : ' ';

        $selectText = $this->element['typemethod'] && $this->element['typemethod'] == 1 ? 1 : 0;



        return PhocacartParameter::getAllParametersSelectBox($this->name, $this->id, $this->value /*$activeId*/, $attr, 'id', $selectText);
    }
}

?>
