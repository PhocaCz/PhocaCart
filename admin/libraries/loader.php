<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

if (class_exists('\\n3tDebug')) {
    \n3tDebug::callStack('Legacy PhocaCart loader');
}

require_once __DIR__ . '/bootstrap.php';

if (!class_exists('\\PhocacartLoader')) {
  class PhocacartLoader extends JLoader {}
}

if (!function_exists('\\phocacartimport')) {
  function phocacartimport($path) {}
}
