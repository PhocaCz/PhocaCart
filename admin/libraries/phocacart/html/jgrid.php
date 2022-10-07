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
use Joomla\CMS\HTML\Helpers\JGrid;
use Joomla\CMS\HTML\HTMLHelper;
/*
jimport('joomla.html.grid');
jimport('joomla.html.html.grid');
jimport('joomla.html.html.jgrid');
*/

if (! class_exists('HTMLHelperJGrid')) {
	require_once( JPATH_SITE.'/libraries/src/HTML/Helpers/JGrid.php' );
}

class PhocacartHtmlJgrid extends JGrid
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

	public static function displayRequired($value, $i, $prefix = '', $enabled = true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		$states	= array(
			1	=> array('disablerequired',	'COM_PHOCACART_REQUIRED',	'COM_PHOCACART_MAKE_FIELD_NOT_REQUIRED',	'COM_PHOCACART_REQUIRED',	false,	'publish',		'publish'),
			0	=> array('enablerequired',	'COM_PHOCACART_NOT_REQUIRED',	'COM_PHOCACART_MAKE_FIELD_REQUIRED',	'COM_PHOCACART_NOT_REQUIRED',	false,	'unpublish',	'unpublish')
		);
		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}



	public static function approve($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb', $publish_up = null, $publish_down = null)
	{
		if (is_array($prefix))
		{
			$options = $prefix;
			$enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$states = array(
			1 => array('unpublish', 'COM_PHOCACART_APPROVED', 'COM_PHOCACART_DISAPPROVE_ITEM', 'COM_PHOCACART_APPROVED', true, 'publish', 'publish'),
			0 => array('publish', 'COM_PHOCACART_NOT_APPROVED', 'COM_PHOCACART_APPROVE_ITEM', 'COM_PHOCACART_NOT_APPROVED', true, 'unpublish', 'unpublish'),
		);

		return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}
}
?>
