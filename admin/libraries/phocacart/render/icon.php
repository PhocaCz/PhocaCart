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

use Joomla\CMS\HTML\HTMLHelper;

defined( '_JEXEC' ) or die( 'Restricted access' );

class PhocacartRenderIcon
{

	public static function icon($class, $attributes = '', $suffix = '', $prefix = '', $forceIconType = '') {

		$pC 						= PhocacartUtils::getComponentParameters();
		$icon_type			= $pC->get( 'icon_type', 'svg');

		$pos   = PhocacartPos::isPos();
		if ($pos) {
			$icon_type = 'svg';
		}

		if ($forceIconType != '') {
			$icon_type			= $forceIconType;
		}

		$o = '';

		if ($prefix != '') {
			$o .= $prefix;
		}
		if ($attributes != '') {
			$attributes = ' '. $attributes;
		}

		if ($icon_type == 'svg') {

			// Get path for the svg-definitions.svg - this can be path to media folder:
        	// com_phocacart: media/com_phocacart/images/svg-definitions.svg
        	// or override in template: media/templates/site/cassiopeia/images/com_phocacart/svg-definitions.svg
        	$path = HTMLHelper::image('com_phocacart/svg-definitions.svg', '', [], true, 1);
			$cleanClass = strtok($class, ' ');

			$o .= '<svg class="pc-si pc-si-'.$class.'"'.$attributes.'><use xlink:href="'.$path.'#pc-si-'.$cleanClass.'"></use></svg>';
		} else {
			$o .= '<span class="'.$class.'"'.$attributes.'></span>';
		}

		if ($suffix != '') {
			$o .= $suffix;
		}

		return $o;
	}
}
