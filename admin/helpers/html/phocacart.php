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
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Database\ParameterType;

/**
 * Basic HTML helper class.
 *
 * @since  4.1
 */
abstract class JHTMLPhocacart
{
  protected static $associationsTable = '';
  protected static $associationsContext = '';
  protected static $associationsEditTask = '';

  /**
   * @param int $itemId
   * @return string
   * @throws Exception
   *
   * @since 4.1
   */
  protected static function associationsList(int $itemId): string	{
		$html = '';

		// Get the associations
		if ($associations = Associations::getAssociations('com_phocacart', static::$associationsTable, static::$associationsContext, $itemId, 'id', 'alias', null)) {
			foreach ($associations as $tag => $associated) {
				$associations[$tag] = (int)$associated->id;
			}

			// Get the associated items
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
        ->select([
          $db->quoteName('i.id'),
          $db->quoteName('i.title'),
          $db->quoteName('l.sef', 'lang_sef'),
          $db->quoteName('l.lang_code'),
          $db->quoteName('l.image'),
          $db->quoteName('l.title', 'language_title'),
        ])
				->from($db->quoteName(static::$associationsTable, 'i'))
        ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('i.language') . ' = ' . $db->quoteName('l.lang_code'))
        ->whereIn($db->quoteName('i.id'), array_values($associations))
        ->where($db->quoteName('i.id') . ' != :id')
        ->bind(':id', $itemId, ParameterType::INTEGER);
			$db->setQuery($query);

			try {
				$items = $db->loadObjectList('id');
			} catch (RuntimeException $e) {
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items) {
        $languages = LanguageHelper::getContentLanguages([0, 1]);
        $content_languages = array_column($languages, 'lang_code');

				foreach ($items as &$item) {
          if (in_array($item->lang_code, $content_languages)) {
          $text = $item->lang_code;
          $url = Route::_('index.php?option=com_phocacart&task=' . static::$associationsEditTask . '&id=' . (int)$item->id);
          $tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
            . htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');
          $classes = 'badge bg-secondary';
          $item->link = '<a href="' . $url . '" class="' . $classes . '">' . $text . '</a>'
            . '<div role="tooltip" id="tip-' . (int) $itemId . '-' . (int) $item->id . '">' . $tooltip . '</div>';
          } else {
            Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_ASSOCIATIONS_CONTENTLANGUAGE_WARNING', $item->lang_code), 'warning');
          }
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
