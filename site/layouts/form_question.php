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
$d['paramname'] = $d['title1'] = $d['param2name'] = $d['getparams'][0] = $jsSet = $jsClear = $d['titleclear'] = $d['titleset'] = '';

?>
<div class="<?php echo $d['s']['c']['form-group'] ?>">
	<label class="<?php echo $d['s']['c']['col.xs12.sm5.md5'] ?>" for="<?php echo $d['paramname']; ?>"><?php echo $d['title1']; ?></label>
	<div class="<?php echo $d['s']['c']['col.xs12.sm7.md7'] ?>"><input type="text" class="<?php echo $d['s']['c']['form-control'] ?>" name="<?php echo $d['paramname']; ?>" value="<?php echo $d['getparams'][0]; ?>" /></div>
</div>
<?php
if (isset($d['param2']) && $d['param2'] != '') { ?>
	<div class="<?php echo $d['s']['c']['form-group'] ?>">
		<label class="<?php echo $d['s']['c']['col.xs12.sm5.md5'] ?>" for="<?php echo $d['param2name']; ?>"><?php echo $d['title2']; ?></label>
		<div class="<?php echo $d['s']['c']['col.xs12.sm7.md7'] ?>"><input type="text" class="<?php echo $d['s']['c']['form-control'] ?>" name="<?php echo $d['param2name']; ?>" value="<?php echo $d['getparams2'][0]; ?>" /></div>
	</div>
<?php } ?>


<div class="<?php echo $d['s']['c']['col.xs12.sm5.md5'] ?>"></div>
<div class="<?php echo $d['s']['c']['col.xs12.sm7.md7'] ?>">
	<div class="<?php echo $d['s']['c']['pull-right'] ?> <?php echo $d['s']['c']['btn-group'] ?> ph-zero ph-right-zero">
		<button class="<?php echo $d['s']['c']['btn.btn-success'] ?> tip <?php echo $d['s']['c']['hastooltip'] ?>" type="button" onclick="<?php echo $jsSet; ?>" title="<?php echo $d['titleset']; ?>"><span class="<?php echo $this->s['i']['ok'] ?>"></span></button>
		<button class="<?php echo $d['s']['c']['btn.btn-danger'] ?> tip <?php echo $d['s']['c']['hastooltip'] ?> <?php echo $d['s']['c']['pull-right'] ?>" type="button" onclick="<?php echo $jsClear; ?>" title="<?php echo $d['titleclear']; ?>"><span class="<?php echo $this->s['i']['clear'] ?>"></span></button>
	</div>
</div>
