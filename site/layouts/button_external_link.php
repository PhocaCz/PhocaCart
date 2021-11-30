<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
$d 		= $displayData;
$text	= ' ';
if ($d['external_text'] != '') {
	$text = $d['external_text'];
}
?>
<div class="<?php echo $d['s']['c']['pull-right']; ?> ph-item-buy-now-box">
	<a class="<?php echo $d['s']['c']['btn.btn-primary.btn-sm']; ?> ph-btn" href="<?php echo $d['external_link']; ?>" target="_blank"><span class="<?php echo $d['s']['i']['ext-link'] ?>"></span> <?php echo Text::_($text); ?></a>
</div>
<div class="ph-cb"></div>
