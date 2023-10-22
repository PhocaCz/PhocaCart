<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Joomla\CMS\Event\AbstractEvent;

class GetShippingBranches extends AbstractEvent
{
  public function __construct() {
    parent::__construct('pcs', '', [
    ]);
  }
}
