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

if (!empty($d['compare'])) {
	foreach ($d['compare'] as $k => $v) {
		// Try to find the best menu link

		if (isset($v->catid2) && (int)$v->catid2 > 0 && isset($v->catalias2) && $v->catalias2 != '') {
			$linkProduct 	= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid2, $v->alias, $v->catalias2));
		} else {
			$linkProduct 	= JRoute::_(PhocacartRoute::getItemRoute($v->id, $v->catid, $v->alias, $v->catalias));
		}

?>
<div class="<?php echo $d['s']['c']['row'] ?>">
	<div class="<?php echo $d['s']['c']['col.xs12.sm8.md8'] ?>"><a href="<?php echo $linkProduct; ?>"><?php echo $v->title; ?></a></div>
	<div class="<?php echo $d['s']['c']['col.xs12.sm4.md4'] ?>">
		<form action="<?php echo $d['linkcomparison']; ?>" method="post" id="phCompareRemove<?php echo (int)$v->id; ?>">
			<input type="hidden" name="id" value="<?php echo (int)$v->id; ?>">
			<input type="hidden" name="task" value="comparison.remove">
			<input type="hidden" name="tmpl" value="component" />
			<input type="hidden" name="option" value="com_phocacart" />
			<input type="hidden" name="return" value="<?php echo $d['actionbase64']; ?>" />
			<div class="<?php echo $d['s']['c']['pull-right'] ?>">
			<?php if (isset($d['method']) && (int)$d['method'] > 0) { ?>
				<div class="ph-category-item-compare"><a href="javascript:void(0)" onclick="phItemRemoveCompareFormAjax('phCompareRemove<?php echo (int)$v->id; ?>');" title="<?php echo JText::_('COM_PHOCACART_REMOVE_FROM_COMPARISON_LIST'); ?>"><span class="<?php echo $d['s']['i']['remove'] ?>"></span></a></div>
			<?php } else { ?>
				<div class="ph-category-item-compare"><a href="javascript:void(0)" onclick="document.getElementById('phCompareRemove<?php echo (int)$v->id; ?>').submit();" title="<?php echo JText::_('COM_PHOCACART_REMOVE_FROM_COMPARISON_LIST'); ?>"><span class="<?php echo $d['s']['i']['remove'] ?>"></span></a></div>
			<?php } ?>
			</div>
		<?php echo Joomla\CMS\HTML\HTMLHelper::_('form.token'); ?>
		</form>
	</div>
</div>
<?php
	}
} else {
	echo '<div>'.JText::_('COM_PHOCACART_COMPARISON_LIST_IS_EMPTY').'</div>';
}
?>
<div class="ph-small ph-right ph-u ph-cart-link-compare"><a href="<?php echo $d['linkcomparison']; ?>"><?php echo JText::_('COM_PHOCACART_VIEW_COMPARISON_LIST'); ?></a></div>
