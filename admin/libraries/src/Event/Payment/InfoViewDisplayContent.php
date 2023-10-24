<?php
namespace Phoca\PhocaCart\Event\Payment;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class InfoViewDisplayContent extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(array $infoData, array $eventData = []) {
    parent::__construct('pcp', 'onPCPonInfoViewDisplayContent', [
      'infoData' => $infoData,
      'eventData' => $eventData,
    ]);
  }
}
