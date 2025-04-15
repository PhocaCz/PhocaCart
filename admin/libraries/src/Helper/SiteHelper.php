<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Helper;

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

abstract class SiteHelper
{
    public static function getTemplate(): object
    {
        static $siteTemplate = null;

        if ($siteTemplate === null) {
            if (Factory::getApplication()->isClient('site')) {
                $template = Factory::getApplication()->getTemplate(true);
                $siteTemplate = (object) [
                    'template' => $template->template,
                    'parent'   => $template->parent,
                ];
            } else {
                $templates = Factory::getApplication()->bootComponent('templates')->getMVCFactory()
                    ->createModel('Style', 'Administrator')->getSiteTemplates();

                foreach ($templates as $template) {
                    if ($template->home == 1) {
                        $siteTemplate = (object) [
                            'template' => $template->template,
                            'parent'   => $template->parent,
                        ];

                        break;
                    }
                }
            }
        }

        return $siteTemplate;
    }
}
