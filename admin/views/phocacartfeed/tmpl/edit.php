<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


$r 			=  $this->r;
$js ='
Joomla.submitbutton = function(task) {
	if (task == "'. $this->t['task'] .'.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
		Joomla.submitform(task, document.getElementById("adminForm"));
	} else {
		Joomla.renderMessages({"error": ["'. JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true).'"]});
	}
}
';
JFactory::getDocument()->addScriptDeclaration($js);

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="col-xs-12 col-sm-10 col-md-10 form-horizontal">';
$tabs = array (
'general' 		=> JText::_($this->t['l'].'_GENERAL_OPTIONS'),
'product' 		=> JText::_($this->t['l'].'_PRODUCT_OPTIONS'),
'feed' 		=> JText::_($this->t['l'].'_FEED_OPTIONS'),
'publishing' 	=> JText::_($this->t['l'].'_PUBLISHING_OPTIONS'));
echo $r->navigation($tabs);

echo $r->startTabs();

echo $r->startTab('general', $tabs['general'], 'active');
$formArray = array ('title', 'alias', 'header', 'footer', 'root', 'item', 'feed_plugin');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('product', $tabs['product']);
$fieldSets = $this->form->getFieldsets('item_params');
foreach ($fieldSets as $name => $fieldSet) {
	foreach ($this->form->getFieldset($name) as $field) {
		echo $r->itemLabel($field->input, $field->label);
	}
}
echo $r->endTab();


echo $r->startTab('feed', $tabs['feed']);
$fieldSets = $this->form->getFieldsets('feed_params');
foreach ($fieldSets as $name => $fieldSet) {
	foreach ($this->form->getFieldset($name) as $field) {
		echo $r->itemLabel($field->input, $field->label);
	}
}
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
echo '<div class="col-xs-12 col-sm-2 col-md-2">';

if (isset($this->item->id) && (int)$this->item->id > 0 && isset($this->item->alias) && $this->item->alias != '') {
	/* phocacart import('phocacart.path.route'); */
	$xmlLink 		= PhocacartRoute::getFeedRoute((int)$this->item->id, $this->item->alias);
	$xmlLink2 		= PhocacartRoute::getFeedRoute((int)$this->item->id, $this->item->alias, 1);
	$app    		= JApplicationCms::getInstance('site');
	$router 		= $app->getRouter();
	$uri 			= $router->build($xmlLink);


	$frontendUrl 	= str_replace(JURI::root(true).'/administrator/', '',$uri->toString());
    $frontendUrl 	= str_replace(JURI::root(true), '', $frontendUrl);
    $frontendUrl 	= str_replace('\\', '/', $frontendUrl);
    //$frontendUrl 	= JURI::root(false). str_replace('//', '/', $frontendUrl);
    $frontendUrl 	= preg_replace('/([^:])(\/{2,})/', '$1/', JURI::root(false). $frontendUrl);
    $frontendUrl2 	= JURI::root(false). str_replace(JURI::root(true).'/administrator/', '',$xmlLink2);



	echo '<div>'.JText::_('COM_PHOCACART_XML_FEED_URL').'</div>';
	echo '<textarea rows="5">'.$frontendUrl.'</textarea>';
	echo '<div><small>('.JText::_('COM_PHOCACART_URL_FORMAT_DEPENDS_ON_SEF').')</small></div>';

	echo '<div>&nbsp;</div>';
	echo '<div>'.JText::_('COM_PHOCACART_XML_FEED_URL_NO_SEF').'</div>';
	echo '<textarea rows="5">'.$frontendUrl2.'</textarea>';
}

echo '</div>';//end span2
echo $r->formInputs();
echo $r->endForm();
?>

