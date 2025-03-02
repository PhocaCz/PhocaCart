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

    public static function htmlToPlainText(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $text = $html;

        /* TODO some better parser */
        $text = str_replace('</p>', "\n\n", $text);
        $text = str_replace('<br>', "\n", $text);
        $text = str_replace('<br/>', "\n", $text);
        $text = str_replace('<br />', "\n", $text);

        $text = strip_tags($text);

        return $text;
    }

    public static function underline(string $text, string $underlineChar = '-'): string
    {
        return $text . "\n" . str_repeat($underlineChar, strlen($text));
    }
}
