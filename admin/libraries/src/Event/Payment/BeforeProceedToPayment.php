<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeProceedToPayment extends AbstractEvent
{
  public function __construct(int &$proceed, array &$message, array $data) {
    parent::__construct('pcp', 'onPCPbeforeProceedToPayment', [
      'proceed' => &$proceed,
      'message' => &$message,
      'data' => $data,
    ]);
  }
}
