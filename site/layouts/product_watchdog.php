<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\Tools\WatchDog;

/** @var array $displayData */
/** @var object $product */

extract($displayData);

$params = PhocacartUtils::getComponentParameters();
$s      = PhocacartRenderStyle::getStyles();

if ($params->get('watchdog_enable', 0)) {
    if (WatchDog::has($product->id)) {
?>
      <div class="ph-watchdog-info <?php echo $s['c']['alert-info'] ?>">
          <?php echo Text::_('COM_PHOCACART_WATCHDOG_IS_SET'); ?>
      </div>
<?php
    } else {
        $return = base64_encode(Uri::getInstance()->toString());
        $link = Route::_('index.php?option=com_phocacart&task=wishlist.setwatchdog&id=' . $product->id . '&catid=' . $product->catid . '&return=' . $return);
?>
      <div class="ph-watchdog">
        <a href="<?php echo $link ?>"><?php echo PhocacartRenderIcon::icon($s['i']['watchdog']); ?></a> <a href="<?php echo $link ?>"><?php echo Text::_('COM_PHOCACART_SET_WATCHDOG'); ?></a>
      </div>
      <div class="ph-watchdog-info <?php echo $s['c']['alert-info'] ?>">
          <?php echo Text::_('COM_PHOCACART_WATCHDOG_SET_DESC'); ?>
      </div>
<?php
    }
}
