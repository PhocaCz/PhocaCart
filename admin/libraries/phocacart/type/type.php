<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

class PhocacartType
{
	
	/*
	 * Transform type array to int
	 * 0 ... common
	 * 1 ... online shop
	 * 2 ... pos
	 * 
	 * example: array(0,2) means - all common categories/payment methods/shipping methods plus only POS categories
	 * example: array(0,1) means - all common categories/payment methods/shipping methods plus only Online Shop categories
	 */
	public static function getTypeByTypeArray($type = array(0,1)) {
		
		return (int)$type[1];
		
	}
}
?>