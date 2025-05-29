<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Filesystem;

use Joomla\Filesystem\Path;

defined('_JEXEC') or die;

abstract class File extends \Joomla\Filesystem\File
{
    public static function exists($file)
    {
        return is_file(Path::clean($file));
    }
}
