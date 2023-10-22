<?php
namespace Phoca\PhocaCart\Event\View\Account;

use Phoca\PhocaCart\Event\AbstractEvent;

class InsideAddressAfterHeader extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcv', '', [
    ]);
  }
}
