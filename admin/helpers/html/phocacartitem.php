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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;


use Joomla\Utilities\ArrayHelper;

JLoader::register('PhocacartitemHelper', JPATH_ADMINISTRATOR . '/components/com_phocacart/helpers/phocacartitem.php');

/**
 * Contact HTML helper class.
 *
 * @since  1.6
 */
abstract class JHTMLPhocacartitem
{
	/**
	 * Get the associated language flags
	 *
	 * @param   integer  $productId  The item id to search associations
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  Exception
	 */


	public static function association($productId)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = Associations::getAssociations('com_phocacart', '#__phocacart_products', 'com_phocacart.item', $productId, 'id', 'alias', false)) {
			foreach ($associations as $tag => $associated) {
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated contact items
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('pi.id, pi.title as title')
				->select('l.sef as lang_sef, lang_code')
				->from('#__phocacart_products as pi')
				//->select('cat.title as category_title')
				//->join('LEFT', '#__categories as cat ON cat.id=c.catid')
				->where('pi.id IN (' . implode(',', array_values($associations)) . ')')
				->where('pi.id != ' . $productId)
				->join('LEFT', '#__languages as l ON pi.language=l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try {
				$items = $db->loadObjectList('id');
			} catch (RuntimeException $e) {
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items) {
				foreach ($items as &$item) {
					$text = strtoupper($item->lang_sef);
					$url = Route::_('index.php?option=com_phocacart&task=phocacartitem.edit&id=' . (int) $item->id);

					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');// . '<br />' . JText::sprintf('JCATEGORY_SPRINTF', $item->category_title);
					$classes = 'hasPopover label label-association label-' . $item->lang_sef;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			HTMLHelper::_('bootstrap.popover');

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}


		return $html;
	}

	/**
	 * Show the featured/not-featured icon.
	 *
	 * @param   integer  $value      The featured value.
	 * @param   integer  $i          Id of the item.
	 * @param   boolean  $canChange  Whether the value can be changed or not.
	 *
	 * @return  string	The anchor tag to toggle featured/unfeatured contacts.
	 *
	 * @since   1.6
	 */
	/*
	public static function featured($value = 0, $i, $canChange = true)
	{

		// Array of image, task, title, action
		$states = array(
			0 => array('unfeatured', 'contacts.featured', 'COM_CONTACT_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
			1 => array('featured', 'contacts.unfeatured', 'JFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . HTMLHelper::_('tooltipText', $state[3])
				. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
			. HTMLHelper::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}*/
}
