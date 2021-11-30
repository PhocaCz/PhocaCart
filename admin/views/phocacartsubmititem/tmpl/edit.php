<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


$r 			=  $this->r;
$js ='
Joomla.submitbutton = function(task) {
	if (task == "'. $this->t['task'] .'.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
		Joomla.submitform(task, document.getElementById("adminForm"));
	} else {
		Joomla.renderMessages({"error": ["'. Text::_('JGLOBAL_VALIDATION_FORM_FAILED', true).'"]});
	}
}
';
JFactory::getDocument()->addScriptDeclaration($js);
echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="col-xs-12 col-sm-12 col-md-12 form-horizontal">';
$tabs = array(
    'general' => Text::_($this->t['l'] . '_GENERAL_OPTIONS'),
    'item' => Text::_($this->t['l'] . '_PRODUCT_INFORMATION'),
    'contact' => Text::_($this->t['l'] . '_CONTACT_INFORMATION'));
echo $r->navigation($tabs);

$formArray = array ('title', 'alias');
echo $r->groupHeader($this->form, $formArray);

echo $r->startTabs();


// GENERAL OPTIONS
echo $r->startTab('general', $tabs['general'], 'active');
$formArray = array('user_id', 'ip', 'date_submit', 'published', 'ordering', 'upload_folder', 'upload_token');
echo $r->group($this->form, $formArray);
echo $r->endTab();


// PRODUCT INFORMATION
echo $r->startTab('item', $tabs['item']);


// Items
if (!empty($this->t['items_item'])) {

    $fieldSets = $this->form->getFieldsets();

    foreach ($fieldSets as $name => $fieldSet) {

        if (isset($fieldSet->name) && $fieldSet->name == 'items_item') {

            foreach ($this->form->getFieldset($name) as $field) {

                $isIncluded = 0;
                if (in_array($field->fieldname, $this->t['items_item'])) {
                    $isIncluded = 1;// included
                }
                if (in_array($field->fieldname . '*', $this->t['items_item'])) {
                    $isIncluded = 2;// included and required
                }

                if ($isIncluded > 0) {

                    if ($isIncluded == 2) {
                        $field->__set('required', true);//$field->required = true;//$field->addAttribute('required', 'true');
                    }

                    echo '<div class="' . $this->s['c']['control-group'] . '">';
                    echo '<div class="' . $this->s['c']['control-label'] . '">' . $field->label . '</div>';
                    echo '<div class="' . $this->s['c']['controls'] . '">' . $field->input . '</div>';
                    echo '</div>';

                } else {

                    // Because of validation, add empty values of not used form fiels
                    $field->__set('required', false);
                    $field->__set('hidden', true);
                    $field->__set('class', 'hidden');
                    echo '<div style="display:none">' . $field->input . '</div>';

                }
            }
        }
    }
}

// Items - Parameter
if (!empty($this->t['items_parameter'])) {

    $fieldSets = $this->form->getFieldsets();

    foreach ($fieldSets as $name => $fieldSet) {

        if (isset($fieldSet->name) && $fieldSet->name == 'items_parameter') {

            $parameters = PhocacartParameter::getAllParameters();


            foreach ($this->form->getFieldset($name) as $field) {

                // We store parameters with ID not aliases in DB
                $alias = '';
                $fN = (int)$field->fieldname;
                if (isset($parameters[$fN]->alias) && $parameters[$fN]->alias != '') {
                    $alias = $parameters[$fN]->alias;
                }

                $isIncluded = 0;
                if ($alias != '' && in_array($alias, $this->t['items_parameter'])) {
                    $isIncluded = 1;// included
                }
                if ($alias != '' && in_array($alias . '*', $this->t['items_parameter'])) {
                    $isIncluded = 2;// included and required
                }


                if ($isIncluded > 0) {

                    if ($isIncluded == 2) {
                        $field->__set('required', true);
                    }

                    echo '<div class="' . $this->s['c']['control-group'] . '">';
                    echo '<div class="' . $this->s['c']['control-label'] . '">' . $field->label . '</div>';
                    echo '<div class="' . $this->s['c']['controls'] . '">' . $field->input . '</div>';
                    echo '</div>';

                } else {

                    $field->__set('required', false);
                    $field->__set('hidden', true);
                    $field->__set('class', 'hidden');
                    echo '<div style="display:none">' . $field->input . '</div>';

                }
            }
        }
    }
}
echo $r->endTab();

// CONTACT INFORMATION
echo $r->startTab('contact', $tabs['contact']);

if (!empty($this->t['items_contact'])) {

    $fieldSets = $this->form->getFieldsets();

    foreach ($fieldSets as $name => $fieldSet) {

        if (isset($fieldSet->name) && $fieldSet->name == 'items_contact') {

            foreach ($this->form->getFieldset($name) as $field) {

                $isIncluded = 0;
                if (in_array($field->fieldname, $this->t['items_contact'])) {
                    $isIncluded = 1;// included
                }

                if (in_array($field->fieldname . '*', $this->t['items_contact'])) {
                    $isIncluded = 2;// included and required
                }


                if ($isIncluded > 0) {

                    if ($isIncluded == 2) {
                        $field->__set('required', true);
                    }

                    echo '<div class="' . $this->s['c']['control-group'] . '">';
                    echo '<div class="' . $this->s['c']['control-label'] . '">' . $field->label . '</div>';
                    echo '<div class="' . $this->s['c']['controls'] . '">' . $field->input . '</div>';
                    echo '</div>';

                } else {

                    $field->__set('required', false);
                    $field->__set('hidden', true);
                    $field->__set('class', 'hidden');
                    echo '<div style="display:none">' . $field->input . '</div>';

                }
            }
        }
    }
}
echo $r->endTab();


echo $r->endTabs();
echo '</div>';//end span10
// Second Column
echo '<div class="col-xs-12 col-sm-2 col-md-2">';
echo '</div>';//end span2
echo $r->formInputs();
echo $r->endForm();
?>
