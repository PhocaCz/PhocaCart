<?php
namespace Phoca\PhocaCart\Event\Layout\Category;

use Phoca\PhocaCart\Event\AbstractEvent;

class InsideLayout extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcl', '', [
    ]);
  }
}
