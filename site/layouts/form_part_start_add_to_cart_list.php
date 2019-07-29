<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Add to cart button
// 1 Standard Button
// 4 Icon Only Button
// 100 and above - specific external buttons
// 102 Paddle
// 103 External link
defined('_JEXEC') or die();
$d 				= $displayData;
$displayData 	= null;
$task 			= $d['typeview'] == 'Pos' ? 'pos.add' : 'checkout.add';
$ticketId 		= isset($d['ticketid']) ? (int)$d['ticketid'] : 0;
$unitId 		= isset($d['unitid']) ? (int)$d['unitid'] : 0;
$sectionId 		= isset($d['sectionid']) ? (int)$d['sectionid'] : 0;

echo '<form 
	id="phCartAddToCartButton'.(int)$d['id'].'"
	class="phItemCartBoxForm phjAddToCart phj'.$d['typeview'].' phjAddToCartV'.$d['typeview'].'P'.(int)$d['id'].'" 
	action="'.$d['linkch'].'" method="post">';

echo '<input type="hidden" name="id" value="'.(int)$d['id'].'">';
echo '<input type="hidden" name="catid" value="'.(int)$d['catid'].'">';
echo (int)$ticketId > 0 ? '<input type="hidden" name="ticketid" value="'.(int)$ticketId.'">' : '';
echo (int)$unitId > 0 ? '<input type="hidden" name="unitid" value="'.(int)$unitId.'">' : '';
echo (int)$sectionId > 0 ? '<input type="hidden" name="sectionid" value="'.(int)$sectionId.'">' : '';
echo '<input type="hidden" name="quantity" value="1">';
echo '<input type="hidden" name="task" value="'.$task.'">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="return" value="'.$d['return'].'" />';

// FORM END MUST BE ADDED IN OUTPUT TEMPLATE --->
?>
