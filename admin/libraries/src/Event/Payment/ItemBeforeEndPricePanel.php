<?php
namespace Phoca\PhocaCart\Event\Payment;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeStringAware;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class ItemBeforeEndPricePanel extends AbstractEvent
{
  use ResultAware, ResultTypeStringAware;

  public function __construct(string $context, ?array &$item, Registry $appParams) {
    parent::__construct('pcp', 'onPCPonItemBeforeEndPricePanel', [
      'context' => $context,
      'item' => &$item,
      'appParams' => $appParams,
    ]);
  }
}
