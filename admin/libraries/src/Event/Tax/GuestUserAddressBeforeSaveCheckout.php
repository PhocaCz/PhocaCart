<?php
namespace Phoca\PhocaCart\Event\Tax;

use Phoca\PhocaCart\Event\AbstractEvent;

class GuestUserAddressBeforeSaveCheckout extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pct', '', [
    ]);
  }
}
