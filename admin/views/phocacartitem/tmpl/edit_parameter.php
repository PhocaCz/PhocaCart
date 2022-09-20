<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$fieldSets = $this->form->getFieldsets();

$o = '';


foreach($fieldSets as $name => $fieldSet) {

    if (isset($fieldSet->name) && $fieldSet->name == 'items_parameter') {

        foreach ($this->form->getFieldset($name) as $field) {

            $o .= '<div class="control-group ph-par-'.$field->fieldname.'">';
            $o .= '<div class="control-label">' . $field->label . '</div>';
            $o .= '<div class="controls">' . $field->input . '</div>';
            $o .= '</div>';
        }
    }
}

echo $o;



