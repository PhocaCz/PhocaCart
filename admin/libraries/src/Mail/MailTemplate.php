<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Mail;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailTemplate as JoomlaMailTemplate;
use Joomla\Filesystem\Path;

defined('_JEXEC') or die;

class MailTemplate extends JoomlaMailTemplate
{
    public function __construct(string $templateId, ?string $language = null, ?Mail $mailer = null)
    {

        if ($language === null) {
            $language = Factory::getApplication()->getLanguage()->getTag();
        }

        $this->loadMailLanguage($language);

        parent::__construct($templateId, $language, $mailer);
    }

    public function loadMailLanguage(string $lang): void
    {
        /* TODO bypass Joomla Issue https://github.com/joomla/joomla-cms/issues/39228 */
        $language = Factory::getApplication()->getLanguage();

        if ($lang !== $language->getTag()) {
            $language->load('com_phocacart', JPATH_ADMINISTRATOR, $lang, true);
            $language->load('com_phocacart', JPATH_SITE, $lang, true);
        }
    }

    protected function replaceTags($text, $tags, $isHtml = false)
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

        return parent::replaceTags($text, $tags, $isHtml);
    }

    public function addTemplateData($data, $plain = false)
    {
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            foreach ($data['attachments'] as $id => $file) {
                $this->addInlineImage($file, $id);
            }
        }

        parent::addTemplateData($data, $plain);
    }

    public function addInlineImage(?string $imageFile, string $name): void
    {
        if (!$imageFile) {
            return;
        }

        $imageFile = Path::check(JPATH_ROOT . '/' . HTMLHelper::_('cleanImageURL', $imageFile)->url);
        if (is_file(urldecode($imageFile))) {
            $this->mailer->addAttachment($imageFile, $name, 'base64', mime_content_type($imageFile), 'inline');

            $this->addLayoutTemplateData(['inline.' . $name => $name]);
        }
    }

    /**
     * Updates or creates if not exists mail template
     *
     * @param   string  $key
     * @param   string  $subject
     * @param   string  $body
     * @param   array   $tags
     * @param   string  $htmlbody
     *
     * @return bool
     *
     * @since 5.0.0
     */
    public static function checkTemplate(string $key, string $subject, string $body, array $tags, string $htmlbody = ''): bool
    {
        $template = self::getTemplate($key, '');

        if ($template)
            return self::updateTemplate($key, $subject, $body, $tags, $htmlbody);
        else
            return self::createTemplate($key, $subject, $body, $tags, $htmlbody);
    }

    /**
     * Insert a new mail template for specific language into the system
     *
     * @param   string  $key       Mail template key
     * @param   string  $language  Language code
     * @param   string  $subject   A default subject (normally a translatable string)
     * @param   string  $body      A default body (normally a translatable string)
     * @param   array   $tags      Associative array of tags to replace
     * @param   string  $htmlbody  A default htmlbody (normally a translatable string)
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   5.0.0
     */
    public static function createLanguageTemplate(string $key, string $language, string $subject, string $body, array $tags, string $htmlbody = ''): bool
    {
        $db = Factory::getDbo();

        $template              = new \stdClass();
        $template->template_id = $key;
        $template->language    = $language;
        $template->subject     = $subject;
        $template->body        = $body;
        $template->htmlbody    = $htmlbody;
        $template->extension   = explode('.', $key, 2)[0] ?? '';
        $template->attachments = '';
        $params                = new \stdClass();
        $params->tags          = $tags;
        $template->params      = json_encode($params);

        return $db->insertObject('#__mail_templates', $template);
    }
}
