<?php
namespace Phoca\PhocaCart\Container;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;

final class Container
{
    public static function getDbo(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    public static function getUser(): User
    {
        return Factory::getApplication()->getIdentity();
    }
}
