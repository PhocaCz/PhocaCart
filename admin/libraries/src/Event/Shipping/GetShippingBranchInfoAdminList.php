<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class GetShippingBranchInfoAdminList extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, object $order, object $shippingMethod, array $eventData = []) {
    parent::__construct('pcs', 'onPCSgetShippingBranchInfoAdminList', [
      'context' => $context,
      'order' => $order,
      'shippingMethod' => $shippingMethod,
      'eventData' => $eventData,
    ]);
  }
}
