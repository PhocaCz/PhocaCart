<?php
namespace Phoca\PhocaCart\Event\View\Category;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeLoadColumns extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcv', '', [
    ]);
  }
}
