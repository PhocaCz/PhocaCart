<?php
namespace Phoca\PhocaCart\Event\Tax;

use Joomla\CMS\Table\Table;
use Phoca\PhocaCart\Event\AbstractEvent;

class UserAddressBeforeSaveAccount extends AbstractEvent
{
  public function __construct(string $context, Table &$row, array $eventData = []) {
    parent::__construct('pct', 'onPCTonUserAddressBeforeSaveAccount', [
      'context' => $context,
      'row' => &$row,
      'eventData' => $eventData,
    ]);
  }
}
