<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\ComponentInterface;
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
    require_once __DIR__ . '/../src/Extension/PhocaCartComponent.php';
    $container->set(
      ComponentInterface::class,
      new PhocaCartComponent()
    );
  }
};
