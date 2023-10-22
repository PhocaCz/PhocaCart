<?php
namespace Phoca\PhocaCart\Event\View\Items;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeLoadColumns extends AbstractEvent
{
  public function __construct(string $context, array &$pluginOptions, array $eventData = []) {
    parent::__construct('pcv', 'onPCVonItemsBeforeLoadColumns', [
      'context' => $context,
      'pluginOptions' => &$pluginOptions,
      'eventData' => $eventData,
    ]);
  }
}
