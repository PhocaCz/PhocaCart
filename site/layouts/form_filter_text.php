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
if (isset($d['param2']) && $d['param2'] != '') {
	$d['param2name']	= str_replace('-', '', $d['param2']);
}

if (isset($d['param2']) && $d['param2'] != '') {
	// We have second parameter, so in first we define that the javascript should wait with re-direct
	$jsSet	= 'phChangeFilter(\''.$d['param'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['paramname'].'&quot;]\').val(), 1, \'text\', 1, 1);';
	$jsSet	.= 'phChangeFilter(\''.$d['param2'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['param2name'].'&quot;]\').val(), 1, \'text\', 1);';
	$jsClear		 = 'phClearField(\'#'.$d['id'].$d['paramname'].'\');';
	$jsClear		.= 'phClearField(\'#'.$d['id'].$d['param2name'].'\');';
	$jsClear		.= 'phChangeFilter(\''.$d['param'].'\', \'\', 0, \'text\', 1, 1);';
	$jsClear		.= 'phChangeFilter(\''.$d['param2'].'\', \'\', 0, \'text\', 1);';
} else {
	// We have only one parameter so we don't need define wait and the site is reloaded immediately
	$jsSet	= 'phChangeFilter(\''.$d['param'].'\', jQuery(\'#'. $d['id'].' input[name=&quot;'.$d['paramname'].'&quot;]\').val(), 1, \'text\', 1);';
	$jsClear= 'phChangeFilter(\''.$d['param'].'\', \'\', 0, \'text\', 1);';
}

$displayData 	= null;
?>
<div class="panel panel-default">
	<div class="panel-heading" role="tab" id="heading<?php echo $d['param']; ?>">
		<h4 class="panel-title">
			<a data-toggle="collapse" href="#collapse<?php echo $d['param']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $d['param']; ?>" class="panel-collapse"><span class="glyphicon glyphicon-triangle-bottom"></span></a> 
			<a data-toggle="collapse" href="#collapse<?php echo $d['param']; ?>" aria-expanded="true" aria-controls="collapse<?php echo $d['param']; ?>" class="panel-collapse"><?php echo $d['title'] ?></a>
		</h4>
	</div>
			
	<div id="collapse<?php echo $d['param']; ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading<?php echo $d['param']; ?>">
		<div class="panel-body form-horizontal" id="<?php echo $d['id']; ?>">
		
			<div class="form-group">
				<label class="col-sm-5" for="<?php echo $d['paramname']; ?>"><?php echo $d['title1']; ?></label>
				<div class="col-sm-7"><input type="text" class="form-control" name="<?php echo $d['paramname']; ?>" value="<?php echo $d['getparams'][0]; ?>" id="<?php echo $d['id'].$d['paramname']; ?>" /></div>
			</div>
			<?php
			if (isset($d['param2']) && $d['param2'] != '') { ?>
				<div class="form-group">
					<label class="col-sm-5" for="<?php echo $d['param2name']; ?>"><?php echo $d['title2']; ?></label>
					<div class="col-sm-7"><input type="text" class="form-control" name="<?php echo $d['param2name']; ?>" value="<?php echo $d['getparams2'][0]; ?>" id="<?php echo $d['id'].$d['param2name']; ?>" /></div>
				</div>
			<?php } ?>
			
			
			<div class="col-sm-5"></div>
			<div class="col-sm-7">
				<div class="pull-right btn-group ph-zero ph-right-zero">
					<button class="btn btn-success tip hasTooltip" type="button" onclick="<?php echo $jsSet; ?>" title="<?php echo $d['titleset']; ?>"><span class="glyphicon glyphicon-ok"></span></button>
					<button class="btn  btn-danger tip hasTooltip pull-right" type="button" onclick="<?php echo $jsClear; ?>" title="<?php echo $d['titleclear']; ?>"><span class="glyphicon glyphicon-remove"></span></button> 
				</div>
			</div>
			<?php
			/*
			
			<button class="btn tip hasTooltip" type="button" onclick="phChangeFilter('.$d['param'].', \''. $value.'\', 0, \'text\');" title="<?php echo JText::_('COM_PHOCACART_CLEAR_PRICE'); ?>"><span class="icon-remove"></span></button>
			
			foreach ($d['items'] as $k => $v) {
				
				$checked 	= '';
				$value 		= (int)$v->id .'-'. htmlspecialchars($v->alias);
				
				if (in_array($value, $d['getparams'])) {
					$checked 	= 'checked';
				}
				
				echo '<div class="checkbox">';
				echo '<label><input type="checkbox" name="tag" value="'.$value.'" '.$checked.' onchange="phChangeFilter(this, \''.$d['param'].'\', \''. $value.'\');" />'.$v->title.'</label>';
				echo '</div>';
				
			}*/
		?>
		</div>
	</div>
</div>