<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Layout;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Phoca\PhocaCart\Helper\SiteHelper;

defined('_JEXEC') or die;

final class SiteLayout extends FileLayout
{
    public function getDefaultIncludePaths()
    {
        if (Factory::getApplication()->isClient('site')) {
            return parent::getDefaultIncludePaths();
        }

        $template = SiteHelper::getTemplate();

        $paths = [];
        $paths[] = JPATH_SITE . '/templates/' . $template->template . '/html/layouts/com_phocacart';
        $paths[] = JPATH_SITE . '/templates/' . $template->template . '/html/layouts';
        if ($template->parent) {
            $paths[] = JPATH_SITE . '/templates/' . $template->parent . '/html/layouts/com_phocacart';
            $paths[] = JPATH_SITE . '/templates/' . $template->parent . '/html/layouts';
        }
        $paths[] = JPATH_SITE . '/components/com_phocacart/layouts';

        return $paths;
    }
}
