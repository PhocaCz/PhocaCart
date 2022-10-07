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

class TablePhocacartCurrency extends Table
{
	function __construct(& $db) {
		parent::__construct('#__phocacart_currencies', 'id', $db);
	}

	function check() {


	    if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocacartUtils::getAliasName($this->alias);

		if ($this->exchange_rate == 0 || $this->exchange_rate < 0.00000001) {
            $this->setError( Text::_( 'COM_PHOCACART_ERROR_EXCHANGE_RATE_MUST_BE_GREATER_THAN_ZERO') );
		    return false;
        }

		return true;
	}
}
?>
