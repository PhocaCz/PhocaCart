<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Phoca\PhocaCart\Event\AbstractEvent;

class CheckShippingBranchFormFields extends AbstractEvent
{
  public function __construct(string $context, array &$shippingParams, object $validShipping, array $eventData = []) {
    parent::__construct('pcs', 'onPCScheckShippingBranchFormFields', [
      'context' => $context,
      'shippingParams' => &$shippingParams,
      'validShipping' => $validShipping,
      'eventData' => $eventData,
    ]);
  }
}
