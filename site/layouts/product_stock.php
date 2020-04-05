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
<div id="phItemStockBox<?php echo $d['typeview'] . (int)$d['product_id']; ?>">
	<div class="<?php echo $d['class']; ?>">
		<div class="ph-stock-txt"><?php echo JText::_('COM_PHOCACART_AVAILABILITY'); ?>:</div>
		<div class="ph-stock"><?php echo JText::_($d['stock_status_output']); ?></div>
	</div>
</div>
<div class="ph-cb"></div>
