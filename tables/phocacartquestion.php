<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
jimport('joomla.filter.input');

class TablePhocaCartQuestion extends JTable
{
	function __construct(& $db) {
		parent::__construct('#__phocacart_questions', 'id', $db);
	}
	
	function check() {
		/*if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocaCartUtils::getAliasName($this->alias);
		*/
		return true;
	}
}
?>