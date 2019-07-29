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
// $d['number'];
// $d['suffix'];
// $d['status']; // 'finished' or 'pending'
// $d['type']; // parameter checkout_icon_status

$d['icon'] 	= $d['status'] == 'finished' ? 'ok' : 'not-ok';
$d['class']	= $d['s']['i'][$d['icon']]. strip_tags($d['suffix']).' ph-checkout-icon-'.$d['status'];

if ($d['type'] == 1) {

?><div class="<?php echo $d['s']['c']['pull-right'] ?>">
	<span class="ph-checkout-icon-spec-<?php echo $d['status'];?>"><?php echo $d['number'];?></span>
</div><?php

} else {

?><div class="<?php echo $d['s']['c']['pull-right'] ?>">
	<span class="<?php echo $d['class']; ?>"></span>
</div><?php

}
