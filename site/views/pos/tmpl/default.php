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

echo '<div id="ph-pc-pos-site">';


// POPUP
// jQuery UI
//echo '<div id="phDialogConfirm" class="ph-dialog" title="'. Text::_('COM_PHOCACART_CONFIRM').'"></div>';


// Bootstrap Modal
echo '<div id="phDialogConfirm" class="modal fade" tabindex="-1" role="dialog">';
echo '<div class="modal-dialog" role="document">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<h4 class="modal-title">'. Text::_('COM_PHOCACART_CONFIRM').'</h4>';
echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
echo '</div>';
echo '<div class="modal-body"></div>';
echo '<div class="modal-footer">';
echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'. Text::_('COM_PHOCACART_CANCEL').'</button>';
echo '<button id="phDialogConfirmSave" type="button" class="btn btn-success">'. Text::_('COM_PHOCACART_OK').'</button>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';


// TOP
echo '<div class="ph-pos-wrap-top">';
echo $this->loadTemplate('main_top');
echo '</div>';


// MAIN
echo '<div class="ph-pos-wrap-main">';


// MAIN LEFT
echo '<div class="ph-pos-main-column-left">';


// MAIN FILTER
echo '<div class="ph-pos-main-filter">';
echo $this->loadTemplate('main_filter');
echo '</div>';


// MAIN CATEGORIES
echo '<div class="ph-pos-main-categories">';
echo '<div id="phPosCategoriesBox">';
echo $this->loadTemplate('main_categories');
echo '</div>';
echo '</div>';


// MAIN CONTENT
echo '<div class="ph-pos-main-content">';

// HEADER - NOT AJAX
echo '<div id="ph-pc-pos-box" class="pc-view pc-pos-view'.$this->p->get( 'pageclass_sfx' ).'">';
echo '<div id="phPosContentBox">';
echo $this->loadTemplate('main_content_products');// divided into more different views - as default main will display products

// FOOTER - NOT AJAX
echo '</div>';// end #phItemsBox
echo '</div>';// end #ph-pc-category-box

echo '<div id="phContainer"></div>';
echo '<div>&nbsp;</div>';


echo '</div>';// end ph-pos-main-content

echo '</div>';// end ph-pos-column-left


// MAIN RIGHT
echo '<div class="ph-pos-main-column-right">';


// MAIN CART
echo '<div class="ph-pos-main-cart" id="phPosCart">';
echo '<div class="phPosCartBox" id="phPosCartBox">';
echo $this->loadTemplate('main_cart');
echo '</div>';
echo '</div>';


// MAIN INPUT
echo '<div class="ph-pos-main-input">';
echo '<div id="phPosInputBox">';
echo $this->loadTemplate('main_input');
echo '</div>';
echo '</div>';


echo '</div>';// end ph-pos-column-right

echo '</div>';// end ph-pos-wrap-main


// BOTTOM
echo '<div class="ph-pos-wrap-bottom">';
echo $this->loadTemplate('bottom');
echo '</div>';


echo '</div>';// end ph-pc-pos-site

echo '<div class="ph-pos-warning-msg-box" id="phPosWarningMsgBox" style="display:none"></div>';
?>
