<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class InfoViewDisplayContent extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcp', '', [
    ]);
  }
}
