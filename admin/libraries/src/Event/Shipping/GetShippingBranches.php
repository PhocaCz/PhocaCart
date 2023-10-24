<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Phoca\PhocaCart\Event\AbstractEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;

class GetShippingBranches extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, object $shippingMethod, array $eventData = []) {
    parent::__construct('pcs', 'onPCSgetShippingBranches', [
      'context' => $context,
      'shippingMethod' => $shippingMethod,
      'eventData' => $eventData,
    ]);
  }
}
