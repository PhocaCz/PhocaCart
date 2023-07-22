<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
$d = $displayData;
$pC = PhocacartUtils::getComponentParameters();
$p['paddle_vendor_id'] = $pC->get('paddle_vendor_id', '');
$p['paddle_sandbox'] = $pC->get('paddle_sandbox', 1);

if ($p['paddle_vendor_id'] != '') {
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

    $wa->registerAndUseScript('com_phocacart.paddle', 'https://cdn.paddle.com/paddle/paddle.js');

    $paddleSandbox = '';
    if ((int)$p['paddle_sandbox'] == 1) {
        $paddleSandbox = 'Paddle.Environment.set(\'sandbox\');';
    }

    $wa->addInlineScript('
        '.$paddleSandbox.'
        Paddle.Setup({ vendor: '.(int)$p['paddle_vendor_id'].' });
    ');
}

?>
<div class="<?php echo $d['s']['c']['pull-right'];?> ph-item-buy-now-box">
	<button type="submit" class="<?php echo $d['s']['c']['btn.btn-primary'];?> ph-btn paddle_button" data-theme="none" data-product="<?php echo $d['external_id']; ?>" data-theme="green"><?php echo PhocacartRenderIcon::icon($d['s']['i']['shopping-cart'], '', ' ') . Text::_('COM_PHOCACART_BUY_NOW'); ?></button>
</div>
<div class="ph-cb"></div>
