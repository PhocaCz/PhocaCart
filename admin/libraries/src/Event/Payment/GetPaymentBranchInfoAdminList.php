<?php
namespace Phoca\PhocaCart\Event\Payment;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class GetPaymentBranchInfoAdminList extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, object $order, object $paymentMethod, array $eventData = []) {
    parent::__construct('pcp', 'onPCPgetPaymentBranchInfoAdminList', [
      'context' => $context,
      'order' => $order,
      'paymentMethod' => $paymentMethod,
      'eventData' => $eventData,
    ]);
  }
}
