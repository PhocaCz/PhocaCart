<?php
namespace Phoca\PhocaCart\Event\Tax;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class ChangeTaxBasedRule extends AbstractEvent
{
  use ResultAware, ResultTypeBooleanAware;

  public function __construct(string $context, array &$taxRule, array $eventData = []) {
    parent::__construct('pct', 'onPCTonChangeTaxBasedRule', [
      'context' => $context,
      'taxRule' => &$taxRule,
      'eventData' => $eventData,
    ]);
  }
}
