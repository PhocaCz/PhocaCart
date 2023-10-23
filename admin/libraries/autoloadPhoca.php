<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

trigger_error(
  sprintf(
    'Bootstrapping PhocaCart using the %1$s file is deprecated.  Use %2$s instead.',
    __FILE__,
    __DIR__ . '/bootstrap.php'
  ),
  E_USER_DEPRECATED
);

require_once __DIR__ . '/bootstrap.php';
