<?php
namespace Phoca\PhocaCart\Event\View\Info;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class DisplayContent extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, array &$infoData, int &$infoAction, array $eventData = []) {
    parent::__construct('pcv', 'onPCVonInfoViewDisplayContent', [
      'context' => $context,
      'infoData' => &$infoData,
      'infoAction' => &$infoAction,
      'eventData' => $eventData,
    ]);
  }
}
