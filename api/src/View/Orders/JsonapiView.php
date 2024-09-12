<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

namespace Joomla\Component\PhocaCart\Api\View\Orders;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\PhocaCart\Api\Serializer\OrdersSerializer;
use Joomla\Component\PhocaCart\Api\View\BaseJsonApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The orders view
 *
 * @since  5.0.0
 */
class JsonApiView extends BaseJsonApiView
{
  /**
   * @inheritdoc
   * @since  4.1.0
   */
  protected $model = 'orders';

  /**
   * @inheritdoc
   * @since  4.1.0
   */
  protected $modelItem = 'order';

  /**
   * @inheritdoc
   * @since  4.1.0
   */
  protected $fieldsToRenderItem = [
    'id',
    'order_token',
    'user_id',
    'group_id',
    'invoice_id',
    'products',
  ];

  /**
   * @inheritdoc
   * @since  4.1.0
   */
  protected $fieldsToRenderList = [
    'id',
    'order_token',
    'user_id',
    'group_id',
    'invoice_id',
  ];

  /**
   * @inheritdoc
   * @since  5.0.0
   */
  protected $relationship = [
    'user_id',
  ];

  public function __construct($config = [])
  {
    if (\array_key_exists('contentType', $config)) {
      $this->serializer = new OrdersSerializer($config['contentType']);
    }

    parent::__construct($config);
  }

  /**
   * @inheritdoc
   * @since  5.0.0
   */
  public function displayItem($item = null)
  {
    return parent::displayItem();
  }

  /**
   * @inheritdoc
   * @since  5.0.0
   */
  protected function prepareItem($item)
  {
    if (!isset($item->products))
      $item->products = [];



    return parent::prepareItem($item);
  }
}
