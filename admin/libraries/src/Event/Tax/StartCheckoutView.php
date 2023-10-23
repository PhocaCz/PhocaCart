<?php
namespace Phoca\PhocaCart\Event\Tax;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class StartCheckoutView extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, &$address, array $eventData = []) {
    parent::__construct('pct', 'onPCTonStartCheckoutView', [
      'context' => $context,
      'address' => &$address,
      'eventData' => $eventData,
    ]);
  }
}
