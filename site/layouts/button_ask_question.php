<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d = $displayData;

if ($d['popup'] == 1) { ?>
<div class="pull-right ph-pull-right">
	<a href="<?php echo $d['link']; ?>" class="btn btn-default btn-small ph-btn" role="button" onclick="phWindowPopup(this.href, 'phWindowPopup', 2.5, 1.2);return false;"><span class="glyphicon glyphicon-question-sign"></span> <?php echo JText::_('COM_PHOCACART_ASK_A_QUESTION'); ?></a>
</div>
<?php } else { ?>
	
	<div class="pull-right ph-pull-right">
	<a href="<?php echo $d['link']; ?>" class="btn btn-default btn-small ph-btn" role="button"><span class="glyphicon glyphicon-question-sign"></span> <?php echo JText::_('COM_PHOCACART_ASK_A_QUESTION'); ?></a>
</div>
	
<?php }
?>
