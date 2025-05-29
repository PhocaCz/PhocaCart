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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Phoca\PhocaCart\Filesystem\File;
use Joomla\CMS\Uri\Uri;

use Joomla\CMS\Session\Session;
use Joomla\CMS\Version;
use Phoca\Render\Adminviews;
use Joomla\CMS\Factory;

class PhocacartRenderAdminviews extends Adminviews
{
    public $view        = '';
    public $viewtype    = 1;
    public $option      = '';
    public $optionLang  = '';
    public $tmpl        = '';
    public $sidebar     = true;
    protected $document	= false;

    public function __construct() {

        $version = new Version();
        $is42    = $version->isCompatible('4.2.0-beta');

        if ($is42) {
            $this->document = Factory::getDocument();
            $wa             = $this->document->getWebAssetManager();
            $wa->useScript('table.columns')->useScript('multiselect');
        }

        parent::__construct();
	}
}
?>
