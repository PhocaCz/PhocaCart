<?php
namespace Phoca\PhocaCart\Event\View\Item;

use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class Image extends AbstractEvent
{
  public function __construct(string $context, object &$item, array &$templateData, Registry &$appParams) {
    parent::__construct('pcv', 'onPCVonItemImage', [
      'context' => $context,
      'item' => &$item,
      'templateData' => &$templateData,
      'appParams' => &$appParams,
    ]);
  }
}
