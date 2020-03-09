<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d 				= $displayData;
$displayData 	= null;
$dParamAttr		= str_replace(array('[',']'), '', $d['param']);


?>
<div class="<?php echo $d['s']['c']['panel.panel-default'] ?> <?php echo $dParamAttr; ?>">
	<div class="<?php echo $d['s']['c']['panel-heading'] ?>" role="tab" id="heading<?php echo $dParamAttr; ?>">
		<h4 class="<?php echo $d['s']['c']['panel-title'] ?>">
			<a data-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo $dParamAttr; ?>" class="panel-collapse" aria-label="<?php echo JText::_('COM_PHOCACART_COLLAPSE') . ' ' . $d['title'] ?>"><span class="<?php echo $d['triangle_class'] ?>"></span></a>
			<a data-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo$dParamAttr; ?>" class="panel-collapse" aria-label="<?php echo $d['title'] ?>"><?php echo $d['title'] ?></a>
		</h4>
	</div>

	<div id="collapse<?php echo $dParamAttr; ?>" class="<?php echo $d['collapse_class'] ?>" role="tabpanel" aria-labelledby="heading<?php echo $dParamAttr; ?>">
		<div class="<?php echo $d['s']['c']['panel-body'] ?>"><div class="ph-filter-module-categories-tree">
			<?php echo $d['output'];?>
		</div></div>
	</div>
</div>
