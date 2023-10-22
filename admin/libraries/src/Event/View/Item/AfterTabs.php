<?php
namespace Phoca\PhocaCart\Event\View\Item;

use Phoca\PhocaCart\Event\AbstractEvent;

class AfterTabs extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcv', '', [
    ]);
  }
}
