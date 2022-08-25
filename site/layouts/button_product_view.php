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
?>
<div class="<?php echo $d['s']['c']['pull-left']; ?> ph-button-view-product-box">
<?php
if ($d['display_view_product_button'] == 1) {

	?><a href="<?php echo $d['link']; ?>" class="<?php echo $d['s']['c']['btn.btn-primary.btn-sm']; ?> ph-btn" role="button"><?php

	echo PhocacartRenderIcon::icon($d['s']['i']['view-product'], '', ' ');
	echo Text::_('COM_PHOCACART_VIEW_PRODUCT').'</a>';

} else if ($d['display_view_product_button'] == 2) {

	?><a href="<?php echo $d['link']; ?>" class="<?php echo $d['s']['c']['btn.btn-primary.btn-sm']; ?> ph-btn" role="button" title="<?php echo Text::_('COM_PHOCACART_VIEW_PRODUCT'); ?>"><?php
	echo PhocacartRenderIcon::icon($d['s']['i']['view-product'], '', ' ').'</a>';
} ?>
</div>

<?php




