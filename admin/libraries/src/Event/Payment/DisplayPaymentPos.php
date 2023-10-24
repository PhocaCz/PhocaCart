<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;

class DisplayPaymentPos extends AbstractEvent
{
  public function __construct(string &$output, array $templateData, array $eventData = []) {
    parent::__construct('pcp', 'onPCPonDisplayPaymentPos', [
      'output' => &$output,
      'templateData' => $templateData,
      'eventData' => $eventData,
    ]);
  }
}
