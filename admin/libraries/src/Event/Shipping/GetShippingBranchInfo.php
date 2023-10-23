<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class GetShippingBranchInfo extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, array $shippingMethod, array $shippingParams, array $eventData = []) {
    parent::__construct('pcs', 'onPCSgetShippingBranchInfo', [
      'context' => $context,
      'shippingMethod' => $shippingMethod,
      'shippingParams' => $shippingParams,
      'eventData' => $eventData,
    ]);
  }
}
