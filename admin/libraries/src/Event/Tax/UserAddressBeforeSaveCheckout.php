<?php
namespace Phoca\PhocaCart\Event\Tax;

use Joomla\CMS\Table\Table;
use Phoca\PhocaCart\Event\AbstractEvent;

class UserAddressBeforeSaveCheckout extends AbstractEvent
{
  public function __construct(string $context, Table &$row, array $eventData = []) {
    parent::__construct('pct', 'onPCTonUserAddressBeforeSaveCheckout', [
      'context' => $context,
      'row' => &$row,
      'eventData' => $eventData,
    ]);
  }
}
