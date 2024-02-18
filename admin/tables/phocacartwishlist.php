<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;

class TablePhocacartWishlist extends Table
{
    public function __construct(DatabaseDriver $db, DispatcherInterface $dispatcher = null)
    {
		parent::__construct('#__phocacart_wishlists', 'id', $db, $dispatcher);
	}

    public function store($updateNulls = false)
    {
        if (!(int)$this->date) {
            $this->date = Factory::getDate()->toSql();
        }

        return parent::store($updateNulls);
    }
}
