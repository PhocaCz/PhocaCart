<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
//JHtml::_('formbehavior.chosen', 'select');
$layoutPC = new FileLayout('form_privacy_checkbox', null, array('component' => 'com_phocacart'));
$layoutUL = new FileLayout('user_login', null, array('component' => 'com_phocacart'));
$layoutUR = new FileLayout('user_register', null, array('component' => 'com_phocacart'));


echo '<div id="ph-pc-submit-item-box" class="pc-view pc-submit-item-view' . $this->p->get('pageclass_sfx') . '">';
echo PhocacartRenderFront::renderHeader(array(Text::_('COM_PHOCACART_SUBMIT_ITEM')));


if (isset($this->t['submit_item_description']) && $this->t['submit_item_description'] != '') {
    echo '<div class="ph-desc">' . $this->t['submit_item_description'] . '</div>';
}

if (PhocacartSubmit::isAllowedToSubmit()) {

    $hiddenfield = '<div class="' . $this->s['c']['control-group'] . ' ' . $this->p->get('hidden_field_class') . '">' .
        '<div class="' . $this->s['c']['controls'] . ' input-prepend input-group">' .
        '' . $this->form->getInput($this->p->get('hidden_field_name')) .
        '</div>' .
        '</div>';


    echo '<div>&nbsp;</div>';
    echo '<div class="' . $this->s['c']['row'] . '">';
    echo '<div class="' . $this->s['c']['col.xs12.sm12.md12'] . '">';

    echo '<form action="' . $this->t['action'] . '" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">';


    // All form items


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

                        //$field->addAttribute('required', 'true');
                        //$field->required = true;
                        $field->__set('required', true);
                    }

                    echo '<div class="' . $this->s['c']['control-group'] . '">';

                    echo '<div class="' . $this->s['c']['control-label'] . '">' . $field->label . '</div>';
                    echo '<div class="' . $this->s['c']['controls'] . '">' . $field->input . '</div>';

                    echo '</div>';

                }
            }
        }
    }
    /*
    if (!empty($this->t['items_item'])) {
        foreach ($this->t['items_item'] as $k => $v) {

            $field = trim($v);
            // Required
            if (strpos($field, '*') !== false) {

                $field = str_replace('*', '', $field);
                $this->form->setFieldAttribute($field, 'required', 'true');
            }
            $field = str_replace('*', '', $field);

            $fieldInput = $this->form->getInput($field);

            $fieldInput = str_replace('icon-calendar', $this->s['i']['calendar'], $fieldInput);

            echo '<div class="'.$this->s['c']['control-group'].'">';

            echo '<div class="'.$this->s['c']['control-label'].'">'.$this->form->getLabel($field).'</div>';
            echo '<div class="'.$this->s['c']['controls'].'">'.$fieldInput.'</div>';

            echo '</div>';

        }
    }*/
    if ($this->p->get('hidden_field_position') == 1) {
        echo $hiddenfield;
    }


    // Parameters
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

                        //$field->addAttribute('required', 'true');
                        //$field->required = true;
                        $field->__set('required', true);
                    }

                    echo '<div class="' . $this->s['c']['control-group'] . '">';

                    echo '<div class="' . $this->s['c']['control-label'] . '">' . $field->label . '</div>';
                    echo '<div class="' . $this->s['c']['controls'] . '">' . $field->input . '</div>';

                    echo '</div>';

                }
            }
        }
    }

    if ($this->p->get('hidden_field_position') == 2) {
        echo $hiddenfield;
    }

    // Contact information
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

                        //$field->addAttribute('required', 'true');
                        //$field->required = true;
                        $field->__set('required', true);
                    }

                    echo '<div class="' . $this->s['c']['control-group'] . '">';

                    echo '<div class="' . $this->s['c']['control-label'] . '">' . $field->label . '</div>';
                    echo '<div class="' . $this->s['c']['controls'] . '">' . $field->input . '</div>';

                    echo '</div>';

                }
            }
        }
    }
    /*
    if (!empty($this->t['items_contact'])) {

        echo '<div class="ph-submititem-header-contact">'.Text::_('COM_PHOCACART_CONTACT_INFORMATION').'</div>';
        foreach ($this->t['items_contact'] as $k => $v) {

            $field = trim($v);
            // Required
            if (strpos($field, '*') !== false) {

                $field = str_replace('*', '', $field);
                $this->form->setFieldAttribute($field, 'required', 'true');
            }
            $field = str_replace('*', '', $field);

            $fieldInput = $this->form->getInput($field);
            $fieldInput = str_replace('icon-calendar', $this->s['i']['calendar'], $fieldInput);

            echo '<div class="'.$this->s['c']['control-group'].'">';

            echo '<div class="'.$this->s['c']['control-label'].'">'.$this->form->getLabel($field).'</div>';
            echo '<div class="'.$this->s['c']['controls'].'">'.$fieldInput.'</div>';

            echo '</div>';

        }
    }*/
    if ($this->p->get('hidden_field_position') == 3) {
        echo $hiddenfield;
    }

    // Captcha
    echo '<div class="' . $this->s['c']['control-group'] . '">';
    echo '<div class="' . $this->s['c']['control-label'] . '">' . $this->form->getLabel('phq_captcha') . '</div>';
    echo '<div class="' . $this->s['c']['controls'] . '">' . $this->form->getInput('phq_captcha') . '</div>';
    echo '</div>';
    if ($this->p->get('hidden_field_position') == 4) {
        echo $hiddenfield;
    }

    // Privacy Checkbox
    if ($this->t['display_submit_item_privacy_checkbox'] > 0) {
        $d = array();
        $d['s'] = $this->s;
        $d['label_text'] = $this->t['submit_item_privacy_checkbox_label_text'];
        $d['id'] = 'phSubmitItemPrivacyCheckbox';
        $d['name'] = 'privacy';
        $d['class'] = $this->s['c']['pull-left'] . ' ' . $this->s['c']['inputbox.checkbox'] . ' ph-submititem-checkbox-confirm';
        $d['display'] = $this->t['display_submit_item_privacy_checkbox'];

        echo '<div class="ph-cb"></div>';
        echo $layoutPC->render($d);
        echo '<div class="ph-cb"></div>';
    }
    if ($this->p->get('hidden_field_position') == 5) {
        echo $hiddenfield;
    }

    // Submit button
    echo '<div class="btn-toolbar">';
    echo '<div class="btn-group">';
    echo '<button type="submit" class="' . $this->s['c']['btn.btn-primary'] . '">';
    echo '<span class="' . $this->s['i']['submit'] . '"></span> ' . Text::_('COM_PHOCACART_SUBMIT') . '</button>';
    echo '</div>';
    echo '</div>';


    echo '<input type="hidden" name="view" value="submit" />';
    //echo '<input type="hidden" name="cid" value="cid" />';
    //echo '<input type="hidden" name="id" value="id" />';
    echo '<input type="hidden" name="option" value="com_phocacart" />';
    echo '<input type="hidden" name="task" value="submit.submit" />';

    echo HTMLHelper::_('form.token');
    echo '</form>';

    echo '</div>';
    echo '</div>';

} else {

    require_once JPATH_SITE . '/components/com_users/helpers/route.php';
    jimport('joomla.application.module.helper');
    $module = ModuleHelper::getModule('mod_login');
    $mP = new Registry();
    $mP->loadString($module->params);

    $lang = Factory::getLanguage();
    $lang->load('mod_login');

    echo '<div class="' . $this->s['c']['row'] . ' ph-account-box-row" >';
    //echo '<div class="ph-account-box-header" id="phaccountloginedit"><div class="ph-pull-right"><span class="'.$this->s['i']['remove-circle'].' ph-account-icon-not-ok"></span></div><h3>1. '.JText::_('COM_PHOCACART_LOGIN_REGISTER').'</h3></div>';
    echo '<div class="' . $this->s['c']['col.xs12.sm12.md12'] . ' ph-account-box-header" id="phaccountloginedit"><h3>' . Text::_('COM_PHOCACART_LOGIN_REGISTER') . '</h3></div>';
    echo '</div>';


    echo '<div class="' . $this->s['c']['row'] . ' ph-account-box-action">';


    echo '<div class="' . $this->s['c']['col.xs12.sm8.md8'] . ' ph-right-border">';

    $d = array();
    $d['s'] = $this->s;
    $d['t'] = $this->t;
    echo $layoutUL->render($d);

    echo '</div>' . "\n";// end columns

    echo '<div class="' . $this->s['c']['col.xs12.sm4.md4'] . ' ph-left-border">';

    $d = array();
    $d['s'] = $this->s;
    $d['t'] = $this->t;
    echo $layoutUR->render($d);

    echo '</div>' . "\n";// end columns

    echo '<div class="ph-cb"></div>';

    echo '</div>' . "\n";// end account box login

    echo '</form>' . "\n";


}
echo '</div>';
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>


