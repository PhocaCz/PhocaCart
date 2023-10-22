<?php
namespace Phoca\PhocaCart\Event\Tax;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class GetUserBillingInfoAdminEdit extends AbstractEvent
{
  use ResultAware, ResultTypeArrayAware;

  public function __construct(string $context, object $order, array $data = []) {
    parent::__construct('pct', 'onPCTgetUserBillingInfoAdminEdit', [
      'context' => $context,
      'order' => $order,
      'data' => $data,
    ]);
  }
}
