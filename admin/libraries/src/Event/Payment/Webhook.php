<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class Webhook extends AbstractEvent
{
  public function __construct(int $paymentId, array $eventData) {
    parent::__construct('pcp', 'onPCPonPaymentWebhook', [
      'paymentId' => $paymentId,
      'eventData' => $eventData,
    ]);
  }
}
