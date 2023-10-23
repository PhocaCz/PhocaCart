<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeShowPossibleShippingMethod extends AbstractEvent
{
  public function __construct(bool &$active, object $shipping, array $eventData = []) {
    parent::__construct('pcs', 'onPCSbeforeShowPossibleShippingMethod', [
      'active' => &$active,
      'shipping' => $shipping,
      'eventData' => $eventData,
    ]);
  }
}
