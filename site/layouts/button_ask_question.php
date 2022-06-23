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
$d = $displayData;

if ($d['popup'] == 2) { ?>

<div class="<?php echo $d['s']['c']['pull-right'];?>">
        <a href="<?php echo $d['link'] ?>" data-bs-target="#phAskAQuestionPopup" data-bs-toggle="modal" data-id="phAskAQuestionPopup" data-src="<?php echo $d['link'] ?>" class="<?php echo $d['s']['c']['btn.btn-default.btn-sm'];?> ph-btn phModalContainerButton" role="button" title="<?php echo Text::_('COM_PHOCACART_ASK_A_QUESTION'); ?>"  data-bs-toggle="tooltip" data-placement="top"><span class="<?php echo $d['s']['i']['question-sign'] ?>"></span> <?php echo Text::_('COM_PHOCACART_ASK_A_QUESTION'); ?></a>
</div>

<?php } else if ($d['popup'] == 1) { ?>

<div class="<?php echo $d['s']['c']['pull-right'];?>">
	<a href="<?php echo $d['link']; ?>" class="<?php echo $d['s']['c']['btn.btn-default.btn-sm'];?> ph-btn" role="button" onclick="phWindowPopup(this.href, 'phWindowPopup', 2.5, 1.2);return false;"><span class="<?php echo $d['s']['i']['question-sign'] ?>"></span> <?php echo Text::_('COM_PHOCACART_ASK_A_QUESTION'); ?></a>
</div>

<?php } else { ?>

<div class="<?php echo $d['s']['c']['pull-right'];?>">
	<a href="<?php echo $d['link']; ?>" class="<?php echo $d['s']['c']['btn.btn-default.btn-sm'];?> ph-btn" role="button"><span class="<?php echo $d['s']['i']['question-sign'] ?>"></span> <?php echo Text::_('COM_PHOCACART_ASK_A_QUESTION'); ?></a>
</div>
<?php }




