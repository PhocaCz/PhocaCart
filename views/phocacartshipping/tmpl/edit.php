<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$class		= $this->t['n'] . 'RenderAdminview';
$r 			=  new $class();
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == '<?php echo $this->t['task'] ?>.cancel' || task == 'phocacartwizard.backtowizard' || document.formvalidator.isValid(document.id('adminForm'))) {
		<?php echo $this->form->getField('description')->save(); ?>
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
	else {
		Joomla.renderMessages({"error": ["<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true);?>"]});
	}
}
</script><?php
echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="span10 form-horizontal">';
$tabs = array (
'general' 		=> JText::_($this->t['l'].'_GENERAL_OPTIONS'),
'amount' 		=> JText::_($this->t['l'].'_AMOUNT_RULE'),
'zone' 			=> JText::_($this->t['l'].'_ZONE_RULE'),
'country' 		=> JText::_($this->t['l'].'_COUNTRY_RULE'),
'region' 		=> JText::_($this->t['l'].'_REGION_RULE'),
'weight' 		=> JText::_($this->t['l'].'_WEIGHT_RULE'),
'method' 		=> JText::_($this->t['l'].'_SHIPPING_METHOD_OPTIONS'),
'publishing' 	=> JText::_($this->t['l'].'_PUBLISHING_OPTIONS'));
echo $r->navigation($tabs);

echo '<div class="tab-content">'. "\n";

echo '<div class="tab-pane active" id="general">'."\n"; 
$formArray = array ('title', 'cost', 'tax_id', 'calculation_type');
echo $r->group($this->form, $formArray);

$formArray = array ('method');
echo $r->group($this->form, $formArray);
echo '<div id="ph-extended-params-msg" class="ph-extended-params-msg"></div>';

$formArray = array ('image', 'ordering', 'access');
echo $r->group($this->form, $formArray);

$formArray = array('description');
echo $r->group($this->form, $formArray, 1);
echo '</div>';

echo '<div class="tab-pane" id="amount">'."\n"; 
$formArray = array ('lowest_amount', 'highest_amount', 'active_amount');
echo $r->group($this->form, $formArray);
echo '</div>';


echo '<div class="tab-pane" id="zone">'."\n"; 
$formArray = array ('zone', 'active_zone');
echo $r->group($this->form, $formArray);
echo '</div>';

echo '<div class="tab-pane" id="country">'."\n"; 
$formArray = array ('country', 'active_country');
echo $r->group($this->form, $formArray);
echo '</div>';

echo '<div class="tab-pane" id="region">'."\n"; 
$formArray = array ('region', 'active_region');
echo $r->group($this->form, $formArray);
echo '</div>';

echo '<div class="tab-pane" id="weight">'."\n"; 
$formArray = array ('lowest_weight', 'highest_weight', 'active_weight');
echo $r->group($this->form, $formArray);
echo '</div>';

echo '<div class="tab-pane" id="method">'."\n"; 
echo '<div id="ph-extended-params" class="ph-extended-params">'.JText::_('COM_PHOCACART_SELECT_SHIPPING_METHOD_TO_DISPLAY_PARAMETERS').'</div>';
echo '</div>';

echo '<div class="tab-pane" id="publishing">'."\n"; 
foreach($this->form->getFieldset('publish') as $field) {
	echo '<div class="control-group">';
	if (!$field->hidden) {
		echo '<div class="control-label">'.$field->label.'</div>';
	}
	echo '<div class="controls">';
	echo $field->input;
	echo '</div></div>';
}
echo '</div>';
				
echo '</div>';//end tab content
echo '</div>';//end span10
// Second Column
echo '<div class="col-xs-12 col-sm-2 col-md-2"></div>';//end span2
echo $r->formInputs($this->t['task']);
echo $r->endForm();
echo PhocacartRenderJs::renderAjaxTopHtml();
?>

