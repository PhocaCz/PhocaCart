<?php
namespace Phoca\PhocaCart\Event\View\Checkout;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeStringAware;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class AfterCart extends AbstractEvent
{
  use ResultAware, ResultTypeStringAware;

  public function __construct(string $context, \PhocacartAccess $access, Registry &$appParams, array $total) {
    parent::__construct('pcv', 'onPCVonCheckoutAfterCart', [
      'context' => $context,
      'access' => $access,
      'appParams' => &$appParams,
      'total' => $total,
    ]);
  }
}
