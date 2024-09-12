<?php
namespace Phoca\PhocaCart\Event\Feed\Item;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;
use Joomla\CMS\Form\Form;
use Phoca\PhocaCart\Event\AbstractEvent;

class BeforeRender extends AbstractEvent
{
  use ResultAware, ResultTypeBooleanAware;

  public function __construct(string $context, string $feedName, Form &$subForm) {
    parent::__construct('pcf', 'onPCFonItemBeforeRender', [
      'context' => $context,
      'feedName' => $feedName,
      'subForm' => &$subForm,
    ]);
  }
}
