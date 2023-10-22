<?php
namespace Phoca\PhocaCart\Event\Tax;

use Phoca\PhocaCart\Event\AbstractEvent;

class UserAddressBeforeSaveAccount extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pct', '', [
    ]);
  }
}
