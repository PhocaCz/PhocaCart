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

class PhocaCartCpModelPhocacartContentType extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	public function getTable($type = 'PhocacartContentType', $prefix = 'Table', $config = [])
    {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
    {
		return $this->loadForm('com_phocacart.phocacartcontenttype', 'phocacartcontenttype', ['control' => 'jform', 'load_data' => $loadData]);
	}

	protected function loadFormData()
    {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartcontenttype.data', []);
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}

	protected function prepareTable($table)
    {
		$table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);

		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db = $this->getDatabase();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_content_types');
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}
}
