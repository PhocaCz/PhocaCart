<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\MVC\Model\ListModel;
use Phoca\PhocaCart\Container\Container;

class PhocaCartCpModelPhocaCartExports extends ListModel
{
	protected $option 	= 'com_phocacart';

	public function getItemsCountProduct()
    {
        $db = Container::getDbo();
        $db->setQuery('SELECT COUNT(id) FROM #__phocacart_products');

        return $db->loadResult();
    }

	public function getItemsCountExport() {
        $db = Container::getDbo();
		$user	= Container::getUser();
		$db->setQuery('SELECT COUNT(id) FROM #__phocacart_export WHERE user_id = ' . (int)$user->id . ' AND type = 0');

		return $db->loadResult();
	}
}
