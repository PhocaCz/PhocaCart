<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class AfterCancelPayment extends AbstractEvent
{
  public function __construct(int $messageId, array &$message, array $data) {
    parent::__construct('pcp', 'onPCPafterCancelPayment', [
      'messageId' => $messageId,
      'message' => &$message,
      'data' => $data,
    ]);
  }
}
