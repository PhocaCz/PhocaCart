<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
$d 				    = $displayData;
$filterItems        = 0;
$clearFilterLink    = Route::_(PhocacartRoute::getItemsRoute());

$price  = new PhocacartPrice();
$price->setPrefix('');
$price->setSuffix('');

if (!empty($d['f'])) {
    foreach ($d['f'] as $k => $v) {

        if ($k == 'price') {

            if ((isset($v['from']) && $v['from'] !== '') || (isset($v['to']) && $v['to'] !== '')) {


                $priceFrom  = $v['from'] !== '' ? $price->getPriceFormat($v['from']) : '';
                $priceTo    = $v['to'] !== '' ? $price->getPriceFormat($v['to']) : '';

                $title = Text::_('COM_PHOCACART_PRICE') . ': ' . $priceFrom . ' - ' . $priceTo;
                $title = trim($title);

                $onclick = ' onclick="event.preventDefault(); phClearField(\'#phPriceFromTopricefrom\'); phClearField(\'#phPriceFromTopriceto\'); phChangeFilter(\'price_from\', \'\', 0, \'text\',1, 1, 2); phChangeFilter(\'price_to\', \'\', 0, \'text\',1, 0, 2);"';

                echo '<span class="' . $d['s']['c']['label.label-info'] . ' ph-label-close">';

                echo '<a class="btn ph-label-close-title" href="#"'.$onclick.'>' . $title .'</a>';
                echo '<a class="btn ph-label-close-icon" href="#"'.$onclick.'>' . PhocacartRenderIcon::icon($d['s']['i']['remove-circle'] . ' ph-label-close-remove', '', '', '') .'</a>';

                echo '</span>';

                $filterItems = 1;
            }

        } else {

            if (!empty($v)) {

                foreach ($v as $k2 => $v2) {

                    if (isset($v2['parameteralias']) && isset($v2['alias']) && isset($v2['title']) && $v2['parameteralias'] != '' && $v2['alias'] != '' && $v2['title'] != '') {
                        $title = $v2['title'];
                        if (isset($v2['parametertitle']) && $v2['parametertitle'] != '') {
                            switch ($v2['parametertitle']) {
                                case 'category':
                                    $titlePrefix = Text::_('COM_PHOCACART_CATEGORY');
                                break;
                                case 'tag':
                                    $titlePrefix = Text::_('COM_PHOCACART_TAG');
                                break;
                                case 'label':
                                    $titlePrefix = Text::_('COM_PHOCACART_LABEL');
                                break;
                                case 'manufacturer':
                                    $titlePrefix = Text::_('COM_PHOCACART_MANUFACTURER');

                                break;
                                default:
                                    $titlePrefix = $v2['parametertitle'];

                                break;

                            }
                            $title = $titlePrefix . ': ' . $title;
                            $title = trim($title);

                        }

                        // Specifications e.g.
                        $alias = $v2['alias'];
                        if (isset($v2['alias_value']) && $v2['alias_value'] != '') {
                            $alias = $v2['alias_value'];
                        }

                        $onclick = ' onclick="event.preventDefault(); phChangeFilter(\'' . $v2['parameteralias'] . '\', \'' . $alias . '\', this, \'checked\',0, 0, 2);"';

                        echo '<span class="' . $d['s']['c']['label.label-info'] . ' ph-label-close">';
                        echo '<a class="btn ph-label-close-title" href="#"'.$onclick.'>' . $title .'</a>';
                        echo '<a class="btn ph-label-close-icon" href="#"'.$onclick.'>' . PhocacartRenderIcon::icon($d['s']['i']['remove-circle'] . ' ph-label-close-remove', '', '', '') .'</a>';
                        echo '</span>';

                        $filterItems = 1;
                    }
                }
            }
        }
    }
}

if ($filterItems == 1) {

    $onclick = ' onclick="startFullOverlay(1)"';

    echo '<span class="' . $d['s']['c']['label.label-danger'] . ' ph-label-close">';
    echo '<a class="btn ph-label-close-title" href="' . Route::_($clearFilterLink) . '"'.$onclick.'>' . Text::_('COM_PHOCACART_CLEAR_ALL').'</a>';
    echo '<a class="btn ph-label-close-icon" href="' . Route::_($clearFilterLink) . '"'.$onclick.'>' . PhocacartRenderIcon::icon($d['s']['i']['remove-circle'] . ' ph-label-close-remove', '', '', '').'</a>';
    echo '</span>';

}
