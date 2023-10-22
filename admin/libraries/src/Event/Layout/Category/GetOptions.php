<?php
namespace Phoca\PhocaCart\Event\Layout\Category;

use Phoca\PhocaCart\Event\AbstractEvent;

class GetOptions extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcl', '', [
    ]);
  }
}
