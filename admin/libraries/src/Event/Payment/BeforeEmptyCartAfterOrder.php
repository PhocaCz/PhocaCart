<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;
use Joomla\Registry\Registry;

class BeforeEmptyCartAfterOrder extends AbstractEvent
{
  public function __construct(string &$proceed, array &$pluginData, Registry $componentParams, ?Registry $paymentParams, object $order, array $data) {
    parent::__construct('pcp', 'onPCPbeforeEmptyCartAfterOrder', [
      'proceed' => &$proceed,
      'pluginData' => &$pluginData,
      'componentParams' => $componentParams,
      'paymentParams' => $paymentParams,
      'order' => $order,
      'data' => $data,
    ]);
  }
}
