<?php
namespace Phoca\PhocaCart\Dispatcher;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Phoca\PhocaCart\Event\AbstractEvent;

final class Dispatcher
{
  /**
   * @param AbstractEvent $event Event to dispatch
   *
   * @return  array  An array of results from each function call. Note this will be an empty array if no dispatcher is set.
   * @throws \Exception
   */
  public static function dispatch(AbstractEvent $event): array
  {
    PluginHelper::importPlugin($event->getPluginType());
    $result = Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);
    return !isset($result['result']) || \is_null($result['result']) ? [] : $result['result'];
  }

  public static function dispatchChangeText(string &$text)
  {
    // Event for hacked JoomlaCK Mulitlanguages CK plugin
    // see: https://www.joomlack.fr/en/download-joomla-extensions?task=view_category&category_id=17
    // see: https://www.phoca.cz/forum/viewtopic.php?t=58960&start=10
    Factory::getApplication()->triggerEvent('onChangeText', [&$text]);
  }
}
