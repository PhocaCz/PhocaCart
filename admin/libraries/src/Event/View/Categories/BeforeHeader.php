<?php
namespace Phoca\PhocaCart\Event\View\Categories;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeStringAware;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeHeader extends AbstractEvent
{
  use ResultAware, ResultTypeStringAware;

  public function __construct(string $context, array &$categories, Registry &$appParams) {
    parent::__construct('pcv', 'onPCVonCategoriesBeforeHeader', [
      'context' => $context,
      'categories' => &$categories,
      'appParams' => &$appParams,
    ]);
  }
}
