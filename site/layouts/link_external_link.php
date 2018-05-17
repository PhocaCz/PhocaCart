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
?>
<div class="ph-pull-right">
	<div class="ph-external-link"><?php
	if ($d['title'] != '') {
		echo '<a href="'.$d['linkexternal'].'" target="_blank">'.$d['title'].'</a>';
	} else {
		echo '<a href="'.$d['linkexternal'].'" target="_blank">'.$d['linkexternal'].'</a>';
	}
	?></div>
</div>