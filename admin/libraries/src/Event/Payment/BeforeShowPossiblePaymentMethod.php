<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeShowPossiblePaymentMethod extends AbstractEvent
{
  public function __construct(&$active, object $paymentMethod, array $eventData = []) {
    parent::__construct('pcp', 'onPCPbeforeShowPossiblePaymentMethod', [
      'active' => &$active,
      'paymentMethod' => $paymentMethod,
      'eventData' => $eventData,
    ]);
  }
}
