<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

namespace Joomla\Component\PhocaCart\Api\Serializer;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Relationship;
use Tobscure\JsonApi\Resource;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Products serializer
 *
 * @since  5.0.0
 */
class OrdersSerializer extends BaseSerializer
{
  /**
   * Build user relationship
   *
   * @param   \stdClass  $model  orders model
   *
   * @return  Relationship
   *
   * @since 5.0.0
   */
  public function createdBy($model)
  {
    $serializer = new JoomlaSerializer('users');

    $resource = (new Resource($model->created_by, $serializer))
      ->addLink('self', Route::link('site', Uri::root() . 'api/index.php/v1/users/' . $model->created_by));

    return new Relationship($resource);
  }

}
