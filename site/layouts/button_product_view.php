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
?>
<div class="pull-left">
<?php
if ($d['display_view_product_button'] == 1) {
	
	?><a href="<?php echo $d['link']; ?>" class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-search"></span> <?php echo JText::_('COM_PHOCACART_VIEW_PRODUCT'); ?></a><?php

} else if ($d['display_view_product_button'] == 2) {
	
	?><a href="<?php echo $d['link']; ?>" class="btn btn-primary btn-sm ph-btn" role="button" title="<?php echo JText::_('COM_PHOCACART_VIEW_PRODUCT'); ?>"><span class="glyphicon glyphicon-search"></span></a><?php

} ?>
</div>