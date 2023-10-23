<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

namespace Joomla\Component\PhocaCart\Api\View;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\JsonApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base JsonapiView view
 *
 * @since  4.1.0
 */
abstract class BaseJsonApiView extends JsonApiView
{
  /**
   * Name of the model to use
   *
   * @var string
   * @since  4.1.0
   */
  protected $model = '';

  /**
   * Name of the model to use for displayItem action
   *
   * @var string
   * @since  4.1.0
   */
  protected $modelItem = '';

  /**
   * @inheritdoc
   * @since 4.1.0
   */
  public function __construct($config = [])
  {
    parent::__construct($config);
  }
  /**
   * @inheritdoc
   * @since  4.1.0
   */
  public function displayItem($item = null)
  {
    $this->model = $this->modelItem;
    return parent::displayItem($item);
  }

  /**
   * @inheritdoc
   * @since  4.1.0
   */
  public function getModel($name = null)
  {
    BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/models');
    return BaseDatabaseModel::getInstance('PhocaCart' . $this->model, 'PhocaCartCpModel');
  }

}
