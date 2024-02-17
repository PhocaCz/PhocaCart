<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;

class PhocaCartCpModelPhocacartWishlist extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	public function getTable($type = 'PhocacartWishlist', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		return $this->loadForm('com_phocacart.phocacartwishlist', 'phocacartwishlist', array('control' => 'jform', 'load_data' => $loadData));
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartwishlist.data', []);

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		$table->title		= htmlspecialchars_decode((string)$table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		$table->date 				= PhocacartUtils::getDateFromString($table->date);
		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_wishlists WHERE user_id = '. (int) $table->user_id);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'user_id = '. (int) $table->user_id;
		return $condition;
	}
}

