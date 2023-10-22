<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeCheckPayment extends AbstractEvent
{
  public function __construct(int $paymentId, array $data) {
    parent::__construct('pcp', 'onPCPbeforeCheckPayment', [
      'paymentId' => $paymentId,
      'data' => $data,
    ]);
  }
}
