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
defined( '_JEXEC' ) or die( 'Restricted access' );

class PhocacartPdf
{
	
	public static function replacePdfVariables($text) {
		
		
		
	}
	
	public static function skipStartAndLastTag($text, $tag = 'p') {
		
		$pattern = "=^<".$tag.">(.*)</".$tag.">$=i";
		preg_match($pattern, $text, $matches);
		if (isset($matches[1])) {
			return $matches[1];
		}
		return $text;
	}
}
?>