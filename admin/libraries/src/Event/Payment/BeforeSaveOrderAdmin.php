<?php
namespace Phoca\PhocaCart\Event\Payment;

use Phoca\PhocaCart\Event\AbstractEvent;
use Joomla\CMS\Table\Table;

class BeforeSaveOrderAdmin extends AbstractEvent
{
  public function __construct(string $context, \TablePhocacartOrder $table, bool $isNew, array &$data) {
    parent::__construct('pcp', 'onPCPbeforeSaveOrderAdmin', [
      'context' => $context,
      'table' => $table,
      'isNew' => $isNew,
      'data' => &$data
    ]);
  }
}
