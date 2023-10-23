<?php
namespace Phoca\PhocaCart\Event\Admin\Item;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;
use Joomla\CMS\Table\Table;
use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeSave extends AbstractEvent
{
  use ResultAware, ResultTypeBooleanAware;

  public function __construct(string $context, Table &$product, bool $isNew, array $eventData = []) {
    parent::__construct('pca', 'onPCAonItemBeforeSave', [
      'context' => $context,
      'product' => &$product,
      'isNew' => $isNew,
      'eventData' => $eventData,
    ]);
  }
}
