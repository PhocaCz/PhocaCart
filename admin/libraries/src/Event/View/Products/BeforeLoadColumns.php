<?php
namespace Phoca\PhocaCart\Event\View\Products;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeLoadColumns extends AbstractEvent
{
	public function __construct(string $context, array &$options, array $eventData = []) {
    parent::__construct('pcv', 'onPCVonProductsBeforeLoadColumns', [
      'context' => $context,
      'options' => &$options,
      'eventData' => $eventData,
    ]);
	}
}
