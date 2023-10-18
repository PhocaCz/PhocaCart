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
use Joomla\CMS\Language\Text;
jimport('joomla.filter.input');

class TablePhocacartTime extends Table
{
	function __construct(& $db) {
		parent::__construct('#__phocacart_opening_times', 'id', $db);
	}

	function check() {

		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocacartUtils::getAliasName($this->alias);

        if (!isset($this->date) || $this->date == '0' || $this->date == '') {
			$this->date = '0000-00-00 00:00:00';
		}


		$timeFrom = PhocacartTime::getTime($this->hour_from, $this->minute_from);
		$timeTo = PhocacartTime::getTime($this->hour_to, $this->minute_to);

		if (strtotime($timeFrom) > 0  && strtotime($timeTo) == 0) {
			$this->setError(Text::_('COM_PHOCACART_IF_TIME_FROM_SET_THEN_TIME_TO_MUST_BE_SET_TOO'));
			return false;
		}
		if (strtotime($timeTo) > 0  && strtotime($timeFrom) == 0) {
			$this->setError(Text::_('COM_PHOCACART_IF_TIME_TO_SET_THEN_TIME_FROM_MUST_BE_SET_TOO'));
			return false;
		}

		if(strtotime($timeFrom) > strtotime($timeTo)) {
			//$app	= Factory::getApplication();
			//$app->enqueueMessage(Text::_('COM_PHOCACART_TIME_FROM_CANNOT_BE_GREATER_THAN_TIME_TO'), 'error');
			$this->setError(Text::_('COM_PHOCACART_TIME_FROM_CANNOT_BE_GREATER_THAN_TIME_TO'));
			return false;
		}

		return true;
	}
}
?>
