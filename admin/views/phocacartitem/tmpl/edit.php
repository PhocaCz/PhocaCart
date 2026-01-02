<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\I18n\I18nHelper;

// ASSOCIATION
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');



// Custom fields
$this->useCoreUI = true;

$app   = Factory::getApplication();
$input = $app->getInput();
$r     = $this->r;


// phocacartitem-form ==> adminForm
$js = '
let phRequestActive = null;

function phCheckRequestStatus(i, task) {
    i++;
    if (i > 30) {
        /* Stop Loop */
        phRequestActive = null;
    }

    if (phRequestActive) {
        setTimeout(function(){
            phCheckRequestStatus(i, task);
        }, 1000);
    } else {
        var cidA = [];
        jQuery(\'[name*="jform[catid_multiple]"]\').each((_, edit) => {
            cidA.push(...jQuery(edit).val())
        });
            
        if (task != "'. $this->t['task'].'.cancel" && task != "phocacartwizard.backtowizard" && !cidA.length) {
            alert("'. $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . ' - '. $this->escape(Text::_('COM_PHOCACART_ERROR_CATEGORY_NOT_SELECTED')).'");
        } else if (task == "' . $this->t['task'] . '.cancel" || task == "phocacartwizard.backtowizard" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
            Joomla.submitform(task, document.getElementById("adminForm"));

            /* Close Modal */
            if (task !== "phocacartitem.apply") {
                window.parent.jQuery("#phocacartitemEdit' . $this->item->id . 'Modal").modal("hide");
            }
        } else {
            Joomla.renderMessages({error: ["' . Text::_('JGLOBAL_VALIDATION_FORM_FAILED', true) . '"]});
        }
    }
}

Joomla.submitbutton = function(task) {
    phCheckRequestStatus(0, task);
}
';
Factory::getDocument()->addScriptDeclaration($js);

// ASSOCIATION
$assoc = I18nHelper::associationsEnabled();

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? 'component' : '';

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'item_associations', 'jmetadata');

echo $r->startForm($this->t['o'], $this->t['task'], (int)$this->item->id, 'adminForm', 'adminForm', '', $layout, $tmpl);
// First Column
echo '<div class="form-horizontal">';
$tabs                   = array();
$tabs['general']        = Text::_($this->t['l'] . '_GENERAL_OPTIONS');
$tabs['image']          = Text::_($this->t['l'] . '_IMAGE_OPTIONS');
$tabs['attributes']     = Text::_($this->t['l'] . '_ATTRIBUTES');
$tabs['specifications'] = Text::_($this->t['l'] . '_SPECIFICATIONS');
$tabs['related']        = Text::_($this->t['l'] . '_RELATED_PRODUCTS');
$tabs['stock']          = Text::_($this->t['l'] . '_STOCK_OPTIONS');
$tabs['discount']       = Text::_($this->t['l'] . '_DISCOUNT_OPTIONS');
$tabs['download']       = Text::_($this->t['l'] . '_DOWNLOAD_OPTIONS');
$tabs['size']           = Text::_($this->t['l'] . '_SIZE_OPTIONS');
$tabs['reward']         = Text::_($this->t['l'] . '_REWARD_POINTS');
$tabs['publishing']     = Text::_($this->t['l'] . '_PUBLISHING_OPTIONS');
$tabs['feed']           = Text::_($this->t['l'] . '_FEED_OPTIONS');
$tabs['metadata']       = Text::_($this->t['l'] . '_METADATA_OPTIONS');
$tabs['subscription']   = Text::_('COM_PHOCACART_FIELDSET_SUBSCRIPTION');
$tabs['aidata']       = Text::_($this->t['l'] . '_AI_TASKS');
//$tabs['fields']       = T ext::_($this->t['l'] . '_FIELDS');
if (!$isModal && $assoc) {
    $tabs['associations'] = Text::_($this->t['l'] . '_ASSOCIATIONS');
}

echo $r->navigation($tabs);

$formArray = array ('title', 'alias');
echo $r->groupHeader($this->form, $formArray);

echo $r->startTabs();

echo $r->startTab('general', $tabs['general'], 'active');


// Customer Group Price
$idMd       = 'phEditProductPriceGroupModal';
$textButton = 'COM_PHOCACART_CUSTOMER_GROUP_PRICES';
$w          = 500;
$h          = 400;

echo '<div class="row">';
echo '<div class="col-lg-9">';

$formArray = array('price', 'price_original', 'tax_id', 'catid_multiple', 'catid', 'manufacturer_id', 'title_long', 'sku', 'upc', 'ean', 'jan', 'mpn', 'isbn', 'serial_number', 'registration_key', 'external_id', 'external_key', 'external_link', 'external_text', 'external_link2', 'external_text2', 'access', 'group', 'featured', 'featured_background_image', 'video', 'public_download_file', 'public_download_text', 'public_play_file', 'public_play_text', 'condition', 'type_feed', 'type_category_feed');
echo $r->group($this->form, $formArray);
$formArray = array('description');
echo $r->group($this->form, $formArray, 1);
$formArray = array('description_long');
echo $r->group($this->form, $formArray, 1);
$formArray = array('features');
echo $r->group($this->form, $formArray, 1);
$formArray = array ('special_parameter', 'special_image');
echo $r->group($this->form, $formArray);


// ASSOCIATION
$this->form->setFieldAttribute('id', 'type', 'hidden');
$formArray = array('id');
echo $r->group($this->form, $formArray);

echo '</div>'; // END col-lg-9
echo '<div class="col-lg-3">';
echo '<div class="ph-admin-additional-box">';
if ($this->item->image != '') {
    $pathImage = PhocacartPath::getPath('productimage');
    $image     = PhocacartImage::getThumbnailName($pathImage, $this->item->image, 'small');
    $image->rel = PhocacartUtils::getSvgOriginalInsteadThumb($image->rel);//SVG support
    echo '<div class="ph-admin-additional-box-img-box"><img src="' . Uri::root() . $image->rel . '" alt="" /></div><hr />';
}

$linkStatus = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacarteditproductpricegroup&tmpl=component&id=' . (int)$this->item->id);
echo '<a href="#' . $idMd . '" role="button" class="ph-u ' . $idMd . 'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="' . $linkStatus . '" data-height="' . $h . '" data-width="' . $w . '">' . Text::_($textButton) . '</a>';
echo $r->modalWindowDynamic($idMd, $textButton, $w, $h, false);


// Product Price History

$idMd       = 'phEditProductPriceHistoryModal';
$textButton = 'COM_PHOCACART_PRODUCT_PRICE_HISTORY';
$w          = 500;
$h          = 400;

$linkStatus = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacarteditproductpricehistory&tmpl=component&id=' . (int)$this->item->id);
echo '<br /><a href="#' . $idMd . '" role="button" class="ph-u ' . $idMd . 'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="' . $linkStatus . '" data-height="' . $h . '" data-width="' . $w . '">' . Text::_($textButton) . '</a>';
echo $r->modalWindowDynamic($idMd, $textButton, $w, $h, false);


// Preview
if ((int)$this->item->id > 0) {

    $catidA = PhocacartCategoryMultiple::getCategories((int)$this->item->id, 1);
    if (isset($catidA[0]) && $catidA[0] > 0) {
        $idPr       = 'phEditProductPreview';
        $textButton = 'COM_PHOCACART_PRODUCT_PREVIEW';
        $w          = 500;
        $h          = 400;

        $linkPreview = PhocacartRoute::getItemRoute((int)$this->item->id, (int)$catidA[0], '', '', array(), 1) /* . '&tmpl=component'*/
        ;

        $linkPreview = PhocacartPath::getRightPathLink($linkPreview);


        echo '<br /><a href="#' . $idPr . '" role="button" class="ph-u ' . $idPr . 'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="' . $linkPreview . '" data-height="' . $h . '" data-width="' . $w . '">' . Text::_($textButton) . '</a>';

        $footer = '<span class="ph-warning-modal-window">' . Text::_('COM_PHOCACART_YOU_ARE_PREVIEWING_LIVE_PAGE') . '</span><button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-hidden="true">' . Text::_('COM_PHOCACART_CLOSE') . '</button>';
        echo $r->modalWindowDynamic($idPr, $textButton, $w, $h, false, 0, '', '', $footer);

    }
}

echo '</div>'; // END ph-admin-additional-box

echo $r->group($this->form, ['published', 'type', 'language', 'tags', 'taglabels', 'owner_id', 'internal_comment']);

echo '</div>'; // END col-lg-3
echo '</div>'; // END row
echo $r->endTab();



// IMAGES
echo $r->startTab('image', $tabs['image']);


$formArray = array('image');
echo $r->group($this->form, $formArray);
echo '<h3>' . Text::_($this->t['l'] . '_ADDITIONAL_IMAGES') . '</h3>';

$formArray = array('additional_images');// , 'download_hits' - it is counted in orders
echo $r->group($this->form, $formArray);

echo $r->endTab();


// ATTRIBUTES, OPTIONS
/*
$pathAttributes = PhocacartPath::getPath('attributefile');
$w = 700;
$h = 400;
$urlO 	= 'index.php?option=com_phocacart&amp;view=phocacartmanager&amp;tmpl=component&amp;manager=productimage&amp;field=jform_optionimage';
$urlO2 	= 'index.php?option=com_phocacart&amp;view=phocacartmanager&amp;tmpl=component&amp;manager=productimage&amp;field=jform_optionimage_medium';
$urlO3 	= 'index.php?option=com_phocacart&amp;view=phocacartmanager&amp;tmpl=component&amp;manager=productimage&amp;field=jform_optionimage_small';
$urlO4 	= 'index.php?option=com_phocacart&amp;view=phocacartmanager&amp;tmpl=component&amp;manager=attributefile&amp;field=jform_optiondownload_file';


*/
echo $r->startTab('attributes', $tabs['attributes']);
echo '<h3>' . Text::_($this->t['l'] . '_ATTRIBUTES') . '</h3>';
$formArray = array('attributes');
echo $r->group($this->form, $formArray);
echo $r->endTab();



// SPECIFICATIONS
/*$w = 700;
$h = 400;
$urlO 	= 'index.php?option=com_phocacart&amp;view=phocacartmanager&amp;tmpl=component&amp;manager=productimage&amp;field=jform_specimage';
$urlO2 	= 'index.php?option=com_phocacart&amp;view=phocacartmanager&amp;tmpl=component&amp;manager=productimage&amp;field=jform_specimage_medium';
$urlO3 	= 'index.php?option=com_phocacart&amp;view=phocacartmanager&amp;tmpl=component&amp;manager=productimage&amp;field=jform_specimage_small';
*/
echo $r->startTab('specifications', $tabs['specifications']);
echo '<h3>' . Text::_($this->t['l'] . '_SPECIFICATIONS') . '</h3>';
$formArray = array('specifications');
echo $r->group($this->form, $formArray);
echo $r->endTab();


// RELATED
echo $r->startTab('related', $tabs['related']);
$formArray = array('related', 'bundles');
echo $r->group($this->form, $formArray);
echo $r->endTab();


// STOCK
echo $r->startTab('stock', $tabs['stock']);

$idMd       = 'phEditStockAdvancedModal';
$textButton = 'COM_PHOCACART_ADVANCED_STOCK_OPTIONS';
$w          = 500;
$h          = 400;

$linkStatus = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacarteditstockadvanced&tmpl=component&id=' . (int)$this->item->id);
echo '<div class="ph-float-right ph-admin-additional-box"><a href="#' . $idMd . '" role="button" class="ph-u ' . $idMd . 'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="' . $linkStatus . '" data-height="' . $h . '" data-width="' . $w . '">' . Text::_($textButton) . '</a>';
echo $r->modalWindowDynamic($idMd, $textButton, $w, $h, false);

echo '</div>';

$formArray = array('stock', 'stock_calculation', 'min_quantity', 'min_multiple_quantity', 'min_quantity_calculation', 'max_quantity', 'max_quantity_calculation', 'stockstatus_a_id', 'stockstatus_n_id', 'delivery_date');
echo $r->group($this->form, $formArray);
echo $r->endTab();


// PRODUCT DISCOUNTS
echo $r->startTab('discount', $tabs['discount']);
echo '<h3>' . Text::_($this->t['l'] . '_PRODUCT_DISCOUNT') . '</h3>';
$formArray = array('discounts');
echo $r->group($this->form, $formArray);
echo $r->endTab();


// DOWNLOAD
echo $r->startTab('download', $tabs['download']);
$formArray = array('download_folder', 'download_token', 'download_file', 'download_days');// , 'download_hits' - it is counted in orders
echo $r->group($this->form, $formArray);

echo '<h3>' . Text::_($this->t['l'] . '_ADDITIONAL_DOWNLOAD_FILES') . '</h3>';
$formArray = array('additional_download_files');// , 'download_hits' - it is counted in orders
echo $r->group($this->form, $formArray);
echo $r->endTab();


// SIZE
echo $r->startTab('size', $tabs['size']);
$formArray = array('length', 'width', 'height', 'weight', 'volume', 'unit_amount', 'unit_unit',);
echo $r->group($this->form, $formArray);
echo $r->endTab();

// REWARD POINTS
echo $r->startTab('reward', $tabs['reward']);

$idMd       = 'phEditProductPointGroupModal';
$textButton = 'COM_PHOCACART_CUSTOMER_GROUP_RECEIVED_POINTS';
$w          = 500;
$h          = 400;

$linkStatus = Route::_('index.php?option=' . $this->t['o'] . '&view=phocacarteditproductpointgroup&tmpl=component&id=' . (int)$this->item->id);
echo '<div class="ph-float-right ph-admin-additional-box"><a href="#' . $idMd . '" role="button" class="ph-u ' . $idMd . 'ModalButton" data-bs-toggle="modal" title="' . Text::_($textButton) . '" data-src="' . $linkStatus . '" data-height="' . $h . '" data-width="' . $w . '">' . Text::_($textButton) . '</a>';

echo $r->modalWindowDynamic($idMd, $textButton, $w, $h, false);
echo '</div>';


$formArray = array('points_needed', 'points_received');
echo $r->group($this->form, $formArray);
echo $r->endTab();



// PUBLISHING
echo $r->startTab('publishing', $tabs['publishing']);
foreach ($this->form->getFieldset('publish') as $field) {
    if (in_array($field->fieldname, ['published', 'type', 'language', 'tags', 'taglabels', 'owner_id', 'internal_comment'])) {
        continue;
    }

    $description = Text::_($field->description);
    $descriptionOutput = '';
    if ($description != '') {
        $descriptionOutput = '<div role="tooltip">'.$description.'</div>';
    }


    echo '<div class="control-group ph-par-'.$field->fieldname.'">';
    if (!$field->hidden) {
        echo '<div class="control-label">' . $field->label . $descriptionOutput . '</div>';
    }
    echo '<div class="controls">';
    echo $field->input;
    echo '</div></div>';
}

echo $this->loadTemplate('parameter');
echo $r->endTab();


// FEED
echo $r->startTab('feed', $tabs['feed']);
echo $this->loadTemplate('feed');
echo $r->endTab();


// METADATA
echo $r->startTab('metadata', $tabs['metadata']);
echo $this->loadTemplate('metadata');
echo $r->endTab();


echo $r->startTab('subscription', $tabs['subscription']);
// Render the subscription fieldset
$subscriptionFieldset = $this->form->getFieldset('subscription');

if (!empty($subscriptionFieldset)) {
    // We can use the generic render or custom loop.
    // Phoca Cart usually uses $r->group explicitly or loops.
    // Let's use the loop style from 'publishing' tab for consistency.
    foreach ($subscriptionFieldset as $field) {
        $description = Text::_($field->description);
        $descriptionOutput = '';
        if ($description != '') {
            $descriptionOutput = '<div role="tooltip">'.$description.'</div>';
        }

        echo '<div class="control-group ph-par-'.$field->fieldname.'">';
        if (!$field->hidden) {
             echo '<div class="control-label">' . $field->label . $descriptionOutput . '</div>';
        }
        echo '<div class="controls">';
        echo $field->input;
        echo '</div></div>';
    }
} else {
    echo '<div class="ph-pro-box">'.Text::_('COM_PHOCACART_ADVANCED_FEATURE_PRO'). '</div>';
}
echo $r->endTab();

echo $r->startTab('aidata', $tabs['aidata']);
echo $this->loadTemplate('aidata');
echo $r->endTab();


// ASSOCIATION
if (!$isModal && $assoc) {
    echo $r->startTab('associations', $tabs['associations']);
    echo $this->loadTemplate('associations');
    echo $r->endTab();
} else if ($isModal && $assoc) {
    echo '<div class="hidden">' . $this->loadTemplate('associations') . '</div>';
}



// Display custom field parameters and ignore current fieldsetsw including all feed fieldsets

$ignoreField = [];
$ignoreField[] = 'aidata';
$ignoreField[] = 'metadata';
$ignoreField[] = 'publish';
$ignoreField[] = 'item_associations';
$ignoreField[] = 'items_parameter';
$ignoreField[] = 'subscription';
$currentFields = $this->form->getFieldsets();
if (!empty($currentFields)) {
    foreach ($currentFields as $k => $v) {
        if (isset($v->name) && strpos($v->name, 'feed_') !== false) {
            $ignoreField[] = $v->name;
        }
    }
}


$this->ignore_fieldsets = $ignoreField;
echo LayoutHelper::render('joomla.edit.params', $this);



echo $r->endTabs();
echo '</div>';//end span10
// Second Column
//echo '<div class="col-xs-12 col-sm-2 col-md-2">';


//echo '</div>';//end span2
echo $r->formInputs($this->t['task']);

if ($forcedLanguage = Factory::getApplication()->getInput()->get('forcedLanguage', '', 'CMD')) {
    echo '<input type="hidden" name="forcedLanguage" value="' . $forcedLanguage . '" />';
}
echo $r->endForm();


?>

