<?php
namespace Phoca\PhocaCart\Event\View\Checkout;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeStringAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class InsideAddressAfterHeader extends AbstractEvent
{
  use ResultAware, ResultTypeStringAware;

  public function __construct(string $context, $address, array $eventData = []) {
    parent::__construct('pcv', 'onPCVonCheckoutInsideAddressAfterHeader', [
      'context' => $context,
      'address' => $address,
      'eventData' => $eventData,
    ]);
  }
}
