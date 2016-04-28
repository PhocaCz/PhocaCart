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
$d['paramname']	= str_replace('_', '', $d['param']);
$jsSet			= 'phChangeSearch(\''.$d['param'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['paramname'].'&quot;]\').val(), 1, \'text\', 1);';
$jsClear		= 'phChangeSearch(\''.$d['param'].'\', \'\', 0, \'text\', 1);';
$displayData 	= null;
$checkedAll 	= '';
$checkedFilter	= '';
if (isset($d['activefilter']) && $d['activefilter']) {
	$checkedFilter = 'checked';
} else {
	$checkedAll = 'checked';
}

?>
<div class="row">
  <div class="col-lg-12">
	
	<div class="input-group" id="<?php echo $d['id']; ?>">
      <input type="text" class="form-control" name="<?php echo $d['paramname']; ?>"  placeholder="<?php echo JText::_('COM_PHOCACART_SEARCH_FOR'); ?>" value="<?php echo $d['getparams']; ?>">
      <span class="input-group-btn">
        <button class="btn btn-default btn-success tip hasTooltip" type="button" onclick="<?php echo $jsSet; ?>" title="<?php echo JText::_('COM_PHOCACART_SEARCH'); ?>" ><span class="glyphicon glyphicon-search"></span></button>
		<button class="btn btn-default btn-danger tip hasTooltip" type="button" onclick="<?php echo $jsClear; ?>" title="<?php echo JText::_('COM_PHOCACART_CLEAR'); ?>" ><span class="glyphicon glyphicon-remove" ></span></button>
      </span>
    </div>
	
<?php if ($d['searchoptions'] == 1) { ?>	
	<div class="radio ph-search-radio"><label><input type="radio" name="phOptionSearchProducts" id="<?php echo $d['id']; ?>SearchAllProducts" value="phOptionSearchAllProducts" <?php echo $checkedAll; ?>><?php echo JText::_('COM_PHOCACART_SEARCH_ALL_PRODUCTS'); ?></label>
	</div>
	
	<div class="radio ph-search-radio"><label><input type="radio" name="phOptionSearchProducts" id="<?php echo $d['id']; ?>SearchFilteredProducts" value="phOptionSearchFilteredProducts" <?php echo $checkedFilter; ?>><?php echo JText::_('COM_PHOCACART_SEARCH_FILTERED_PRODUCTS'); ?></label>
	</div>
<?php } ?>
	
  </div>
</div>
  

