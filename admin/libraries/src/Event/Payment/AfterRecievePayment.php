<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class AfterRecievePayment extends AbstractEvent
{
  public function __construct(int $messageId, array &$message, array $eventData = []) {
    parent::__construct('pcp', 'onPCPafterRecievePayment', [
      'messageId' => $messageId,
      'message' => &$message,
      'eventData' => $eventData,
    ]);
  }
}
