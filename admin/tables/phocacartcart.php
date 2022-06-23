<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
jimport('joomla.filter.input');

class TablePhocacartCart extends Table
{
	// NOT USED
/*	function __construct(& $db) {
		parent::__construct('#__phocacart_cart', 'user_id', $db);
	}
	
	function __construct(& $db) {
		parent::__construct('#__phocacart_cart_multiple', 'id', $db);
	}
	*/
	
	function __construct(& $db) {
		parent::__construct('#__phocacart_cart_multiple', array('user_id', 'vendor_id', 'ticket_id', 'unit_id', 'section_id'), $db);
	}

}
?>