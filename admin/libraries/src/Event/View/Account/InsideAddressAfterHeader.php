<?php
namespace Phoca\PhocaCart\Event\View\Account;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeStringAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class InsideAddressAfterHeader extends AbstractEvent
{
  use ResultAware, ResultTypeStringAware;

  public function __construct(string $context, object $data, array $eventData = []) {
    parent::__construct('pcv', 'onPCVonAccountInsideAddressAfterHeader', [
      'context' => $context,
      'data' => $data,
      'eventData' => $eventData,
    ]);
  }
}
