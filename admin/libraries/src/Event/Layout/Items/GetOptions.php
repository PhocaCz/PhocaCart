<?php
namespace Phoca\PhocaCart\Event\Layout\Items;

use Phoca\PhocaCart\Event\AbstractEvent;

class GetOptions extends AbstractEvent
{
  public function __construct(string $context, array &$pluginOptions, array $eventData = []) {
    parent::__construct('pcl', 'onPCLonItemsGetOptions', [
      'context' => $context,
      'pluginOptions' => &$pluginOptions,
      'eventData' => $eventData,
    ]);
  }
}
