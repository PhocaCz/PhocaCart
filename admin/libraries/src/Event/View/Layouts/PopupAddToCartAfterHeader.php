<?php
namespace Phoca\PhocaCart\Event\View\Layouts;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeStringAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class PopupAddToCartAfterHeader extends AbstractEvent
{
  use ResultAware, ResultTypeStringAware;

  public function __construct(string $context, array $product, array $products, array $total) {
    parent::__construct('pcv', 'onPCVonPopupAddToCartAfterHeader', [
      'context' => $context,
      'product' => $product,
      'products' => $products,
      'total' => $total,
    ]);
  }
}
