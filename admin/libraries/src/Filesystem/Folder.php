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

abstract class Folder extends \Joomla\Filesystem\Folder
{
    public static function exists(string $folderName): bool
    {
        return is_dir(Path::clean($folderName));
    }
}
