<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\I18n;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;

abstract class I18nHelper
{
    public static function getI18nLanguages(): array
    {
        return LanguageHelper::getContentLanguages([0, 1], true, 'lang_code', 'ordering', 'asc');
    }

    public static function getDefLanguage(): string
    {
        static $defLanguage = null;

        if ($defLanguage === null) {
            $params = ComponentHelper::getParams('com_phocacart');
            $defLanguage = $params->get('i18n_language', ComponentHelper::getParams('com_languages')->get('site', 'en-GB'));
        }

        return $defLanguage;
    }
}
