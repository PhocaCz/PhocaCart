<?php
namespace Phoca\PhocaCart\Event\View\Category;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeStringAware;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class BeforePaginationTop extends AbstractEvent
{
  use ResultAware, ResultTypeStringAware;

  public function __construct(string $context, array &$items, Registry &$appParams) {
    parent::__construct('pcv', 'onPCVonCategoryBeforePaginationTop', [
      'context' => $context,
      'items' => &$items,
      'appParams' => &$appParams,
    ]);
  }
}
