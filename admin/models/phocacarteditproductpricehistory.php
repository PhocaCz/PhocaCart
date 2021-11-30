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
jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocaCartEditProductPriceHistory extends AdminModel
{
	protected	$option 		        = 'com_phocacart';
	protected 	$text_prefix	        = 'com_phocacart';
	public      $typeAlias 		        = 'com_phocacart.phocacartpricehistory';


	public function getTable($type = 'PhocacartProductPriceHistory', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {

		$app	= Factory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartpricehistory', 'phocacartpricehistory', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartpricehistory.data', array());

		if (empty($data)) {
			$data = $this->getItem();

		}

		return $data;
	}


	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {

			$history =  PhocacartPriceHistory::getPriceHistoryById((int)$item->product_id, 0, 1);
			if (!empty($history)) {
				foreach($history as $k => $v) {
					$history[$k]['price'] = PhocacartPrice::cleanPrice($v['price']);
				}
			}

			$item->set('price_history', $history);


		}

		return $item;
	}

	protected function prepareTable($table) {
		jimport('joomla.filter.output');

		$table->price 					= PhocacartUtils::replaceCommaWithPoint($table->price);


	}


	public function save($data/*, $productId*/) {

		$app					= Factory::getApplication();
		$productId				= $app->input->get('id', 0, 'int');



		if (!empty($data)) {
			return PhocacartPriceHistory::storePriceHistoryCustomById($data['price_history'], $productId);
		}
	}
}
?>
