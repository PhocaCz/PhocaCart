<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Multilanguage;
// ASSOCIATION

$app = Factory::getApplication();
if ($app->isClient('site')) {
	Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

$r 			= $this->r;
$user		= Factory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$function  	= $app->input->getCmd('function', 'jSelectPhocacartcategory');
$onclick   	= $this->escape($function);

if (!empty($editor)) {
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	Factory::getDocument()->addScriptOptions('xtd-phocacartcategories', array('editor' => $editor));
	$onclick = "jSelectPhocacartcategory";
}

$iconStates = array(
	-2 => 'icon-trash',
	0  => 'icon-unpublish',
	1  => 'icon-publish',
	2  => 'icon-archive',
);

$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$saveOrder	= $listOrder == 'a.ordering';

$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
	$saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
}

echo $r->jsJorderTable($listOrder);

//echo '<div class="clearfix"></div>';

// phocacartcategory-form => adminForm
echo $r->startFormModal($this->t['o'], $this->t['tasks'], 'adminForm', 'adminForm', $function);

echo $r->startMainContainerNoSubmenu();

echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-title">'.HTMLHelper::_('searchtools.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-parentcattitle">'.HTMLHelper::_('searchtools.sort', $this->t['l'].'_PARENT_CATEGORY', 'parentcat_title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-access">'.Text::_($this->t['l'].'_ACCESS').'</th>'."\n";
echo '<th class="ph-language">'.HTMLHelper::_('searchtools.sort',  	'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-hits">'.HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_HITS', 'a.hits', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr 	= "";
$j 				= 0;

if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {
		if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
			$j++;

			$urlEdit		= 'index.php?option='.$this->t['o'].'&task='.$this->t['task'].'.edit&id=';
			$orderkey   	= array_search($item->id, $this->ordering[$item->parent_id]);
			$ordering		= ($listOrder == 'a.ordering');
			$canCreate		= $user->authorise('core.create', $this->t['o']);
			$canEdit		= $user->authorise('core.edit', $this->t['o']);
			$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
			$canChange		= $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
			$linkEdit 		= Route::_( $urlEdit.(int) $item->id );
			$linkParent		= Route::_( $urlEdit.(int) $item->parent_id );
			$canEditParent	= 0;//$user->authorise('core.edit', $this->t['o']);
			$linkLang		= Route::_('index.php?option='.$this->t['o'].'&view=phocacartcategory&id='.$this->escape($item->id).'&lang='.$this->escape($item->language));

			//$linkCat	= Route::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
			$canEdit	= 0;// FORCE NOT EDITING CATEGORY IN MODAL $user->authorise('core.edit', $this->t['o']);
			if ($item->language && Multilanguage::isEnabled()) {
				$tag = strlen($item->language);
				if ($tag == 5) {
					$lang = substr($item->language, 0, 2);
				} else if ($tag == 6) {
					$lang = substr($item->language, 0, 3);
				} else {
					$lang = '';
				}
			} else if (!Multilanguage::isEnabled()) {
				$lang = '';
			}


			$parentsStr = '';
			if (isset($item->parentstree)) {
				$parentsStr = ' '.$item->parentstree;
			}
			if (!isset($item->level)) {
				$item->level = 0;
			}


			//echo $r->startTr($i, $this->t['catid']);
			$iD = $i % 2;
			echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->parent_id.'" item-id="'.$item->id.'" parents="'.$parentsStr.'" level="'. $item->level.'">'. "\n";
//echo '<tr class="row'.$iD.'" sortable-group-id="'.$item->parent_id.'" >'. "\n";

			echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering, false);
        echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering, false);
			/*$checkO = '';
			if ($item->checked_out) {
				$checkO .= HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'].'.', $canCheckin);
			}
			if ($canCreate || $canEdit) {
				$checkO .= '<a href="'. Route::_($linkEdit).'">'. $this->escape($item->title).'</a>';
			} else {
				$checkO .= $this->escape($item->title);
			}
			$checkO .= ' <span class="smallsub">(<span>'.Text::_($this->t['l'].'_FIELD_ALIAS_LABEL').':</span>'. $this->escape($item->alias).')</span>';
			echo $r->td($checkO, "small", 'th');
			*/
			//$linkBox = '<a class="select-link" href="javascript:void(0)" data-function="'.$this->escape($onclick).'" data-id="'.$item->id.'" data-title="'.$this->escape($item->title).'" data-uri="'. $this->escape($linkLang).'" data-language="'.$this->escape($lang).'">';

			$linkBox = '<a class="select-link" href="javascript:void(0)" onclick="if (window.parent) window.parent.'.$this->escape($function).'(\''. $item->id.'\', \''. $this->escape(addslashes($item->title)).'\', null, \''. $this->escape($linkLang).'\', \''. $this->escape($lang).'\', null);">';

			$linkBox .= $this->escape($item->title);
			$linkBox .= '</a>';

			echo $r->td($linkBox, "small");

			//echo $r->td(JHtml::_('jgrid.published', $item->published, $i, $this->t['tasks'].'.', $canChange), "small");

			echo $r->td('<span class="'.$iconStates[$this->escape($item->published)].'" aria-hidden="true"></span>');

			if ($canEditParent) {
				$parentO = '<a href="'. Route::_($linkParent).'">'. $this->escape($item->parentcat_title).'</a>';
			} else {
				$parentO = $this->escape($item->parentcat_title);
			}
			echo $r->td($parentO, "small");
			echo $r->td($this->escape($item->access_level), "small");

			//echo $r->tdLanguage($item->language, $item->language_title, $this->escape($item->language_title));
			echo $r->td(LayoutHelper::render('joomla.content.language', $item), 'small');

			echo $r->td($item->hits, "small");
			echo $r->td($item->id, "small");

			echo $r->endTr();

		}
	}
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 9);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);

if ($forcedLanguage = Factory::getApplication()->input->getCmd('forcedLanguage')) {
  echo '<input type="hidden" name="forcedLanguage" value="' . $forcedLanguage . '" />';
}

echo $r->endMainContainer();
echo $r->endForm();
