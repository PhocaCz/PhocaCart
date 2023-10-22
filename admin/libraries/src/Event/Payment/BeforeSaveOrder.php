<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeSaveOrder extends AbstractEvent
{
  public function __construct(string $statusId, int $paymentId, array $data) {
    parent::__construct('pcp', 'onPCPbeforeSaveOrder', [
      'statusId' => &$statusId,
      'paymentId' => $paymentId,
      'data' => $data,
    ]);
  }
}
