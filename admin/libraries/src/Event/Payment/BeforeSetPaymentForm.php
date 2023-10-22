<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeSetPaymentForm extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcp', '', [
    ]);
  }
}
