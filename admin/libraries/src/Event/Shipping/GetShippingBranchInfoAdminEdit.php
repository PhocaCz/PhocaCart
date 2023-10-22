<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class GetShippingBranchInfoAdminEdit extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, object $order, array $data) {
    parent::__construct('pcs', 'onPCSgetShippingBranchInfoAdminEdit', [
      'context' => $context,
      'order' => $order,
      'data' => $data,
    ]);
  }
}
