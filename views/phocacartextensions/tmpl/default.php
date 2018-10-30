<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$linkIcon	= '';//'<sup><span class="glyph icon glyph icon-link"></span></sup>';

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
//$class		= $this->t['n'] . 'RenderAdminviews';
$r 			=  new PhocacartRenderAdminviews();
$user		= JFactory::getUser();
$userId		= $user->get('id');


if ($this->t['load_extension_list'] == 0) {
	
	echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
	
	echo $r->startFilter();
	echo $r->endFilter();
	echo $r->startMainContainer();
	
	echo '<div class="alert alert-warning">';
	echo '<button type="button" class="close" data-dismiss="alert">×</button>';
	//echo '<h4 class="alert-heading">'.JText::_('COM_PHOCACART_INFO').'</h4>';
	echo '<div class="alert-message">'.JText::_('COM_PHOCACART_LOADING_OF_EXTENSION_LIST_DISABLED').'</div>';
	echo '</div>';
	
	echo '<div class="alert alert-info">';
	echo '<button type="button" class="close" data-dismiss="alert">×</button>';
	//echo '<h4 class="alert-heading">'.JText::_('COM_PHOCACART_INFO').'</h4>';
	echo '<div class="alert-message">'.JText::_('COM_PHOCACART_DISCOVER').' <a href="https://www.phoca.cz/phocacart-extensions" target="_blank" style="text-decoration: underline">'.JText::_('COM_PHOCACART_PHOCA_CART_EXTENSIONS').'</a> '.$linkIcon .'</div>';
	echo '</div>';
	
	
	echo $r->startFilterBar();
	echo $r->startFilterBar(2);
	echo $r->endFilterBar();
	echo $r->endFilterBar();
	
	echo $r->endMainContainer();
	echo $r->endForm();
	
} else {
	

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);

$saveOrder 	= false;
/*if ($this->t['ordering'] && !empty($this->ordering)) {
	$saveOrder	= $listOrder == 'pc.ordering';
	if ($saveOrder) {
		$saveOrderingUrl = 'index.php?option='.$this->t['o'].'&task='.$this->t['tasks'].'.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
	}
}*/
$sortFields = $this->getSortFields();
echo $r->jsJorderTable($listOrder);



echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
echo $r->startFilter();
//echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.state'));
//echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
//echo $r->selectFilterCategory(PhocacartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
echo $r->endFilter();

echo $r->startMainContainer();

echo '<div class="alert alert-info">';
echo '<button type="button" class="close" data-dismiss="alert">×</button>';
//echo '<h4 class="alert-heading">'.JText::_('COM_PHOCACART_INFO').'</h4>';
echo '<div class="alert-message">'.JText::_('COM_PHOCACART_DISCOVER').' <a href="https://www.phoca.cz/phocacart-extensions" target="_blank" style="text-decoration: underline">'.JText::_('COM_PHOCACART_PHOCA_CART_EXTENSIONS').'</a> '.$linkIcon .'</div>';
echo '</div>';


if (is_array($this->news)) {
	foreach ($this->news as $n => $m) {
		if (isset($m['name']) && $m['name'] != '' && isset($m['description']) && $m['description'] != '') {
			
			$mClass 	= isset($m['class']) ? $this->escape(strip_tags($m['class'])) : '';
			$mStyle 	= isset($m['style']) ? $this->escape(strip_tags($m['style'])) : '';
			$mImage 	= isset($m['image']) ? $this->escape(strip_tags($m['image'])) : '';
			$mImageLarge= isset($m['imagelarge']) ? $this->escape(strip_tags($m['imagelarge'])) : '';
			$mLink		= isset($m['link']) ? $this->escape(strip_tags($m['link'])) : '';
			$aStart		= '';
			$aEnd		= '';
			
			if ($mLink != '') {
				$aStart = '<a href="'.$mLink.'" target="_blank">';
				$aEnd 	= '</a>';
			}
			
			echo '<div class="ph-featured-box '.$mClass.'" style="'.$mStyle.'">';
			echo '<div class="ph-featured-head">'.$aStart.$this->escape(strip_tags($m['name'])).$aEnd.'</div>';
			
			echo '<div class="ph-featured-image-large">'.$aStart.'<img src="'.$mImageLarge.'" alt="" />'.$aEnd.'</div>';
			
			echo '<div class="ph-featured-description">';
			if ($mImage != '') {
				echo '<div class="ph-featured-image">'.$aStart.'<img src="'.$mImage.'" alt="" />'.$aEnd.'</div>';
			}
			echo $aStart.$this->escape(strip_tags($m['description'])).$aEnd;
			
			echo '<div class="ph-cb"></div>';
			echo '</div>';
			
			echo '</div>';
			
			
		}
	}
}
echo $r->startFilterBar();

echo $r->startFilterBar(2);
echo $r->selectFilterCategory(PhocacartUtilsSettings::getExtenstionsArray($this->t['o']), '', $this->state->get('filter.category_id'));
echo $r->endFilterBar();

echo $r->endFilterBar();
	

echo $r->startTable('extensionList');

echo $r->startTblHeader();


echo '<th class="ph-image">'.JText::_($this->t['l'].'_IMAGE').'</th>'."\n";
echo '<th class="ph-title-small">'.JText::_($this->t['l'].'_NAME').'</th>'."\n";
echo '<th class="ph-description">'.JTEXT::_($this->t['l'].'_DESCRIPTION').'</th>'."\n";
echo '<th class="ph-version">'.JText::_($this->t['l'].'_VERSION').'</th>'."\n";	
echo '<th class="ph-developer">'.JTEXT::_($this->t['l'].'_DEVELOPER').'</th>'."\n";
echo '<th class="ph-type">'.JTEXT::_($this->t['l'].'_TYPE').'</th>'."\n";
echo '<th class="ph-action">'.JTEXT::_($this->t['l'].'_ACTION').'</th>'."\n";

echo $r->endTblHeader();
		
echo '<tbody>'. "\n";

$originalOrders = array();	
$parentsStr 	= "";		
$j 				= 0;

$price			= new PhocacartPrice();

if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {
		
			$j++;
/*
$urlEdit		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=';
$urlTask		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'];
//$orderkey   	= array_search($item->id, $this->ordering[$item->catid]);
$orderkey		= 0;
if ($this->t['ordering'] && !empty($this->ordering)) {
	$orderkey   	= array_search($item->id, $this->ordering[$this->t['catid']]);
}	
$ordering		= ($listOrder == 'pc.ordering');			
$canCreate		= $user->authorise('core.create', $this->t['o']);
$canEdit		= $user->authorise('core.edit', $this->t['o']);
$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
$linkEdit 		= JRoute::_( $urlEdit. $item->id );

//$linkCat	= JRoute::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
$canEditCat	= $user->authorise('core.edit', $this->t['o']);*/
			
$canCreate		= $user->authorise('core.create', $this->t['o']);
$canEdit		= $user->authorise('core.edit', $this->t['o']);
$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;

$element 	= isset($item['element']) ? $item['element'] : '';
$type 		= isset($item['type']) ? $item['type'] : '';
$folder 	= isset($item['folder']) ? $item['folder'] : '';
$version 	= isset($item['version']) ? $item['version'] : '';

$extension						= array();
$extension['installed']			= false;
$extension['enabled']			= false;
$extension['version']			= $version;
$extension['versioncurrent']	= null;
PhocacartUtilsExtension::getExtensionLoadInfo($extension, $element, $type, $folder);



$trClass= '';
if (isset($item['featured']) && $item['featured'] == 1) {
	$trClass = 'ph-featured';
}


$iD = $i % 2;
echo "\n\n";
echo '<tr class="row'.$iD.' '.$trClass.'" sortable-group-id="0">'. "\n";

$image 		= isset($item['image']) ? $item['image'] : '';

if ($image != '') {
	$image = '<img src="'.$this->escape($image).'" alt="" style="width: 48px;height: 48px" />';
}

echo $r->td($image, "small");

$name 		= isset($item['name']) ? $item['name'] : '';
$linkName	= isset($item['link']) ? $item['link'] : '';
if ($name != '' && $linkName != '') {
	$name = '<a href="'.$this->escape($linkName).'" target="_blank">'.$name.'</a> '.$linkIcon;
}
echo $r->td($name, "small");

$description = isset($item['description']) ? $item['description'] : '';
echo $r->td($description);

$versionCurrent = $extension['versioncurrent'] ? $extension['versioncurrent'] : $extension['version'];

echo $r->td($versionCurrent);

$developer 		= isset($item['developer']) ? $item['developer'] : '';
$linkDeveloper	= isset($item['developerlink']) ? $item['developerlink'] : '';
if ($developer != '' && $linkDeveloper != '') {
	$developer = '<a href="'.$this->escape($linkDeveloper).'" target="_blank">'.$developer.'</a> '.$linkIcon;
}
echo $r->td($developer, "small");


$obtainType = isset($item['obtaintype']) ? $item['obtaintype'] : '';
echo $r->td(PhocacartUtilsSettings::getExtensionsJSONObtainTypeText($obtainType));

// ACTION
if ($canCreate && $canChange && $canEdit) {
	$download = isset($item['download']) ? $item['download'] : '';
	echo $r->td(PhocacartUtilsExtension::getExtensionsObtainTypeButton($obtainType, $download, $extension));
}


echo '</tr>'. "\n";
						
		//}
	}
}
echo '</tbody>'. "\n";

echo $r->tblFoot('', 7);///
echo $r->endTable();

echo '<input type="hidden" name="type" value="'.$this->state->get('filter.category_id'). '" />'. "\n";
echo $r->formInputs($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();

}
?>
