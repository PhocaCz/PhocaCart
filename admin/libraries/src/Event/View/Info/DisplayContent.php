<?php
namespace Phoca\PhocaCart\Event\View\Info;

use Phoca\PhocaCart\Event\AbstractEvent;

class DisplayContent extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcv', '', [
    ]);
  }
}
