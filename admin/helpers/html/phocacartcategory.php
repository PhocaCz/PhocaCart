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

require_once __DIR__ . '/phocacart.php';

/**
 * Phoca Cart Category HTML helper class.
 *
 * @since  4.0
 */
abstract class JHTMLPhocacartcategory extends JHTMLPhocacart
{
  protected static $associationsTable = '#__phocacart_categories';
  protected static $associationsContext = 'com_phocacart.category';
  protected static $associationsEditTask = 'phocacartcategory.edit';

  /**
   * @param int $categoryId
   * @return string
   * @throws Exception
   *
   * @since 4.1
   */
  public static function association(int $categoryId): string	{
    return self::associationsList($categoryId);
  }
}
