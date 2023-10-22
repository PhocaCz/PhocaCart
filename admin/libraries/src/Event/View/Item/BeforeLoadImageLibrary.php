<?php
namespace Phoca\PhocaCart\Event\View\Item;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeLoadImageLibrary extends AbstractEvent
{
  use ResultAware, ResultTypeBooleanAware;

  public function __construct(array &$pluginData, array $eventData = []) {
    parent::__construct('pcv', 'onPCVonItemImageBeforeLoadingImageLibrary', [
      'pluginData' => &$pluginData,
      'eventData' => $eventData,
    ]);
  }
}
