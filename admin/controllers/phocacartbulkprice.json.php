<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocaCartBulkprice extends PhocaCartCpControllerPhocaCartCommon {


	public function run() {

		if (!Session::checkToken('request')) {
			$response = array('status' => '0', 'output' => '<div class="alert alert-error">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}

		$app		= Factory::getApplication();
		$db			= Factory::getDBO();
		$paramsC 	= PhocacartUtils::getComponentParameters();
		$price		= new PhocacartPrice();
		$continue 	= 0;
		$pagination = 5;

		$page		= $app->input->get('p', 0, 'int');
		$id			= $app->input->get('id', 0, 'int');

		// BULK PRICE
		$item 							= PhocacartPriceBulkprice::getItem($id);
		$item_save_price_history_run    = $item->params->get('save_price_history_run', 1);

		if (isset($item->status) && $item->status == 1) {
			$response = array('status' => '1', 'output' => '<div class="alert alert-error">' . Text::_('COM_PHOCACART_THIS_BULK_PRICE_JOB_HAS_ALREADY_BEEN_RUN_AND_IS_ACTIVE') . '</div>');
			echo json_encode($response);
			return;
		}

		$limitOffset 	= ((int)$page * (int)$pagination) - (int)$pagination;
		if ($limitOffset < 0) {
			$limitOffset = 0;
		}
		$limitCount		= $pagination;
        $wheres 		= array();
		$lefts 			= array();

        if (!empty($item->categories)) {
			$wheres[]	= ' c.id IN ('.$item->categories_string.')';
			$lefts[] = ' #__phocacart_product_categories AS pc ON pc.product_id = p.id';
			$lefts[] = ' #__phocacart_categories AS c ON c.id = pc.category_id';
		}

        $q = 'SELECT p.id, p.title, p.price, p.price_original';
        $q .= ' FROM #__phocacart_products AS p';

        if (!empty($lefts)) {
        	$q .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $lefts);
		}

        if (!empty($wheres)) {
            $q .= ' WHERE ' . implode(' AND ', $wheres);
        }
        $q .= ' ORDER BY p.id';
        if ((int)$limitCount > 0) {
            $q .= ' LIMIT ' . (int)$limitOffset . ', ' . (int)$limitCount;
        }

        $db->setQuery($q);
        $products = $db->loadAssocList();


        $o = array();

        if (!empty($products)) {
            $count = count($products);
            if ($count == $pagination) {
                // Pagination full, continue with adding next round of products
                $continue = 1;
            }
            foreach ($products as $k => $v) {

            	$o[] = '<div class="ph-bulk-price-item alert alert-info">';
            	if (isset($v['title'])) {
                    $o[] = '<div class="ph-bulk-price-title">'.$v['title'].'</div>';
                }

            	if (isset($v['price']) ) {

            		$newPrice = PhocacartPriceBulkprice::setNewPrice($v['id'], $v['price'], $item->params);
            		$o[] = '<div class="ph-bulk-price-price">' . Text::_('COM_PHOCACART_PRICE') . ': <b>'. $price->getPriceFormat($v['price']) . '</b> <span class="ph-bulk-price-arrow">&rarr;</span> <b>' .$price->getPriceFormat($newPrice). '</b></div>';
				}

            	if (isset($v['price_original']) ) {

            		$newPriceOriginal = PhocacartPriceBulkprice::setNewOriginalPrice($v['id'], $v['price_original'], $v['price'], $item->params);
            		$o[] = '<div class="ph-bulk-price-original-price">' . Text::_('COM_PHOCACART_ORIGINAL_PRICE') . ': <b>'. $price->getPriceFormat($v['price_original']) . '</b> <span class="ph-bulk-price-arrow">&rarr;</span> <b>' .$price->getPriceFormat($newPriceOriginal). '</b></div>';
				}

            	if (isset($v['price']) && isset($v['price_original'])) {

            		// Price history
					// 0 ... display in price history (standard change of price)
					// 1 ... display in price history (bulk price change)
					// 2 ... don't display in price history (only bulk price info for possible revert) ... specific type of price history
					//
					// BE AWARE
					// Standard price history (0,1) is applied only once a day
					// Bulk price history (2) can have more items per day

					// Price history table is used for storing standard price and bulk price
					// So when storing bulk price, there can be two records: one for bulk price (2), second for standard price (1)
					// Both are independent (2) is used because of revert, (1) one is used because of price history, updated only once a day

            		// Bulk price history
					$type = 2;
            		PhocacartPriceHistory::storePriceHistoryBulkPriceById($v['id'], $newPrice, $newPriceOriginal, $id, $v['price'], $v['price_original'], $type);

            		// Standard price history
					if ($item_save_price_history_run == 1) {
						$type = 1;
						PhocacartPriceHistory::storePriceHistoryById((int)$v['id'], $newPrice, $type);
					}

					// Update group price
					PhocacartGroup::updateGroupProductPriceById((int)$v['id'], $newPrice);


				}
            	$o[] = '</div>';
            }
        }

        $output = implode('', $o);

        $oFinished = array();
        if ($continue == 0) {

        	// Job finished
        	$oFinished[] = '<div class="ph-bulk-price-item alert alert-success">';
        	$oFinished[] = Text::_('COM_PHOCACART_BULK_PRICE_CHANGE_FINISHED');
        	$oFinished[] = '</div>';
        	$output = implode('', $oFinished) . $output;

        	PhocacartPriceBulkprice::setStatus($id, 1);

		}

        $response = array('status' => '1', 'output' => $output, 'continue' => $continue);
		echo json_encode($response);
		return;

	}


	public function revert() {

		if (!Session::checkToken('request')) {
			$response = array('status' => '0', 'output' => '<div class="alert alert-error">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}

		$app		= Factory::getApplication();
		$db			= Factory::getDBO();
		$paramsC 	= PhocacartUtils::getComponentParameters();
		$price		= new PhocacartPrice();
		$continue 	= 0;
		$pagination = 5;

		$page		= $app->input->get('p', 0, 'int');
		$id			= $app->input->get('id', 0, 'int');

		// BULK PRICE
		$item 							= PhocacartPriceBulkprice::getItem($id);
		$item_save_price_history_revert = $item->params->get('save_price_history_revert', 1);

		if (isset($item->status) && $item->status == 0) {
			$response = array('status' => '1', 'output' => '<div class="alert alert-error">' . Text::_('COM_PHOCACART_THIS_BULK_PRICE_JOB_HAS_NOT_BEEN_RUN_AND_IS_NOT_ACTIVE') . '</div>');
			echo json_encode($response);
			return;
		}

		$limitOffset 	= ((int)$page * (int)$pagination) - (int)$pagination;
		if ($limitOffset < 0) {
			$limitOffset = 0;
		}
		$limitCount		= $pagination;
        $wheres 		= array();
		$lefts 			= array();

        if (!empty($item->categories)) {
			$wheres[]	= ' c.id IN ('.$item->categories_string.')';
			$lefts[] = ' #__phocacart_product_categories AS pc ON pc.product_id = p.id';
			$lefts[] = ' #__phocacart_categories AS c ON c.id = pc.category_id';
		}

        $q = 'SELECT p.id, p.title, p.price, p.price_original';
        $q .= ' FROM #__phocacart_products AS p';

        if (!empty($lefts)) {
        	$q .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $lefts);
		}

        if (!empty($wheres)) {
            $q .= ' WHERE ' . implode(' AND ', $wheres);
        }
        $q .= ' ORDER BY p.id';
        if ((int)$limitCount > 0) {
            $q .= ' LIMIT ' . (int)$limitOffset . ', ' . (int)$limitCount;
        }

        $db->setQuery($q);
        $products = $db->loadAssocList();


        $o = array();

        if (!empty($products)) {
            $count = count($products);
            if ($count == $pagination) {
                // Pagination full, continue with adding next round of products
                $continue = 1;
            }
            foreach ($products as $k => $v) {

            	$o[] = '<div class="ph-bulk-price-item alert alert-info">';
            	if (isset($v['title'])) {
                    $o[] = '<div class="ph-bulk-price-title">'.$v['title'].'</div>';
                }

            	if (isset($v['price']) ) {

            		$newPrice = PhocacartPriceBulkprice::setRevertPrice($v['id'], $id, $v['price'], $item->params);
            		$o[] = '<div class="ph-bulk-price-price">' . Text::_('COM_PHOCACART_PRICE') . ': <b>'. $price->getPriceFormat($v['price']) . '</b> <span class="ph-bulk-price-arrow">&rarr;</span> <b>' .$price->getPriceFormat($newPrice). '</b></div>';
				}

            	if (isset($v['price_original']) ) {

            		$newPriceOriginal = PhocacartPriceBulkprice::setRevertOriginalPrice($v['id'], $id, $v['price_original'], $v['price'], $item->params);
            		$o[] = '<div class="ph-bulk-price-original-price">' . Text::_('COM_PHOCACART_ORIGINAL_PRICE') . ': <b>'. $price->getPriceFormat($v['price_original']) . '</b> <span class="ph-bulk-price-arrow">&rarr;</span> <b>' .$price->getPriceFormat($newPriceOriginal). '</b></div>';
				}

            	if (isset($v['price']) && isset($v['price_original'])) {

            		// Price history
					// 0 ... display in price history (standard change of price)
					// 1 ... display in price history (bulk price change)
					// 2 ... don't display in price history (only bulk price info for possible revert) ... specific type of price history
					//
					// BE AWARE
					// Standard price history (0,1) is applied only once a day
					// Bulk price history (2) can have more items per day

            		// Bulk price history
					$type = 2;
            		PhocacartPriceHistory::storePriceHistoryBulkPriceById($v['id'], $newPrice, $newPriceOriginal, $id, $v['price'], $v['price_original'], $type);

            		// Standard price history
					if ($item_save_price_history_revert == 1) {
						$type = 1;
						PhocacartPriceHistory::storePriceHistoryById((int)$v['id'], $newPrice, $type);
					}

					// Update group price
					PhocacartGroup::updateGroupProductPriceById((int)$v['id'], $newPrice);


				}
            	$o[] = '</div>';
            }
        }

        $output = implode('', $o);

        $oFinished = array();
        if ($continue == 0) {

        	// Job finished
        	$oFinished[] = '<div class="ph-bulk-price-item alert alert-success">';
        	$oFinished[] = Text::_('COM_PHOCACART_BULK_PRICE_CHANGE_FINISHED');
        	$oFinished[] = '</div>';
        	$output = implode('', $oFinished) . $output;

        	PhocacartPriceBulkprice::setStatus($id, 0);
        	PhocacartPriceBulkprice::removePriceHistoryItem($id);

		}

        $response = array('status' => '1', 'output' => $output, 'continue' => $continue);
		echo json_encode($response);
		return;

	}
}
?>
