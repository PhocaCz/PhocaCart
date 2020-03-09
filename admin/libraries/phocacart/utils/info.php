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
class PhocacartUtilsInfo
{

    public static function getInfo($mode = 1) {

        JPluginHelper::importPlugin('phocatools');
        $results = \JFactory::getApplication()->triggerEvent('PhocatoolsOnDisplayInfo', array('NjI5NTcyNzcxMTc='));
        if (isset($results[0]) && $results[0] === true) {
            return '';
        }



        if ($mode === 0) {
            return "\n\n" . 'Powered by Phoca Cart' . "\n" . 'https://www.phoca.cz/phocacart';
        } else if ($mode === 2) {
            return '<div>Powered by <a href="https://www.phoca.cz/phocacart"><img src="'.JURI::root(true).'/media/com_phocacart/images/phoca-cart.png" alt="Phoca Cart" style="height:1.2em;width:auto;margin-bottom: 3px;" /></a> & <a href="https://www.phoca.cz/phocacart"><img src="'.JURI::root(true).'/media/com_phocacart/images/phoca-pos.png" alt="Phoca POS" style="height:1.2em;width:auto;margin-bottom: 3px;" /></a></div>';
        } else {
            return '<div style="text-align:right;display:block">Powered by <a href="https://www.phoca.cz/phocacart">Phoca Cart</a></div>';
        }
    }
}
?>
