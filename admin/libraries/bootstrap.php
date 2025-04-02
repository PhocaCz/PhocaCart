<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

JLoader::registerNamespace('\\Phoca\\PhocaCart', __DIR__ . '/src');
JLoader::registerNamespace('\\Phoca', __DIR__ . '/Phoca');
JLoader::registerNamespace('\\Mike42\\Escpos', __DIR__ . '/Escpos');
JLoader::register('Parsedown', __DIR__ . '/Parsedown/Parsedown.php');

// Legacy
JLoader::registerPrefix('Phocacart', __DIR__ . '/phocacart');
JLoader::register('PhocaCartRouterrules', __DIR__ . '/phocacart/path/routerrules.php');
require_once __DIR__ . '/classmap.php';
