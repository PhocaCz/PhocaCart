<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Table\Table;
class TablePhocacartOrderTaxRecapitulation extends Table
{
	function __construct( &$db ) {
		parent::__construct( '#__phocacart_order_tax_recapitulation', 'id', $db );
	}
	
	function check(){
		return true;
	}
}
?>