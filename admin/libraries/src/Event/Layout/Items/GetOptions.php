<?php
namespace Phoca\PhocaCart\Event\Layout\Items;

use Phoca\PhocaCart\Event\AbstractEvent;

class GetOptions extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcl', '', [
    ]);
  }
}
