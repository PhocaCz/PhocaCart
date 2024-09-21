<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class CheckPaymentBranchFormFields extends AbstractEvent
{
  public function __construct(string $context, array &$paymentParams, object $validPayment, array $eventData = []) {
    parent::__construct('pcp', 'onPCScheckPaymentBranchFormFields', [
      'context' => $context,
      'paymentParams' => &$paymentParams,
      'validPayment' => $validPayment,
      'eventData' => $eventData,
    ]);
  }
}
