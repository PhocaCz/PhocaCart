<?php
namespace Phoca\PhocaCart\Dispatcher;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Phoca\PhocaCart\Event\AbstractEvent;

final class Dispatcher
{
  public static function dispatch(AbstractEvent $event): EventInterface
  {
    PluginHelper::importPlugin($event->getPluginType());
    return Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);
  }
}
