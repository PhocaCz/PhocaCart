<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\Form\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Phoca\PhocaCart\Mail\MailHelper;

/**
 * Displays buttons to edit Mail template
 *
 * @since  5.0.0
 */
class EmailEditButtonsField extends FormField
{
    /**
     * @since   5.0.0
     * @inheritdoc
     */
    protected $type = 'EmailEditButtons';

    private array $languagesEnabled = [];

    private ?int $statusId = null;
    private array $templates = ['status', 'notification'];

    /**
     * @since 5.0.0
     * @inheritdoc
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (!$element['label'] ?? '') {
            $element['label'] = 'COM_PHOCACART_ORDER_STATUS_EDIT_EMAILS_LABEL';
        }

        if (!$element['description'] ?? '') {
            $element['description'] = 'COM_PHOCACART_ORDER_STATUS_EDIT_EMAILS_DESC';
        }

        $result = parent::setup($element, $value, $group);

        $this->statusId = $this->form->getValue('id');

        $this->languagesEnabled = LanguageHelper::getContentLanguages([0, 1]);

        if ($element['templates'] ?? '') {
            $this->templates = explode(',', $element['templates']);
        }

        $this->default = isset($element['value']) ? (string) $element['value'] : $this->default;

        return $result;
    }

    protected function getInput()
    {
        if (!$this->statusId) {
            return '<div class="alert alert-info w-100 m-0">' . Text::_('COM_PHOCACART_SAVE_STATUS_FIRST') . '</div>';
        }

        if (!$this->languagesEnabled) {
            return '<div class="alert alert-info w-100 m-0">' . Text::_('COM_PHOCACART_NO_CONTENT_LANGUAGES') . '</div>';
        }

        $html = [];

        foreach ($this->languagesEnabled as $language) {
            $html[] = '<div class="btn-group" role="group">';
            if (count($this->languagesEnabled) > 1) {
                $html[] = '<span class="input-group-text bg-light text-dark border-primary-subtle">';
                if ($language->image) {
                    $html[] = HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, ['title' => $language->title_native], true);
                } else {
                    $html[] = $language->lang_code;
                }
                $html[] = '</span>';
            }

            if (false !== array_search('status', $this->templates)) {
                $link = 'index.php?option=com_phocacart&task=phocacartstatus.mailtemplate&template_id=status&id=' . $this->statusId . '&language=' . $language->lang_code;
                $html[] = '<a href="' . Route::_($link) . '" target="_blank" class="btn btn-outline-primary">' . Text::_('COM_PHOCACART_EMAIL_ORDER_STATUS_CUSTOMER') . '</a>';
            }

            if (false !== array_search('notification', $this->templates)) {
                $link = 'index.php?option=com_phocacart&task=phocacartstatus.mailtemplate&template_id=notification&id=' . $this->statusId . '&language=' . $language->lang_code;
                $html[] = '<a href="' . Route::_($link) . '" target="_blank" class="btn btn-outline-primary">' . Text::_('COM_PHOCACART_EMAIL_ORDER_STATUS_NOTIFICATION') . '</a>';
            }

            if (false !== array_search('gift', $this->templates)) {
                $link = 'index.php?option=com_phocacart&task=phocacartstatus.mailtemplate&template_id=gift&id=' . $this->statusId . '&language=' . $language->lang_code;
                $html[] = '<a href="' . Route::_($link) . '" target="_blank" class="btn btn-outline-primary">' . Text::_('COM_PHOCACART_EMAIL_ORDER_STATUS_GIFT') . '</a>';
            }

            if (false !== array_search('gift_notification', $this->templates)) {
                $link = 'index.php?option=com_phocacart&task=phocacartstatus.mailtemplate&template_id=gift_notification&id=' . $this->statusId . '&language=' . $language->lang_code;
                $html[] = '<a href="' . Route::_($link) . '" target="_blank" class="btn btn-outline-primary">' . Text::_('COM_PHOCACART_EMAIL_ORDER_STATUS_GIFT_NOTIFICATION') . '</a>';
            }

            $html[] = '</div><br>';
        }

        return implode('', $html);
    }
}
