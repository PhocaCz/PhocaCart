<?php
namespace Phoca\PhocaCart\Event\Layout\Items;

use Phoca\PhocaCart\Event\AbstractEvent;

class InsideLayout extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcl', '', [
    ]);
  }
}
