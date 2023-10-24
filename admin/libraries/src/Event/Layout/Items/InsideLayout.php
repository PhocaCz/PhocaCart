<?php
namespace Phoca\PhocaCart\Event\Layout\Items;

use Phoca\PhocaCart\Event\AbstractEvent;

class InsideLayout extends AbstractEvent
{
  public function __construct(string $context, array &$items, array $layoutAttributes, array $eventData = []) {
    parent::__construct('pcl', 'onPCLonItemsInsideLayout', [
      'context' => $context,
      'items' => &$items,
      'layoutAttributes' => $layoutAttributes,
      'eventData' => $eventData,
    ]);
  }
}
