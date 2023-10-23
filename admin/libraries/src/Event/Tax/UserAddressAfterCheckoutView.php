<?php
namespace Phoca\PhocaCart\Event\Tax;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class UserAddressAfterCheckoutView extends AbstractEvent
{
    use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, array &$address, array $eventData = []) {
    parent::__construct('pct', 'onPCTonAfterUserAddressCheckoutView', [
      'context' => $context,
      'address' => &$address,
      'eventData' => $eventData,
    ]);
  }
}
