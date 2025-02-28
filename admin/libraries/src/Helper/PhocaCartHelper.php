<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

abstract class PhocaCartHelper
{
    public static function params(): Registry
    {
        static $params = null;

        if ($params === null) {
            $params = ComponentHelper::getParams('com_phocacart');
        }

        return $params;
    }

    public static function param(string $path, $default = null)
    {
        return self::params()->get($path, $default);
    }
}
