<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
class TablePhocacartOrder extends JTable
{
	function __construct( &$db ) {
		parent::__construct( '#__phocacart_orders', 'id', $db );
	}
	
	function check(){
		
		/*if (trim( $this->title ) == '') {
			$this->setError( JText::_( 'COM_PHOCACART_ERROR_TITLE_NOT_SET') );
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		$this->alias = JApplicationHelper::stringURLSafe($this->alias);
		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}*/
		return true;
	}
}
?>