<?php
namespace Phoca\PhocaCart\Event\View\Categories;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeLoadColumns extends AbstractEvent
{
  public function __construct(string $context, array &$options, array $eventData = []) {
    parent::__construct('pcv', 'onPCVonCategoriesBeforeLoadColumns', [
      'context' => $context,
      'options' => &$options,
      'data' => &$eventData,
    ]);
  }
}
