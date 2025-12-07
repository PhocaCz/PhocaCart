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
$d['paramname'] = str_replace('_', '', $d['param']);
$jsSet = 'phChangeSearch(\'' . $d['param'] . '\', jQuery(\'#' . $d['id'] . ' input[name=&quot;' . $d['paramname'] . '&quot;]\').val(), 1, \'text\', 1);';
$jsClear = 'phClearField(\'#' . $d['id'] . $d['paramname'] . '\');phChangeSearch(\'' . $d['param'] . '\', \'\', 0, \'text\', 1);';
$displayData = null;
$checkedAll = '';
$checkedFilter = '';

$js = ' ' . "\n";
$js .= 'jQuery(document).ready(function(){' . "\n";
$js .= '   jQuery("#' . $d['id'] . $d['paramname'] . '").keyup(function(event){' . "\n";
$js .= '      if(event.keyCode == 13){' . "\n";
$js .= '         jQuery("#' . $d['id'] . $d['paramname'] . 'Btn' . '").click();' . "\n";
$js .= '      }' . "\n";
$js .= '   });' . "\n";
$js .= '});' . "\n";
$js .= ' ' . "\n";
$document = Factory::getDocument();
//$document->addScriptDeclaration($js);
$app = Factory::getApplication();
$wa  = $app->getDocument()->getWebAssetManager();
$wa->addInlineScript($js);

if (isset($d['activefilter']) && $d['activefilter']) {
    $checkedFilter = 'checked';
} else {
    $checkedAll = 'checked';
}

?>
<div class="input-group">

<?php if ($d['display_inner_icon'] == 1) { ?>

    <div class="inner-addon right-addon"><?php echo PhocacartRenderIcon::icon($d['s']['i']['search']) ?><input type="text" class="<?php echo $d['s']['c']['form-control'] ?>" name="<?php echo $d['paramname']; ?>" placeholder="<?php echo Text::_($d['placeholder_text']); ?>" value="<?php echo $d['getparams']; ?>" id="<?php echo $d['id'] . $d['paramname']; ?>" aria-label="<?php echo Text::_('COM_PHOCACART_SEARCH'); ?>" /></div>

<?php } else { ?>
    <input type="text" class="<?php echo $d['s']['c']['form-control'] ?> phSearchBoxSearchInput" name="<?php echo $d['paramname']; ?>" placeholder="<?php echo Text::_($d['placeholder_text']); ?>" value="<?php echo $d['getparams']; ?>" id="<?php echo $d['id'] . $d['paramname']; ?>" aria-label="<?php echo Text::_('COM_PHOCACART_SEARCH'); ?>" />

<?php } ?>

<?php if ($d['hide_buttons'] == 1) { ?>
    <div style="display:none">
<?php } ?>

        <?php /*<span class="input-group-btn"> */ ?>
        <button class="<?php echo $d['s']['c']['btn.btn-success'] ?> tip <?php echo $d['s']['c']['hastooltip'] ?>" type="button" onclick="<?php echo $jsSet; ?>" title="<?php echo Text::_('COM_PHOCACART_SEARCH'); ?>" aria-label="<?php echo Text::_('COM_PHOCACART_SEARCH'); ?>" id="<?php echo $d['id'] . $d['paramname'] . 'Btn'; ?>"><?php echo PhocacartRenderIcon::icon($d['s']['i']['search']) ?></button><button class="<?php echo $d['s']['c']['btn.btn-danger'] ?> tip <?php echo $d['s']['c']['hastooltip'] ?>" type="button" onclick="<?php echo $jsClear; ?>" title="<?php echo Text::_('COM_PHOCACART_CLEAR'); ?>" aria-label="<?php echo Text::_('COM_PHOCACART_CLEAR'); ?>"><?php echo PhocacartRenderIcon::icon($d['s']['i']['clear']) ?></button><?php /* </span> */ ?>

<?php if ($d['hide_buttons'] == 1) { ?>
    </div>
<?php } ?>

</div>


<?php if ($d['search_options'] == 1) { ?>
    <div class="radio ph-search-radio ph-radio-container"><label><input type="radio" name="phOptionSearchProducts" id="<?php echo $d['id']; ?>SearchAllProducts" class="phSearchBoxSearchAllProducts" value="phOptionSearchAllProducts" <?php echo $checkedAll; ?>><?php echo Text::_('COM_PHOCACART_SEARCH_ALL_PRODUCTS'); ?><span class="ph-radio-checkmark"></span></label>
    </div>

    <div class="radio ph-search-radio ph-radio-container"><label><input type="radio" name="phOptionSearchProducts" id="<?php echo $d['id']; ?>SearchFilteredProducts" class="phSearchBoxSearchFilteredProducts" value="phOptionSearchFilteredProducts" <?php echo $checkedFilter; ?>><?php echo Text::_('COM_PHOCACART_SEARCH_FILTERED_PRODUCTS'); ?><span class="ph-radio-checkmark"></span></label>
    </div>
<?php } ?>




