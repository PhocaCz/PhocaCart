<?php
namespace Phoca\PhocaCart\Event\View\Item;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeLoadColumns extends AbstractEvent
{
  public function __construct(string $context, array &$pluginOptions, array $eventData = []) {
    parent::__construct('pcv', 'onPCVonItemBeforeLoadColumns', [
      'context' => $context,
      'pluginOptions' => &$pluginOptions,
      'eventData' => $eventData,
    ]);
  }
}
