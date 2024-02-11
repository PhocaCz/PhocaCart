<?php
namespace Phoca\PhocaCart\Event\Feed;

use Joomla\CMS\Form\Form;
use Phoca\PhocaCart\Event\AbstractEvent;

class BatchForm extends AbstractEvent
{
    public function __construct(string $context, Form $form)
    {
        parent::__construct('pcf', 'onPCFBatchForm', [
            'context' => $context,
            'form'    => $form,
        ]);
    }
}
