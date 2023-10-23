<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Phoca\PhocaCart\Event\AbstractEvent;

class AfterSaveOrder extends AbstractEvent
{
  public function __construct(string $context, array $eventData = []) {
    parent::__construct('pcs', 'onPCSafterSaveOrder', [
      'context' => $context,
      'eventData' => $eventData,
    ]);
  }
}
