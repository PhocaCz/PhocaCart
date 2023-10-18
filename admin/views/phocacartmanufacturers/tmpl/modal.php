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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Associations;

$app = Factory::getApplication();
if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$r = $this->r;
$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$function  	= $app->input->getCmd('function', 'jSelectPhocacartmanufacturer');
$sortFields = $this->getSortFields();

$nrColumns = 6;
echo $r->jsJorderTable($listOrder);

echo $r->startFormModal($this->t['o'], $this->t['tasks'], 'adminForm', 'adminForm', $function);

echo $r->startMainContainerNoSubmenu();
echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);

echo $r->startTable('manufacturerList');

echo $r->startTblHeader();

echo $r->secondColumnHeader($listDirn, $listOrder);
echo '<th class="ph-title">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_TITLE', 'a.title', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-published">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PUBLISHED', 'a.published', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-productcount">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_PRODUCT_COUNT', 'a.count_products', $listDirn, $listOrder) . '</th>' . "\n";
echo '<th class="ph-language">'.HTMLHelper::_('searchtools.sort',  	'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">' . HTMLHelper::_('searchtools.sort', $this->t['l'] . '_ID', 'a.id', $listDirn, $listOrder) . '</th>' . "\n";

echo $r->endTblHeader();

echo $r->startTblBody(false, '', $listDirn);

$originalOrders = array();
$parentsStr = "";
$j = 0;

if (is_array($this->items)) {
    foreach ($this->items as $i => $item) {
        $j++;

        $orderkey = array_search($item->id, $this->ordering[0]);
        $ordering = ($listOrder == 'a.ordering');
        $linkLang		= Route::_('index.php?option='.$this->t['o'].'&view=phocacartmanufacturer&id='.$this->escape($item->id).'&lang='.$this->escape($item->language));

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

        echo $r->startTr($i, isset($item->catid) ? (int)$item->catid : 0);

        echo $r->secondColumn($i, $item->id, false, false, $orderkey, $item->ordering);

        $linkBox = '<a class="select-link" href="javascript:void(0)" onclick="if (window.parent) window.parent.'.$this->escape($function).'(\''. $item->id.'\', \''. $this->escape(addslashes($item->title)).'\', null, \''. $this->escape($linkLang).'\', \''. $this->escape($lang).'\', null);">';
        $linkBox .= $this->escape($item->title);
        $linkBox .= '</a>';
        echo $r->td($linkBox, "small", 'th');

        echo $r->td(HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'] . '.', false) . PhocacartHtmlFeatured::featured($item->featured, $i, false, 'manufacturer'), "small");

        $pC = '<div class="center">' . $item->count_products;
        if (PhocacartUtils::validateDate($item->count_date)) {
            $pC .= '<br><small class="nowrap">(' . HTMLHelper::_('date', $item->count_date, 'd-m-Y H:i') . ')</small>';
        }
        $pC .= '</div>';
        echo $r->td($pC, "small");

        echo $r->td(LayoutHelper::render('joomla.content.language', $item), 'small');


        echo $r->td($item->id, "small");

        echo $r->endTr();
    }
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), $nrColumns);

echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);

if ($forcedLanguage = Factory::getApplication()->input->getCmd('forcedLanguage')) {
  echo '<input type="hidden" name="forcedLanguage" value="' . $forcedLanguage . '" />';
}

echo $r->endMainContainer();
echo $r->endForm();

