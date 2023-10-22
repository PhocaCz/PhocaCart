<?php
namespace Phoca\PhocaCart\Event\Tax;

use Joomla\CMS\Event\AbstractEvent;

class BeforeSaveOrder extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pct', '', [
    ]);
  }
}
