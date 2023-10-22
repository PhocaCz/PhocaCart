<?php
namespace Phoca\PhocaCart\Event\Layout\Category;

use Phoca\PhocaCart\Event\AbstractEvent;

class GetOptions extends AbstractEvent
{
  public function __construct(string $context, array &$pluginOptions, array $eventData = []) {
    parent::__construct('pcl', 'onPCLonCategoryGetOptions', [
      'context' => $context,
      'pluginOptions' => &$pluginOptions,
      'eventData' => $eventData,
    ]);
  }
}
