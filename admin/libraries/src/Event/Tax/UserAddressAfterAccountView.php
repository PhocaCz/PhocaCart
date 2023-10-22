<?php
namespace Phoca\PhocaCart\Event\Tax;

use Phoca\PhocaCart\Event\AbstractEvent;

class UserAddressAfterAccountView extends AbstractEvent
{
  public function __construct(string $context, array $address, array $eventData = []) {
    parent::__construct('pct', 'onPCTonAfterUserAddressAccountView', [
      'context' => $context,
      'address' => $address,
      'eventData' => $eventData,
    ]);
  }
}
