<?php
namespace Phoca\PhocaCart\Event\View\Item;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeStringAware;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class AfterTabs extends AbstractEvent
{
  use ResultAware, ResultTypeStringAware;

  public function __construct(string $context, ?array $item, Registry $appParams) {
    parent::__construct('pcv', 'onPCVonItemAfterTabs', [
      'context' => $context,
      'item' => &$item,
      'appParams' => $appParams,
    ]);
  }
}
