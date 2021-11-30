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
$layoutI	= new FileLayout('product_image', null, array('component' => 'com_phocacart'));
$layoutP	= new FileLayout('product_price', null, array('component' => 'com_phocacart'));
$layoutAB	= new FileLayout('attribute_options_box', null, array('component' => 'com_phocacart'));
$layoutV	= new FileLayout('button_product_view', null, array('component' => 'com_phocacart'));
$layoutPFS	= new FileLayout('form_part_start_add_to_cart_list', null, array('component' => 'com_phocacart'));
$layoutPFE	= new FileLayout('form_part_end', null, array('component' => 'com_phocacart'));
$layoutA	= new FileLayout('button_add_to_cart_list', null, array('component' => 'com_phocacart'));
$layoutA2	= new FileLayout('button_buy_now_paddle', null, array('component' => 'com_phocacart'));
$layoutA3	= new FileLayout('button_external_link', null, array('component' => 'com_phocacart'));
$layoutA4 	= new FileLayout('button_quickview', null, array('component' => 'com_phocacart'));
$layoutBSH	= new FileLayout('button_submit_hidden', null, array('component' => 'com_phocacart'));
$layoutQ	= new FileLayout('button_ask_question', null, array('component' => 'com_phocacart'));

$d 		= $displayData;
$t		= $d['t'];
$s      = $d['s'];
$col    = $d['col'];

echo '<div class="'.$s['c']['row-item'].' '.$s['c']["col.xs12.sm{$col}.md{$col}"].'">';
echo '<div class="ph-item-box '.$d['lt'].'">';

if (!empty($d['labels'])) { echo '<div class="ph-label-box">' . $d['labels'] . '</div>';}

echo '<div class="'.PhocacartRenderFront::completeClass(array($s['c']['thumbnail'], 'ph-thumbnail', 'ph-thumbnail-c', 'ph-item', $t['class_fade_in_action_icons'], $d['lt'])).'">';
echo '<div class="ph-item-content '.$d['lt'].'">';


echo '<div class="'.$s['c']['cat_item_grid'].' ph-category-action-box-icons '.$d['lt'].'">';
// :L: IMAGE
echo '<a href="'.$d['link'].'">';
if (!empty($d['layout']['dI'])) { echo $layoutI->render($d['layout']['dI']);}
echo '</a>';

echo '<div class="'.$s['c']['cat_item_btns'].' ph-category-action-icons '.$d['lt'].'">';
if ($t['fade_in_action_icons'] == 0 && $t['display_action_icons'] == 1) {
    echo $d['icon']['compare']; // if set in options, it will be displayed on other place, so this is why it is printed this way
    echo $d['icon']['wishlist'];
    echo $d['icon']['quickview'];
    echo $d['icon']['addtocart'];
}
echo '</div>';// end category_action_box_icons
echo '</div>';// end category_action_icons


echo '<div class="ph-cb"></div>';

// CAPTION, DESCRIPTION BOX
echo $d['product_header'];

// REVIEW - STAR RATING
if (!empty($d['review'])) { echo $d['review'];}

// DESCRIPTION
if (!empty($d['description'])) { echo $d['description'];}

// TAGS
if (!empty($d['tags'])) { echo '<div class="ph-tag-box">'  . '<span class="ph-tag-box-header">'.Text::_('COM_PHOCACART_TAGS'). '</span>: ' .  $d['tags'] . '</div>';}
// MANUFACTURER
if (!empty($d['manufacturer'])) { echo '<div class="ph-manufacturer-box">'  . '<span class="ph-manufacturer-box-header">'.Text::_('COM_PHOCACART_MANUFACTURER'). '</span>: ' .  $d['manufacturer'] . '</div>';}


echo '<div class="ph-item-action-box ph-caption ph-category-action-box-buttons '.$d['lt'].'">';

// :L: PRICE
if (!empty($d['layout']['dP'])) { echo $layoutP->render($d['layout']['dP']);}


// ACTION BUTTONS
echo '<div class="ph-category-action-buttons '.$d['lt'].'">';

// :L: Stock status
if (!empty($d['layout']['dSO'])) { echo $d['layout']['dSO'];}

// Start Form

if (!empty($d['layout']['dF'])) { echo $layoutPFS->render($d['layout']['dF']);}

// :L: ATTRIBUTES AND OPTIONS
if (!empty($d['layout']['dAb'])) { echo $layoutAB->render($d['layout']['dAb']);}

// :L: LINK TO PRODUCT VIEW
if (!empty($d['layout']['dV'])) { echo $layoutV->render($d['layout']['dV']);}

// :L: ADD TO CART
if (!empty($d['layout']['dA'])) { echo $layoutA->render($d['layout']['dA']);} else if ($d['icon']['addtocart'] != '') { echo $layoutBSH->render();}

// :L: ASK A QUESTION
if (!empty($d['layout']['dQ'])) { echo $layoutQ->render($d['layout']['dQ']);}


// End Form
if (!empty($d['layout']['dF'])) { echo $layoutPFE->render();}

if (!empty($d['layout']['dA2'])) { echo $layoutA2->render($d['layout']['dA2']);}
if (!empty($d['layout']['dA3'])) { echo $layoutA3->render($d['layout']['dA3']);}
if (!empty($d['layout']['dA4'])) { echo $layoutA4->render($d['layout']['dA4']);}


echo '</div>';// end category_action_buttons


echo $d['event']['onCategoryItemsItemAfterAddToCart'];
echo '<div class="ph-cb"></div>';

if ($t['fade_in_action_icons'] == 1 && $t['display_action_icons'] == 1) {

    echo '<div class="ph-category-action-box-fade-icons '.$d['lt'].'">';
    echo '<div class="ph-item-action-fade ph-category-action-fade-icons '.$d['lt'].'">';
    echo $d['icon']['compare'];
    echo $d['icon']['wishlist'];
    echo $d['icon']['quickview'];
    echo $d['icon']['addtocart'];
    echo '</div>';
    echo '</div>';
}

echo '</div>';// end category_action_box_buttons



echo '</div>';// end category_row_item_box_wrap_content
echo '</div>';// end category_row_item_box_wrap
echo '</div>';// end category_row_item_box
echo '</div>';// end category_row_item_grid

echo "\n";

?>


