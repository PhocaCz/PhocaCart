<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Input;

use Joomla\CMS\Component\ComponentHelper;

defined('_JEXEC') or die;

class InputHelper
{
    public static function filterText($texts) {
        if (is_object($texts)) {
            $texts = (array)$texts;
        }

        if (is_array($texts)) {
            foreach ($texts as &$text) {
                $text = ComponentHelper::filterText($text);
            }
            return $texts;
        } else {
            return ComponentHelper::filterText($texts);
        }
    }
}
