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
$displayData 	= null;
$required		= $d['display'] == 2 ? 'required="" aria-required="true"' : '';
?>
<div class="<?php echo $d['class'] ?>">
	<label><input type="checkbox" id="<?php echo $d['name'] ?>" name="<?php echo $d['name'] ?>" <?php echo $required ?> /><?php echo $d['label_text'] ?></label>
</div>