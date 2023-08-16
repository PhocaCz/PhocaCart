<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\PhocaCart\Administrator\Extension\PhocaCartComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * com_phocacart service provider.
 *
 * @since  4.1.0
 */
return new class implements ServiceProviderInterface {
  /**
   * @inheritdoc
   * @since   4.1.0
   */
  public function register(Container $container)
  {
    require_once __DIR__ . '/../libraries/bootstrap.php';
    JLoader::registerNamespace('Joomla\\Component\\PhocaCart\\Administrator',  __DIR__ . '/../src');
    JLoader::registerNamespace('Joomla\\Component\\PhocaCart\\Api',  JPATH_API . '/components/com_phocacart/src');

    $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\PhocaCart'));
    $container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\PhocaCart'));

    $container->set(
      ComponentInterface::class,
      function (Container $container) {
        $component = new PhocaCartComponent($container->get(ComponentDispatcherFactoryInterface::class));
        //$component->setRegistry($container->get(Registry::class));
        $component->setMVCFactory($container->get(MVCFactoryInterface::class));

        return $component;
      }
    );
  }
};
