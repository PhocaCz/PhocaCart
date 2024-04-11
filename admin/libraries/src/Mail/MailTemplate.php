<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Mail;

use Joomla\CMS\Mail\MailTemplate as JoomlaMailTemplate;

defined('_JEXEC') or die;

class MailTemplate extends JoomlaMailTemplate
{

    protected function replaceTags($text, $tags)
    {
        foreach ($tags as $key => $value) {
            if (!$value) {
                $pregKey = preg_quote(strtoupper('IF ' . $key), '/');
                $text = preg_replace('/{' . $pregKey . '}(.*?){\/' . $pregKey . '}/s', '', $text);

                $text = str_replace('{IFNOT ' . strtoupper($key) . '}', '', $text);
                $text = str_replace('{/IFNOT ' . strtoupper($key) . '}', '', $text);
            } else {
                $text = str_replace('{IF ' . strtoupper($key) . '}', '', $text);
                $text = str_replace('{/IF ' . strtoupper($key) . '}', '', $text);

                $pregKey = preg_quote(strtoupper($key), '/');
                $text = preg_replace('/{IFNOT\s+' . $pregKey . '}(.*?){\/IFNOT\s+' . $pregKey . '}/s', '', $text);
            }
        }

        // B/C compatibility - tags in lower case
        $text = preg_replace_callback('/{(.*?)}/s', function($matches) {
           return strtoupper($matches[0]);
        }, $text);

        return parent::replaceTags($text, $tags);
    }
}
