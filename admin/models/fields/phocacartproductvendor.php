<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

class JFormFieldPhocacartProductVendor extends ListField
{
	protected $type = 'PhocacartProductVendor';

	protected function getOptions()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, title AS text')
			->from('#__phocacart_vendors')
			->where('type = 1')
			->order('ordering, id');
		$db->setQuery($query);
		$options= $db->loadObjectList();
		return array_merge(parent::getOptions(), $options);
	}
}
