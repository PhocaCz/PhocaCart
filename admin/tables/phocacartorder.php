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
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
class TablePhocacartOrder extends Table
{
	function __construct( &$db ) {
		parent::__construct( '#__phocacart_orders', 'id', $db );
	}

	function check(){

		/*if (trim( $this->title ) == '') {
			$this->setError( Text::_( 'COM_PHOCACART_ERROR_TITLE_NOT_SET') );
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias);
		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = Factory::getDate()->format("Y-m-d-H-i-s");
		}*/

        if (!isset($this->date) || $this->date == '0' || $this->date == '') {
			$this->date = '0000-00-00 00:00:00';
		}
        if (!isset($this->tracking_date_shipped) || $this->tracking_date_shipped == '0' || $this->tracking_date_shipped == '') {
			$this->tracking_date_shipped = '0000-00-00 00:00:00';
		}
		if (!isset($this->invoice_date) || $this->invoice_date == '0' || $this->invoice_date == '') {
			$this->invoice_date = '0000-00-00 00:00:00';
		}
		if (!isset($this->invoice_due_date) || $this->invoice_due_date == '0' || $this->invoice_due_date == '') {
			$this->invoice_due_date = '0000-00-00 00:00:00';
		}
		if (!isset($this->invoice_time_of_supply) || $this->invoice_time_of_supply == '0' || $this->invoice_time_of_supply == '') {
			$this->invoice_time_of_supply = '0000-00-00 00:00:00';
		}
		if (!isset($this->required_delivery_time) || $this->required_delivery_time == '0' || $this->required_delivery_time == '') {
			$this->required_delivery_time = '0000-00-00 00:00:00';
		}

		return true;
	}
}
?>
