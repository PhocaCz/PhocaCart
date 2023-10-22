<?php
namespace Phoca\PhocaCart\Event\Payment;

use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeSetPaymentForm extends AbstractEvent
{
  public function __construct(bool &$proceed, Registry $appParams, Registry $paymentParams, array $orderData, array $eventData) {
    parent::__construct('pcp', 'onPCPbeforeSetPaymentForm', [
      'proceed' => &$proceed,
      'appParams' => $appParams,
      'paymentParams' => $paymentParams,
      'orderData' => $orderData,
      'eventData' => $eventData,
    ]);
  }
}
