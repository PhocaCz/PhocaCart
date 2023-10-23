<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
namespace Joomla\Component\PhocaCart\Api\Controller;

use Joomla\CMS\Extension\LegacyComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\LegacyModelLoaderTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The products controller
 *
 * @since  4.1.0
 */
class ProductsVariantsController extends BaseApiController
{
  /**
   * @inheritdoc
   * @since  4.1.0
   */
  protected $contentType = 'productsvariants';

  /**
   * @inheritdoc
   * @since  4.1.0
   */
  protected $default_view = 'productsvariants';

  public function getModel($name = '', $prefix = '', $config = [])
  {
    if ($name === 'products')
      $name = 'items';

    if ($name === 'product')
      $name = 'item';

    return parent::getModel($name, $prefix, $config);
  }
}
