<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Html\Grid;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

abstract class HtmlGridHelper
{
    private static function jsCommon(): void
    {
        Text::script('COM_PHOCACART_AJAX_CLOSE');
        Text::script('COM_PHOCACART_AJAX_ERROR');
    }

    public static function featuredIcon(int $value, bool $enabled = true, string $controller = 'phocacartitems'): string
    {
        switch ($controller) {
            case 'phocacartcategories':
                $activeTextSuffix = '_CATEGORY';
                $inactiveTextSuffix = '_CATEGORY';
                break;
            case 'phocacartmanufacturers':
                $activeTextSuffix = '_MANUFACTURER';
                $inactiveTextSuffix = '_MANUFACTURER';
                break;
            case 'phocacartitems':
            default:
                $activeTextSuffix = '';
                $inactiveTextSuffix = '_PRODUCT';
                break;
        }
        $states = [
            1  => ['COM_PHOCACART_TOGGLE_TO_UNFEATURE' . $activeTextSuffix, 'COM_PHOCACART_FEATURED' . $inactiveTextSuffix, 'star featured'],
            0  => ['COM_PHOCACART_TOGGLE_TO_FEATURE' . $activeTextSuffix, 'COM_PHOCACART_UNFEATURED' . $inactiveTextSuffix, 'circle'],
        ];

        $state = ArrayHelper::getValue($states, $value, $states[0]);
        $title  = $enabled ? $state[0] : $state[1];
        $ariaid = uniqid('phfeatured');

        $html = [];

        $html[] = '<span class="tbody-icon jgrid"';
        $html[] = ' aria-labelledby="' . $ariaid . '"';
        $html[] = '>';
        $html[] = LayoutHelper::render('joomla.icon.iconclass', ['icon' => $state[2]]);
        $html[] = '</span>';
        $html[] = '<div role="tooltip" id="' . $ariaid . '">' . Text::_($title) . '</div>';

        return implode('', $html);
    }

    public static function featuredButton(string $controller, int $id, int $value, bool $enabled = true): string
    {
        if (!$enabled) {
            return self::featuredIcon($value, $enabled, $controller);
        }

        self::jsCommon();

        if ($value === 1) {
            $newState = 0;
        } else {
            $newState = 1;
        }

        return '<a href="' . Route::_('index.php?option=com_phocacart&task=' . $controller . '.featured&format=json&id=' . $id). '" data-phajax="state=' . $newState . '">'
            . self::featuredIcon($value, $enabled, $controller)
            . '</a>';
    }

    public static function stateIcon(int $value, bool $enabled = true): string
    {
        $states = [
            1  => ['JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', 'publish'],
            0  => ['JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', 'unpublish'],
            2  => ['JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', 'archive'],
            -2 => ['JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', 'trash'],
        ];

        $state = ArrayHelper::getValue($states, $value, $states[0]);
        $title  = $enabled ? $state[0] : $state[1];
        $ariaid = uniqid('phstate');

        $html = [];

        $html[] = '<span class="tbody-icon jgrid"';
        $html[] = ' aria-labelledby="' . $ariaid . '"';
        $html[] = '>';
        $html[] = LayoutHelper::render('joomla.icon.iconclass', ['icon' => $state[2]]);
        $html[] = '</span>';
        $html[] = '<div role="tooltip" id="' . $ariaid . '">' . Text::_($title) . '</div>';

        return implode('', $html);
    }

    public static function stateButton(string $controller, int $id, int $value, bool $enabled = true): string
    {
        if (!$enabled) {
            return self::stateIcon($value, $enabled);
        }

        self::jsCommon();

        if (in_array($value, [1, -2])) {
            $newState = 0;
        } else {
            $newState = 1;
        }

        return '<a href="' . Route::_('index.php?option=com_phocacart&task=' . $controller . '.state&format=json&id=' . $id). '" data-phajax="state=' . $newState . '">'
            . self::stateIcon($value, $enabled)
            . '</a>';
    }
}
