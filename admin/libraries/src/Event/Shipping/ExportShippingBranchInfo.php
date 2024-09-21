<?php
namespace Phoca\PhocaCart\Event\Shipping;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeMixedAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class ExportShippingBranchInfo extends AbstractEvent implements ResultAwareInterface
{
  use ResultAware, ResultTypeMixedAware;

  public function __construct(string $context, array $pks, object $shippingInfo, array $eventData = []) {
    parent::__construct('pcs', 'onPCSexportShippingBranchInfo', [
      'context' => $context,
      'pks' => $pks,
      'shippingInfo' => $shippingInfo,
      'eventData' => $eventData,
    ]);
  }
}
