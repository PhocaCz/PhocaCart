<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
jimport('joomla.filter.input');

class TablePhocacartRewardPoint extends Table
{
	function __construct(& $db) {
		parent::__construct('#__phocacart_reward_points', 'id', $db);
	}

	function check() {
		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = PhocacartUtils::getAliasName($this->alias);

		if ((int) $this->date == 0)
		{
			$this->date = Factory::getDate()->toSql();
		}

		return true;
	}
}
?>
