<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

namespace Joomla\Component\PhocaCart\Api\View\Status;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\Event\OnGetApiFields;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\PhocaCart\Api\View\BaseJsonApiView;
use Tobscure\JsonApi\Resource;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The status view
 *
 * @since  5.0.0
 */
class JsonApiView extends BaseJsonApiView
{

  /**
   * @param $tpl
   * @return string
   * @throws \Exception
   *
   * @since 5.0.0
   */
  public function displayStatus($tpl = null)
  {
    $data = (object)[
      'id' => 1,
      'status' => 'OK',
    ];

    $eventData = [
      'type'      => OnGetApiFields::ITEM,
      'fields'    => ['status'],
      'relations' => $this->relationship,
      'context'   => $this->type,
    ];
    $event     = new OnGetApiFields('onApiGetFields', $eventData);

    /** @var OnGetApiFields $eventResult */
    $eventResult = Factory::getApplication()->getDispatcher()->dispatch('onApiGetFields', $event);

    $element = (new Resource($data, $this->serializer))
      ->fields([$this->type => $eventResult->getAllPropertiesToRender()]);

    if (!empty($this->relationship)) {
      $element->with($eventResult->getAllRelationsToRender());
    }

    $this->document->setData($element);
    $this->document->addLink('self', Uri::current());

    return $this->document->render();
  }
}
