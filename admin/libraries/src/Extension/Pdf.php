<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Phoca\PhocaCart\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die();

final class Pdf
{
    private static array $defaultOptions = [
        'option' => 'com_phocacart',
        'title' => '',
        'file' => '',
        'filename' => '',
        'subject' => '',
        'keywords' => '',
        'output' => '',
        'pdf_destination' => 'S',
    ];

    public static function load(): bool
    {
        static $loaded = null;

        if ($loaded === null) {
            $loaded = false;

            $component = ComponentHelper::getComponent('com_phocapdf', true)->enabled;
            $plugin    = PluginHelper::isEnabled('phocapdf', 'phocacart');
            $file      = \is_file(JPATH_ADMINISTRATOR . '/components/com_phocapdf/helpers/phocapdfrender.php');

            $loaded = $component && $plugin && $file;
            if ($loaded) {
                require_once(JPATH_ADMINISTRATOR . '/components/com_phocapdf/helpers/phocapdfrender.php');
            }
        }

        return $loaded;
    }

    private static function prepareData(array $data): array
    {
        $data = array_merge(self::$defaultOptions, $data);
        return $data;
    }

    public static function renderPdf(array $data): ?string
    {
        if (!self::load()) {
            return null;
        }

        return \PhocaPDFRender::renderPDF('', self::prepareData($data));
    }

    public static function initializePdf(object &$pdf, object &$content, object &$document, array $data): void
    {
        if (!self::load()) {
            return;
        }

        \PhocaPDFRender::initializePDF($pdf, $content, $document, self::prepareData($data));
    }

    public static function renderInitializedPdf(object &$pdf, object &$content, object &$document, array $data): void
    {
        if (!self::load()) {
            return;
        }

        \PhocaPDFRender::renderInitializedPDF($pdf, $content, $document, self::prepareData($data));
    }

}
