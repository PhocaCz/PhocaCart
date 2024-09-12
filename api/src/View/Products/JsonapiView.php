<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

namespace Joomla\Component\PhocaCart\Api\View\Products;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The banners view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render item in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderItem = [
        'id',
        'title',
        'alias',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'alias',
        'price',
        'price_original',
        'stock',
    ];

    private $model = 'items';
    public function displayItem($item = null)
    {
      $this->model = 'item';
      return parent::displayItem($item);
    }

  public function getModel($name = null)
    {
      BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocacart/models');
      return BaseDatabaseModel::getInstance('PhocaCart' . $this->model, 'PhocaCartCpModel');
    }

}
