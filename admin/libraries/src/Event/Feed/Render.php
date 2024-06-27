<?php
namespace Phoca\PhocaCart\Event\Feed;

use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;
use Phoca\PhocaCart\Event\AbstractEvent;

class Render extends AbstractEvent
{
    use ResultAware, ResultTypeBooleanAware;

    public function __construct(string $plugin, array $items)
    {
        parent::__construct('pcf', 'onPCFRender', [
            'plugin' => $plugin,
            'items'  => $items,
        ]);
    }
}
