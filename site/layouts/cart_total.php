<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

/* Total amount used when product added to cart with ajax method and it returns updated cart, updated count of items in cart and updated total amount */
defined('_JEXEC') or die();
$d 		= $displayData;
$price	= new PhocacartPrice();
if (isset($d['total'][0]['brutto'])) {
	echo $price->getPriceFormat($d['total'][0]['brutto']);
}
?>
