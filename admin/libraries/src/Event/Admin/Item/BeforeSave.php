<?php
namespace Phoca\PhocaCart\Event\Admin\Item;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeSave extends AbstractEvent
{
  use ResultAware, ResultTypeBooleanAware;

  public function __construct(string $context, object &$product, bool $isNew, array $data) {
    parent::__construct('pca', 'onPCAonItemBeforeSave', [
      'context' => $context,
      'product' => &$product,
      'isNew' => $isNew,
      'data' => $data,
    ]);
  }
}
