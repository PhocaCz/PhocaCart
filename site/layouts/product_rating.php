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
if ((int)$d['display_star_rating'] > 0 && $d['rating'] > 0) {
	?><div class="ph-stars-box"><span class="ph-stars"><span style="width:<?php echo ((int)$d['rating'] * (int)$d['size']) ?>px;"></span></span></div><?php
} else if ($d['display_star_rating'] == 2) {
	?><div class="ph-stars-box"><span class="ph-stars"><span style="width: 0px;"></span></span></div><?php
}
?>