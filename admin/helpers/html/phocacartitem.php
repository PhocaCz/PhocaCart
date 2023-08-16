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
 * Phoca Cart Product HTML helper class.
 *
 * @since  4.0
 */
abstract class JHTMLPhocacartitem extends JHTMLPhocacart
{
  protected static $associationsTable = '#__phocacart_products';
  protected static $associationsContext = 'com_phocacart.item';
  protected static $associationsEditTask = 'phocacartitem.edit';

  /**
   * @param int $productId
   * @return string
   * @throws Exception
   *
   * @since 4.1
   */
  public static function association(int $productId): string	{
    return self::associationsList($productId);
  }
}
