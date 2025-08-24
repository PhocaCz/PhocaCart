<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\PhocaCart\MVC\Model;

defined('_JEXEC') or die;

use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;

trait AdminModelTrait
{
    private function getQuery(): QueryInterface
    {
        /** @var DatabaseInterface $db */
        $db = $this->getDatabase();
        return $db->getQuery(true);
    }

    private function executeQuery(QueryInterface $query): bool
    {
        /** @var DatabaseInterface $db */
        $db = $this->getDatabase();
        $db->setQuery($query);
        return $db->execute();
    }

    public function getDispatcher()
    {
        if (!$this->dispatcher) {
            return Factory::getContainer()->get(DispatcherInterface::class);
        }
        return $this->dispatcher;
    }
}
