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
$d 				= $displayData;
$displayData 	= null;
$dParamAttr		= str_replace(array('[',']'), '', $d['param']);

$title = isset($d['titleheader']) && $d['titleheader'] != '' ? $d['titleheader'] : $d['title'];
?>
<div class="<?php echo $d['s']['c']['panel.panel-default'] ?> panel-<?php echo $dParamAttr; ?>" <?php echo $d['s']['a']['accordion'] ?>>

    <?php if ($d['s']['c']['class-type'] != 'uikit') { ?>
        <div class="<?php echo $d['s']['c']['panel-heading'] ?>" role="tab" id="heading<?php echo $dParamAttr; ?>">
            <h4 class="<?php echo $d['s']['c']['panel-title'] ?>">
                <a data-bs-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo $dParamAttr; ?>" class="panel-collapse" aria-label="<?php echo Text::_('COM_PHOCACART_COLLAPSE') . ' ' . $title ?>"><?php echo PhocacartRenderIcon::icon($d['triangle_class']) ?></a>
                <a data-bs-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo$dParamAttr; ?>" class="panel-collapse" aria-label="<?php echo $title ?>"><?php echo $title ?></a>
            </h4>
        </div>
    <?php } ?>

	<div id="collapse<?php echo $dParamAttr; ?>" class="<?php echo $d['collapse_class'] ?>" role="tabpanel" aria-labelledby="heading<?php echo $dParamAttr; ?>">

        <?php if ($d['s']['c']['class-type'] == 'uikit') { ?>
            <a href="#" class="<?php echo $d['s']['c']['panel-title'] ?>"><?php echo $title ?></a>
        <?php } ?>

        <div class="<?php echo $d['s']['c']['panel-body'] ?>"><div class="ph-filter-module-categories-tree">
			<?php echo $d['output'];?>
		</div></div>
	</div>
</div>
