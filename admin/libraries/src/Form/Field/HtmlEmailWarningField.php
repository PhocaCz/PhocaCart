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
use Joomla\CMS\Form\Field\NoteField;

/**
 * Displays warning, when Joomla HTML mail templates are not enabled
 *
 * @since  5.0
 */
class HtmlEmailWarningField extends NoteField
{
    /**
     * @since   5.0
     * @inheritdoc
     */
    protected $type = 'HtmlEmailWarning';

    private bool $htmlEmailEnabled = false;

    /**
     * @since 5.0.0
     * @inheritdoc
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $mailParams = ComponentHelper::getParams('com_mails');
        $this->htmlEmailEnabled = in_array($mailParams->get('mail_style', 'plaintext'), ['html', 'both']);

        if (!$element['description'] ?? '') {
            $element['description'] = 'COM_PHOCACART_WARNING_HTML_EMAILS_NOT_ENABLED';
        }

        if (!$element['class'] ?? '') {
            $element['class'] = 'alert alert-warning w-100 m-0';
        }

        return parent::setup($element, $value, $group);
    }

    protected function getLabel()
    {
        if ($this->htmlEmailEnabled) {
            return '';
        }

        return parent::getLabel();
    }
}
