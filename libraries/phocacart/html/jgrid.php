<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
/*
jimport('joomla.html.grid');
jimport('joomla.html.html.grid');
jimport('joomla.html.html.jgrid');
*/

if (! class_exists('JHtmlJGrid')) {
	require_once( JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'html'.DS.'html'.DS.'jgrid.php' );
}

class PhocaCartJGrid extends JHtmlJGrid
{
	
	public static function displayBilling($value, $i, $prefix = '', $enabled = true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		$states	= array(
			1	=> array('hidebilling',	'COM_PHOCACART_DISPLAYED',	'COM_PHOCACART_HIDE',	'COM_PHOCACART_DISPLAYED',	false,	'publish',		'publish'),
			0	=> array('displaybilling',		'COM_PHOCACART_HIDDEN',	'COM_PHOCACART_DISPLAY',	'COM_PHOCACART_HIDDEN',	false,	'unpublish',	'unpublish')
		);
		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}	
	
	public static function displayShipping($value, $i, $prefix = '', $enabled = true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		$states	= array(
			1	=> array('hideshipping',	'COM_PHOCACART_DISPLAYED',	'COM_PHOCACART_HIDE',	'COM_PHOCACART_DISPLAYED',	false,	'publish',		'publish'),
			0	=> array('displayshipping',		'COM_PHOCACART_HIDDEN',	'COM_PHOCACART_DISPLAY',	'COM_PHOCACART_HIDDEN',	false,	'unpublish',	'unpublish')
		);
		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}	
	
	public static function displayAccount($value, $i, $prefix = '', $enabled = true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		$states	= array(
			1	=> array('hideaccount',	'COM_PHOCACART_DISPLAYED',	'COM_PHOCACART_HIDE',	'COM_PHOCACART_DISPLAYED',	false,	'publish',		'publish'),
			0	=> array('displayaccount',		'COM_PHOCACART_HIDDEN',	'COM_PHOCACART_DISPLAY',	'COM_PHOCACART_HIDDEN',	false,	'unpublish',	'unpublish')
		);
		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}	
}
?>