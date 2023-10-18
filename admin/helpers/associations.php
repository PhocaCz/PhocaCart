<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Associations;

use Joomla\CMS\Association\AssociationExtensionHelper;

Table::addIncludePath(__DIR__ . '/../tables');

/**
 * Content associations helper.
 *
 * @since  3.7.0
 */
class PhocaCartAssociationsHelper extends AssociationExtensionHelper
{
	/**
	 * The extension name
	 *
	 * @var     array   $extension
	 *
	 * @since   3.7.0
	 */
	protected $extension = 'com_phocacart';

	/**
	 * Array of item types
	 *
	 * @var     array   $itemTypes
	 *
	 * @since   3.7.0
	 */
	protected $itemTypes = array('phocacartitem', 'phocacartcategory', 'phocacartmanufacturer');

	/**
	 * Has the extension association support
	 *
	 * @var     boolean   $associationsSupport
	 *
	 * @since   3.7.0
	 */
	protected $associationsSupport = true;

	/**
	 * Get the associated items for an item
	 *
	 * @param   string  $typeName  The item type
	 * @param   int     $id        The id of item for which we need the associated items
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public function getAssociations($typeName, $id)
	{
		$type = $this->getType($typeName);



		$context    = 'com_phocacart.item';
		$catidField = '';


		if ($typeName === 'phocacartcategory') {
			$context    = 'com_phocacart.category';
		}

    if ($typeName === 'phocacartmanufacturer') {
      $context    = 'com_phocacart.manufacturer';
    }

		// Get the associations.
		$associations = Associations::getAssociations(
			$this->extension,
			$type['tables']['a'],
			$context,
			$id,
			'id',
			'alias',
			$catidField
		);

		return $associations;
	}

	/**
	 * Get item information
	 *
	 * @param   string  $typeName  The item type
	 * @param   int     $id        The id of item for which we need the associated items
	 *
	 * @return  JTable|null
	 *
	 * @since   3.7.0
	 */
	public function getItem($typeName, $id)
	{
		if (empty($id))
		{
			return null;
		}

		$table = null;

		switch ($typeName)
		{
			case 'phocacartitem':
				$table = Table::getInstance('PhocacartItem', 'Table');
				break;

			case 'phocacartcategory':
				$table = Table::getInstance('PhocacartCategory', 'Table');
				break;

      case 'phocacartmanufacturer':
        $table = Table::getInstance('PhocacartManufacturer', 'Table');
        break;
		}

		if (empty($table))
		{
			return null;
		}

		$table->load($id);

		return $table;
	}

	/**
	 * Get information about the type
	 *
	 * @param   string  $typeName  The item type
	 *
	 * @return  array  Array of item types
	 *
	 * @since   3.7.0
	 */
	public function getType($typeName = '')
	{
		$fields  = $this->getFieldsTemplate();
		$tables  = array();
		$joins   = array();
		$support = $this->getSupportTemplate();
		$title   = '';

		if (in_array($typeName, $this->itemTypes))
		{
			switch ($typeName)
			{
				case 'phocacartitem':
					$fields['created_user_id'] = false;
					$fields['title'] = 'a.title';
					$fields['state'] = 'a.published';

					$support['state'] = true;
					$support['acl'] = true;
					$support['checkout'] = true;
					$support['category'] = true;
					$support['save2copy'] = true;

					$tables = array(
						'a' => '#__phocacart_products'
					);

					$title = 'product';
					break;

				case 'phocacartcategory':
					$fields['created_user_id'] = false;
					//$fields['created_user_id'] = 'a.created_user_id';
					//$fields['ordering'] = 'a.lft';
					//$fields['level'] = 'a.level';
					$fields['catid'] = '';
					$fields['state'] = 'a.published';

					$support['state'] = true;
					$support['acl'] = true;
					$support['checkout'] = true;
					$support['level'] = true;

					$tables = array(
						'a' => '#__phocacart_categories'
					);

					$title = 'category';
					break;

        case 'phocacartmanufacturer':
          $fields['created_user_id'] = false;
          $fields['catid'] = '';
          $fields['state'] = 'a.published';

          $support['state'] = true;
          $support['acl'] = false;
          $support['checkout'] = true;
          $support['level'] = false;

          $tables = array(
            'a' => '#__phocacart_manufacturers'
          );

          $title = 'manufacturer';
          break;
			}
		}

		return array(
			'fields'  => $fields,
			'support' => $support,
			'tables'  => $tables,
			'joins'   => $joins,
			'title'   => $title
		);
	}

  public function getAssociationsForItem($id = 0, $view = null) {
  }
}
