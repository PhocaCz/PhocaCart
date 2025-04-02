<?php
namespace Phoca\PhocaCart\Event;

use Joomla\CMS\Event\AbstractEvent as JoomlaAbstractEvent;
use Joomla\Event\Event;

class AbstractEvent extends Event
{
  protected $pluginType = '';

  public function __construct(string $pluginType, string $name, array $arguments = [])
  {
    parent::__construct($name, $arguments);
    $this->pluginType = $pluginType;
  }

  /**
   * @return string
   *
   * @since 4.1.0
   */
  public function getPluginType(): string
  {
    return $this->pluginType;
  }

  public function shouldProceed(string $pluginName): bool
  {
    $eventData = $this->getArgument('eventData');
    return !!$eventData && is_array($eventData) && isset($eventData['pluginname']) && $eventData['pluginname'] == $pluginName;
  }
}
