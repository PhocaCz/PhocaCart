<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Utils;

defined('_JEXEC') or die;

class TextUtils
{
    public static function separetedText(array $texts, string $prefix = '', string $suffix = '', string $separator = ' '): string
    {
        $texts = array_filter($texts, function ($value) {
            return !empty($value);
        });

        if ($texts) {
            return $prefix . implode($separator, $texts) . $suffix;
        }

        return '';
    }
}
