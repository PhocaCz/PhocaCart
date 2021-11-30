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
	if (task == "'. $this->t['task'] .'.cancel" || task == "phocacartwizard.backtowizard" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
		Joomla.submitform(task, document.getElementById("adminForm"));
	} else {
		Joomla.renderMessages({"error": ["'. Text::_('JGLOBAL_VALIDATION_FORM_FAILED', true).'"]});
	}
}
';
JFactory::getDocument()->addScriptDeclaration($js);
echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="span12 form-horizontal">';
$tabs = array (
'general' 		=> Text::_($this->t['l'].'_GENERAL_OPTIONS'),
'amount' 		=> Text::_($this->t['l'].'_AMOUNT_RULE'),
'quantity' 		=> Text::_($this->t['l'].'_QUANTITY_RULE'),
'zone' 			=> Text::_($this->t['l'].'_ZONE_RULE'),
'country' 		=> Text::_($this->t['l'].'_COUNTRY_RULE'),
'region' 		=> Text::_($this->t['l'].'_REGION_RULE'),
'zip' 			=> Text::_($this->t['l'].'_ZIP_RULE'),
'weight' 		=> Text::_($this->t['l'].'_WEIGHT_RULE'),
'size' 			=> Text::_($this->t['l'].'_SIZE_RULE'),
'method' 		=> Text::_($this->t['l'].'_SHIPPING_METHOD_OPTIONS'),
'tracking' 		=> Text::_($this->t['l'].'_SHIPMENT_TRACKING_OPTIONS'),
'publishing' 	=> Text::_($this->t['l'].'_PUBLISHING_OPTIONS'));
echo $r->navigation($tabs);

$formArray = array ('title');
echo $r->groupHeader($this->form, $formArray);

echo $r->startTabs();

echo $r->startTab('general', $tabs['general'], 'active');
$formArray = array ('cost', 'cost_additional', 'tax_id', 'calculation_type', 'default', 'type');
echo $r->group($this->form, $formArray);

$formArray = array ('method');
echo $r->group($this->form, $formArray);
echo '<div id="ph-extended-params-msg" class="ph-extended-params-msg"></div>';

$formArray = array ('image', 'ordering', 'access', 'group');
echo $r->group($this->form, $formArray);

$formArray = array('description');
echo $r->group($this->form, $formArray, 1);
$formArray = array('description_info');
echo $r->group($this->form, $formArray, 1);
echo $r->endTab();


echo $r->startTab('quantity', $tabs['quantity']);
$formArray = array ('minimal_quantity', 'maximal_quantity', 'active_quantity');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('amount', $tabs['amount']);
$formArray = array ('lowest_amount', 'highest_amount', 'active_amount');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('zone', $tabs['zone']);
$formArray = array ('zone', 'active_zone');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('country', $tabs['country']);
$formArray = array ('country', 'active_country');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('region', $tabs['region']);
$formArray = array ('region', 'active_region');
echo $r->group($this->form, $formArray);
echo $r->endTab();

echo $r->startTab('zip', $tabs['zip']);
$formArray = array ('zip', 'active_zip');
echo $r->group($this->form, $formArray);
echo $r->endTab();

echo $r->startTab('weight', $tabs['weight']);
$formArray = array ('lowest_weight', 'highest_weight', 'active_weight');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('size', $tabs['size']);
//$formArray = array ('shortest_length', 'longest_length', 'lowest_width', 'largest_width', 'lowest_height', 'highest_height', 'active_size');
$formArray = array ('minimal_length', 'maximal_length', 'minimal_width', 'maximal_width', 'minimal_height', 'maximal_height', 'active_size');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('method', $tabs['method']);
echo '<div id="ph-extended-params" class="ph-extended-params">'.Text::_('COM_PHOCACART_SELECT_SHIPPING_METHOD_TO_DISPLAY_PARAMETERS').'</div>';
echo $r->endTab();


echo $r->startTab('tracking', $tabs['tracking']);
$formArray = array ('tracking_link', 'tracking_description');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('publishing', $tabs['publishing']);
foreach($this->form->getFieldset('publish') as $field) {
	echo '<div class="control-group">';
	if (!$field->hidden) {
		echo '<div class="control-label">'.$field->label.'</div>';
	}
	echo '<div class="controls">';
	echo $field->input;
	echo '</div></div>';
}
echo $r->endTab();

echo $r->endTabs();
echo '</div>';//end span10
// Second Column
////echo '<div class="col-xs-12 col-sm-2 col-md-2"></div>';//end span2
echo $r->formInputs($this->t['task']);
echo $r->endForm();

?>

