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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
$linkIcon = '';//'<sup><span class="glyph icon glyph icon-link"></span></sup>';


$r      = $this->r;
$user   = Factory::getUser();
$userId = $user->get('id');

$saveOrderingUrl = '';


if ($this->t['load_extension_list'] == 0) {

    echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');

    //echo $r->startFilter();
    //echo $r->endFilter();
    echo $r->startMainContainer();

   // echo '<div class="alert alert-warning">';
    echo '<button type="button" class="close" data-bs-dismiss="alert">×</button>';
    //echo '<h4 class="alert-heading">'.Text::_('COM_PHOCACART_INFO').'</h4>';
    echo '<div class="alert alert-warning alert-dismissible fade show">' . Text::_('COM_PHOCACART_LOADING_OF_EXTENSION_LIST_DISABLED') . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="'.Text::_('COM_PHOCACART_CLOSE').'"></button></div>';
    //echo '</div>';

  //  echo '<div class="alert alert-info">';
    //echo '<button type="button" class="close" data-bs-dismiss="alert">×</button>';
    //echo '<h4 class="alert-heading">'.Text::_('COM_PHOCACART_INFO').'</h4>';
    echo '<div class="alert alert-info alert-dismissible fade show">' . Text::_('COM_PHOCACART_DISCOVER') . ' <a href="https://www.phoca.cz/phocacart-extensions" target="_blank" style="text-decoration: underline">' . Text::_('COM_PHOCACART_PHOCA_CART_EXTENSIONS') . '</a> ' . $linkIcon;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="'.Text::_('COM_PHOCACART_CLOSE').'"></button>' . '</div>';
   // echo '</div>';


    /* echo $r->startFilterBar();
     echo $r->startFilterBar(2);
     echo $r->endFilterBar();
     echo $r->endFilterBar();*/

    echo $r->endMainContainer();
    echo $r->endForm();

} else {


    $listOrder = $this->escape($this->state->get('list.ordering'));
    $listDirn  = $this->escape($this->state->get('list.direction'));
    $canOrder  = $user->authorise('core.edit.state', $this->t['o']);

    $saveOrder = false;
    /*if ($this->t['ordering'] && !empty($this->ordering)) {
        $saveOrder	= $listOrder == 'pc.ordering';
        $saveOrderingUrl = '';
    if ($saveOrder && !empty($this->items)) {
        $saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
    }
    }*/
    $sortFields = $this->getSortFields();
    echo $r->jsJorderTable($listOrder);


    echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');
    //echo $r->startFilter();
    //echo $r->selectFilterPublished('JOPTION_SELECT_PUBLISHED', $this->state->get('filter.published'));
    //echo $r->selectFilterLanguage('JOPTION_SELECT_LANGUAGE', $this->state->get('filter.language'));
    //echo $r->selectFilterCategory(PhocacartCategory::options($this->t['o']), 'JOPTION_SELECT_CATEGORY', $this->state->get('filter.category_id'));
    //echo $r->endFilter();

    echo $r->startMainContainer();

    //echo '<div class="alert alert-info">';
   // echo '<button type="button" class="close" data-bs-dismiss="alert">×</button>';
    //echo '<h4 class="alert-heading">'.Text::_('COM_PHOCACART_INFO').'</h4>';
    echo '<div class="alert alert-info alert-dismissible fade show">' . Text::_('COM_PHOCACART_DISCOVER') . ' <a href="https://www.phoca.cz/phocacart-extensions" target="_blank" style="text-decoration: underline">' . Text::_('COM_PHOCACART_PHOCA_CART_EXTENSIONS') . '</a> ' . $linkIcon;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="'.Text::_('COM_PHOCACART_CLOSE').'"></button>' . '</div>';
    //echo '</div>';


    if (is_array($this->news)) {
        foreach ($this->news as $n => $m) {
            if (isset($m['name']) && $m['name'] != '' && isset($m['description']) && $m['description'] != '') {

                $mClass      = isset($m['class']) ? $this->escape(strip_tags($m['class'])) : '';
                $mStyle      = isset($m['style']) ? $this->escape(strip_tags($m['style'])) : '';
                $mImage      = isset($m['image']) ? $this->escape(strip_tags($m['image'])) : '';
                $mImageLarge = isset($m['imagelarge']) ? $this->escape(strip_tags($m['imagelarge'])) : '';
                $mLink       = isset($m['link']) ? $this->escape(strip_tags($m['link'])) : '';
                $aStart      = '';
                $aEnd        = '';

                if ($mLink != '') {
                    $aStart = '<a href="' . $mLink . '" target="_blank">';
                    $aEnd   = '</a>';
                }

                echo '<div class="ph-featured-box ' . $mClass . '" style="' . $mStyle . '">';
                echo '<div class="ph-featured-head">' . $aStart . $this->escape(strip_tags($m['name'])) . $aEnd . '</div>';

                echo '<div class="ph-featured-image-large">' . $aStart . '<img src="' . $mImageLarge . '" alt="" />' . $aEnd . '</div>';

                echo '<div class="ph-featured-description">';
                if ($mImage != '') {
                    echo '<div class="ph-featured-image">' . $aStart . '<img src="' . $mImage . '" alt="" />' . $aEnd . '</div>';
                }
                echo $aStart . $this->escape(strip_tags($m['description'])) . $aEnd;

                echo '<div class="ph-cb"></div>';
                echo '</div>';

                echo '</div>';


            }
        }
    }
    /*  echo $r->startFilterBar();

      echo $r->startFilterBar(2);
      echo $r->selectFilterCategory(PhocacartUtilsSettings::getExtenstionsArray($this->t['o']), '', $this->state->get('filter.category_id'));
      echo $r->endFilterBar();

      echo $r->endFilterBar();*/


    $filters = $this->filterForm->getGroup('filter');
    if ($filters) {
        echo '<div class="js-stools ph-pull-right ph-extensions-filter">';
        foreach ($filters as $fieldName => $field) {
            echo '<div class="js-stools-field-filter">';
            echo $field->input;
            echo '</div >';
        }
        echo '</div>';
    }

    echo $r->startTable('extensionList');

    echo $r->startTblHeader();


    echo '<th class="ph-image">' . Text::_($this->t['l'] . '_IMAGE') . '</th>' . "\n";
    echo '<th class="ph-title-small">' . Text::_($this->t['l'] . '_NAME') . '</th>' . "\n";
    echo '<th class="ph-description">' . Text::_($this->t['l'] . '_DESCRIPTION') . '</th>' . "\n";
    echo '<th class="ph-version">' . Text::_($this->t['l'] . '_VERSION') . ' (J3)</th>' . "\n";
    echo '<th class="ph-version">' . Text::_($this->t['l'] . '_VERSION') . ' (J4)</th>' . "\n";
    echo '<th class="ph-developer">' . Text::_($this->t['l'] . '_DEVELOPER') . '</th>' . "\n";
    echo '<th class="ph-type">' . Text::_($this->t['l'] . '_TYPE') . '</th>' . "\n";
    echo '<th class="ph-action">' . Text::_($this->t['l'] . '_ACTION') . '</th>' . "\n";

    echo $r->endTblHeader();

    echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

    $originalOrders = array();
    $parentsStr     = "";
    $j              = 0;

    $price = new PhocacartPrice();

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
            $linkEdit 		= Route::_( $urlEdit. $item->id );

            //$linkCat	= Route::_( 'index.php?option='.$this->t['o'].'&task='.$this->t['c'].'category.edit&id='.(int) $item->category_id );
            $canEditCat	= $user->authorise('core.edit', $this->t['o']);*/

            $canCreate  = $user->authorise('core.create', $this->t['o']);
            $canEdit    = $user->authorise('core.edit', $this->t['o']);
            $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
            $canChange  = $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;

            $element = isset($item['element']) ? $item['element'] : '';
            $type    = isset($item['type']) ? $item['type'] : '';
            $folder  = isset($item['folder']) ? $item['folder'] : '';
            $version = isset($item['version']) ? $item['version'] : '';
            $version4 = isset($item['version4']) ? $item['version4'] : '';

            $extension                   = array();
            $extension['installed']      = false;
            $extension['enabled']        = false;
            $extension['version']        = $version;
            $extension['version4']        = $version4;
            $extension['versioncurrent'] = null;
            PhocacartUtilsExtension::getExtensionLoadInfo($extension, $element, $type, $folder);


            $trClass = '';
            if (isset($item['featured']) && $item['featured'] == 1) {
                $trClass = 'ph-featured';
            }


            $iD = $i % 2;
            echo "\n\n";
            echo '<tr class="row' . $iD . ' ' . $trClass . '" sortable-group-id="0">' . "\n";

            $image = isset($item['image']) ? $item['image'] : '';

            if ($image != '') {
                $image = '<img src="' . $this->escape($image) . '" alt="" style="width: 48px;height: 48px" />';
            }

            echo $r->td($image, "small");

            $name     = isset($item['name']) ? $item['name'] : '';
            $linkName = isset($item['link']) ? $item['link'] : '';
            if ($name != '' && $linkName != '') {
                $name = '<a href="' . $this->escape($linkName) . '" target="_blank">' . $name . '</a> ' . $linkIcon;
            }
            echo $r->td($name, "small");

            $description = isset($item['description']) ? $item['description'] : '';
            echo $r->td($description);

            $versionCurrent = $extension['versioncurrent'] ? $extension['versioncurrent'] : $extension['version'];

            $versionCurrent4 = $extension['versioncurrent'] ? $extension['versioncurrent'] : $extension['version4'];

            echo $r->td($versionCurrent);
            echo $r->td($versionCurrent4);

            $developer     = isset($item['developer']) ? $item['developer'] : '';
            $linkDeveloper = isset($item['developerlink']) ? $item['developerlink'] : '';
            if ($developer != '' && $linkDeveloper != '') {
                $developer = '<a href="' . $this->escape($linkDeveloper) . '" target="_blank">' . $developer . '</a> ' . $linkIcon;
            }
            echo $r->td($developer, "small");


            $obtainType = isset($item['obtaintype']) ? $item['obtaintype'] : '';
            echo $r->td(PhocacartUtilsSettings::getExtensionsJSONObtainTypeText($obtainType));


            // ACTION
            if ($canCreate && $canChange && $canEdit) {
                $download = array();
                $download['3'] = isset($item['download']) ? $item['download'] : '';
                $download['4'] = isset($item['download4']) ? $item['download4'] : '';
                //$download['5'] = isset($item['download5']) ? $item['download5'] : '';
                echo $r->td(PhocacartUtilsExtension::getExtensionsObtainTypeButton($obtainType, $download, $extension));
            }


            echo $r->endTr();

            //}
        }
    }
    echo $r->endTblBody();

    echo $r->tblFoot('', 8);//
    echo $r->endTable();

    echo '<input type="hidden" name="type" value="' . $this->state->get('filter.category_id') . '" />' . "\n";
    echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
    echo $r->endMainContainer();
    echo $r->endForm();

}
?>
