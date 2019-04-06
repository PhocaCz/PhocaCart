<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
class PhocacartLanguage
{

    private $primeLangTag   = '';
    private $primeLangPaths = array();
    /**
     * Set new language and return the default so it can be set back
     * @param $lang
     */
    public function __construct(){
        $language               = JFactory::getLanguage();
        $this->primeLangTag     = $language->getTag();
        $this->primeLangPaths   = $language->getPaths();
    }


    public function setLanguage($lang) {

        $app                    = JFactory::getApplication();
        $language               = JFactory::getLanguage();

        if ($lang != '' && $lang != '*') {
            $newLang = JLanguage::getInstance($lang);
            JFactory::$language = $newLang;
            $app->loadLanguage($newLang);

            if (!empty($this->primeLangPaths)) {
                foreach($this->primeLangPaths as $k => $v) {
                    $newLang->load($k);
                }
            }

        }
	}

	public function setLanguageBack() {

        $app                    = JFactory::getApplication();
        $language               = JFactory::getLanguage();

        $newLang = JLanguage::getInstance($this->primeLangTag);
        JFactory::$language = $newLang;
        $app->loadLanguage($newLang);

        if (!empty($this->primeLangPaths)) {
            foreach($this->primeLangPaths as $k => $v) {
                $newLang->load($k);
            }
        }
    }

    public function getDefaultLanguage($type = 1) {

        $params = JComponentHelper::getParams('com_languages');

        if ($type == 1) {
            return $params->get('site');
        } else {
            return $params->get('administrator');
        }
    }


    /* Static Part */
    /* $titleSuffix = array(
        0 => array('title', 'separator', 'starttag', 'endtag));
    */

    public static function renderTitle($title = '', $titleLang = '', $titleSuffix = array(), $type = 'order') {


        $paramsC 					    = PhocacartUtils::getComponentParameters();
        $order_language_variables		= $paramsC->get( 'order_language_variables', 0 );

        // No language variables (no translation) for items in order (total items like shipping or payment titles)
        if ($type == 'order' && $order_language_variables == 0) {
            return $title;;
        }

        $o = '';
        if ($titleLang != '') {
            $o .= JText::_($titleLang);

            if (!empty($titleSuffix)) {
                foreach ($titleSuffix as $k => $v) {

                    $title      = isset($v[0]) && $v[0] != '' ? $v[0] : '';
                    $separator  = isset($v[1]) && $v[1] != '' ? $v[1] : '';
                    $startTag   = isset($v[2]) && $v[2] != '' ? $v[2] : '';
                    $endTag     = isset($v[3]) && $v[3] != '' ? $v[3] : '';

                    if ($startTag . JText::_($title) . $endTag != '') {
                        $o .= $separator . $startTag . JText::_($title) . $endTag;
                    }
                }
            }

        } else {
            // No title raw, render standard title (such includes suffix)
            $o = $title;
        }

        return $o;

    }
}
?>
