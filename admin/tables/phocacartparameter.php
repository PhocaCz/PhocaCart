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

class TablePhocacartParameter extends Table
{
	function __construct(& $db) {
		parent::__construct('#__phocacart_parameters', 'id', $db);
	}

	function check() {
		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocacartUtils::getAliasName($this->alias);

		$wrongAlias = array(
			// Default Parameters
			'c', 'price_from', 'price_to', 'tag', 'label', 'manufacturer', 'a', 's',
			// Joomla! Suffix Parameters
			'start', 'limitstart', 'tmpl', 'format',
			// AJAX
			'option', 'view', 'typeview', 'task',
			// AJAX - com_ajax used for ajax refresching modules
			'module', 'method', 'plugin'
			);
		if (in_array(trim($this->alias), $wrongAlias)) {

			$this->setError(Text::_('COM_PHOCACART_ERROR_TITLE_ALIAS_CANNOT_BE_USED_ALREADY_IN_USE_BY_ANOTHER_PARAMETER'), 'error');
			return false;
		}

		return true;
	}
}
?>
