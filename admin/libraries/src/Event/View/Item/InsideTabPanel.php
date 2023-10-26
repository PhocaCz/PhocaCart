<?php
namespace Phoca\PhocaCart\Event\View\Item;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class InsideTabPanel extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, ?array $item, Registry $appParams) {
    parent::__construct('pcv', 'onPCVonItemInsideTabPanel', [
      'context' => $context,
      'item' => &$item,
      'appParams' => $appParams,
    ]);
  }
}
