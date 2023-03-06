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

class TablePhocacartVendor extends Table
{
	function __construct(& $db) {
		parent::__construct('#__phocacart_vendors', 'id', $db);
	}

	function check() {
		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocacartUtils::getAliasName($this->alias);

        if (!isset($this->date) || $this->date == '0' || $this->date == '') {
			$this->date = '0000-00-00 00:00:00';
		}

		return true;
	}
}
?>
