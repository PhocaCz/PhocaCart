<?php
namespace Phoca\PhocaCart\Event\View\Checkout;

use Phoca\PhocaCart\Event\AbstractEvent;

class AfterConfirm extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcv', '', [
    ]);
  }
}
