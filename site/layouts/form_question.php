<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d 	= $displayData;
// NOT USED
$d['paramname'] = $d['title1'] = $d['param2name'] = $d['getparams'][0] = $jsSet = $jsClear = $d['titleclear'] = $d['titleset'] = 'xxx';

?>
<div class="form-group">
	<label class="col-sm-5" for="<?php echo $d['paramname']; ?>"><?php echo $d['title1']; ?></label>
	<div class="col-sm-7"><input type="text" class="form-control" name="<?php echo $d['paramname']; ?>" value="<?php echo $d['getparams'][0]; ?>" /></div>
</div>
<?php
if (isset($d['param2']) && $d['param2'] != '') { ?>
	<div class="form-group">
		<label class="col-sm-5" for="<?php echo $d['param2name']; ?>"><?php echo $d['title2']; ?></label>
		<div class="col-sm-7"><input type="text" class="form-control" name="<?php echo $d['param2name']; ?>" value="<?php echo $d['getparams2'][0]; ?>" /></div>
	</div>
<?php } ?>


<div class="col-sm-5"></div>
<div class="col-sm-7">
	<div class="ph-pull-right btn-group ph-zero ph-right-zero">
		<button class="btn btn-success tip hasTooltip" type="button" onclick="<?php echo $jsSet; ?>" title="<?php echo $d['titleset']; ?>"><span class="<?php echo PhocacartRenderIcon::getClass('ok') ?>"></span></button>
		<button class="btn  btn-danger tip hasTooltip ph-pull-right" type="button" onclick="<?php echo $jsClear; ?>" title="<?php echo $d['titleclear']; ?>"><span class="<?php echo PhocacartRenderIcon::getClass('clear') ?>"></span></button> 
	</div>
</div>