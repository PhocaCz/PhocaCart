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

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;


abstract class PhocacartHtmlFeatured
{

	public static function featured($value = 0, $i = 0, $canChange = true, $type = 'product')
	{
		HTMLHelper::_('bootstrap.tooltip');

		// Array of image, task, title, action

        switch ($type){

            case 'manufacturer':
                $states	= array(
                    0	=> array('unfeatured',	'phocacartmanufacturers.featured',	'COM_PHOCACART_UNFEATURED_MANUFACTURER',	'COM_PHOCACART_TOGGLE_TO_FEATURE_MANUFACTURER'),
                    1	=> array('featured',	'phocacartmanufacturers.unfeatured',	'COM_PHOCACART_FEATURED_MANUFACTURER',	'COM_PHOCACART_TOGGLE_TO_UNFEATURE_MANUFACTURER'),
                );
            break;
            case 'category':
                $states	= array(
                    0	=> array('unfeatured',	'phocacartcategories.featured',	'COM_PHOCACART_UNFEATURED_CATEGORY',	'COM_PHOCACART_TOGGLE_TO_FEATURE_CATEGORY'),
                    1	=> array('featured',	'phocacartcategories.unfeatured',	'COM_PHOCACART_FEATURED_CATEGORY',	'COM_PHOCACART_TOGGLE_TO_UNFEATURE_CATEGORY'),
                );
            break;

            case 'product':
            default:
                $states	= array(
                    0	=> array('unfeatured',	'phocacartitems.featured',	'COM_PHOCACART_UNFEATURED_PRODUCT',	'COM_PHOCACART_TOGGLE_TO_FEATURE'),
                    1	=> array('featured',	'phocacartitems.unfeatured',	'COM_PHOCACART_FEATURED_PRODUCT',	'COM_PHOCACART_TOGGLE_TO_UNFEATURE'),
                );
            break;
        }


		$state	= ArrayHelper::getValue($states, (int) $value, $states[1]);
		//$icon	= $state[0];


		$icon = $state[0] === 'featured' ? 'star featured' : 'circle';
		$onclick = 'onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')"';
		$tooltipText = Text::_($state[3]);

		if (!$canChange)
		{
			$onclick     = 'disabled';
			$tooltipText = Text::_($state[2]);
		}

		$html = '<button type="submit" class="tbody-icon' . ($value == 1 ? ' active' : '') . '"'
			. ' aria-labelledby="cb' . $i . '-desc" ' . $onclick . '>'
			. '<span class="icon-' . $icon . '" aria-hidden="true"></span>'
			. '</button>'
			. '<div role="tooltip" id="cb' . $i . '-desc">' . $tooltipText . '</div>';


	/*	if ($canChange)
		{
			$html	= '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::tooltipText($state[3]) . '"><i class="icon-'
					. $icon . '"></i></a>';
		}
		else
		{
			$html	= '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::tooltipText($state[2]) . '"><i class="icon-'
					. $icon . '"></i></a>';
		}
*/
		return $html;





	}
}
