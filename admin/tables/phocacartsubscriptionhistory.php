<?php
/**
 * @package    phocacart
 * @subpackage Tables
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TablePhocacartSubscriptionHistory extends Table
{
    function __construct(&$db)
    {
        parent::__construct('#__phocacart_subscription_history', 'id', $db);
    }
}
