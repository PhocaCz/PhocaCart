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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
jimport('joomla.filter.input');

class TablePhocaCartFormfield extends Table
{
	function __construct(& $db) {
		parent::__construct('#__phocacart_form_fields', 'id', $db);
	}


	function check() {
		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocacartUtils::getAliasName($this->alias);

		return true;
	}

	public function displayItem($pks = null, $state = 1, $userId = 0, $column = 'display_billing') {
		$k = $this->_tbl_key;

		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		if (empty($pks)) {
			if ($this->$k) {
				$pks = array($this->$k);
			} else {
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		$where = $k.'='.implode(' OR '.$k.'=', $pks);
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
			$checkin = '';
		} else {
			$checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
		}

		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET '. $this->_db->quoteName(htmlspecialchars($column)).' = '.(int) $state .
			' WHERE ('.$where.')' .
			$checkin
		);
		//$this->_db->query();


		try {
			$this->_db->execute();
		} catch (RuntimeException $e) {

			throw new Exception($e->getMessage(), 500);
			return false;
		}

		if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
			foreach($pks as $pk) {
				$this->checkin($pk);
			}
		}

		if (in_array($this->$k, $pks)) {
			$this->state = $state;
		}

		$this->setError('');
		return true;
	}
}
?>
