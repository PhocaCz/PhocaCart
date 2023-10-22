<?php
namespace Phoca\PhocaCart\Event\View\Items;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforePaginationTop extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcv', '', [
    ]);
  }
}
