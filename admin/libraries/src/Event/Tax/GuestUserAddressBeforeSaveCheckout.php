<?php
namespace Phoca\PhocaCart\Event\Tax;

use Phoca\PhocaCart\Event\AbstractEvent;

class GuestUserAddressBeforeSaveCheckout extends AbstractEvent
{
  public function __construct(string $context, array &$data, array $eventData = []) {
    parent::__construct('pct', 'onPCTonGuestUserAddressBeforeSaveCheckout', [
      'context' => $context,
      'data' => &$data,
      'eventData' => $eventData,
    ]);
  }
}
