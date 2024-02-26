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

/*

$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));
$layoutAl->render(array('type' => 'success', 'text' => Text::_('COM_PHOCACART'), 'close' => 1));
*/

$s = PhocacartRenderStyle::getStyles();

$d 				= $displayData;
$displayData 	= null;

if (isset($d['pos']) && $d['pos'] == 1) {
    $s['c']['class-type'] == 'bs5';

}

$closeStart = '';
$closeEnd   = '';
if (isset($d['close']) && $d['close'] == 1) {
    if ($s['c']['class-type'] != 'uikit') {
        $closeStart = '';
        $closeEnd = '<button type="button" class="'.$s['c']['alert-close'].'" aria-label="'.Text::_('COM_PHOCACART_CLOSE').'" '.$s['a']['alert-close'].'></button>';
    } else {
        $closeStart = '<a class="'.$s['c']['alert-close'].'" aria-label="'.Text::_('COM_PHOCACART_CLOSE').'" '.$s['a']['alert-close'].'></a>';
        $closeEnd = '';
    }
}

$class = '';
if (isset($d['class']) && $d['class'] != '') {
    $class = $d['class'];
}

if ($d['type'] == 'success') {
    echo '<div class="'.$s['c']['alert-success'].' '. $class.'" '.$s['a']['alert'].'>'. $closeStart . $d['text']. $closeEnd .'</div>';
} else if ($d['type'] == 'error') {
    echo '<div class="'.$s['c']['alert-danger'].' '. $class.'" '.$s['a']['alert'].'>'. $closeStart . $d['text']. $closeEnd .'</div>';
} else if ($d['type'] == 'warning') {
    echo '<div class="'.$s['c']['alert-warning'].' '. $class.'" '.$s['a']['alert'].'>'. $closeStart . $d['text']. $closeEnd .'</div>';
} else if ($d['type'] == 'info') {
    echo '<div class="'.$s['c']['alert-info'].' '. $class.'" '.$s['a']['alert'].'>'. $closeStart . $d['text']. $closeEnd .'</div>';
}
