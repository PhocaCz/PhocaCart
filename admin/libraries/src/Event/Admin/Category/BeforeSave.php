<?php
namespace Phoca\PhocaCart\Event\Admin\Category;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeSave extends AbstractEvent
{
  use ResultAware, ResultTypeBooleanAware;

  public function __construct(string $context, object &$category, bool $isNew, array $eventData = []) {
    parent::__construct('pca', 'onPCAonCategoryBeforeSave', [
      'context' => $context,
      'category' => &$category,
      'isNew' => $isNew,
      'eventData' => $eventData,
    ]);
  }
}
