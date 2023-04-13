<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;

class PhocacartOrderCalculation
{

	protected $items 		= array();
	protected $total 		= array();
	protected $taxes 		= array();
	protected $currencies 	= array();
	protected $type			= array(0,1);// e-shop, POS, ...

	public function __construct() {

		$app						= Factory::getApplication();
		$paramsC					= PhocacartUtils::getComponentParameters();
		// Affect only calculation in POS cart
		// Not receipts, invoices
		// Display or hide brutto prices in POS cart
		//$this->posbruttocalculation		= PhocacartPos::isPos() ? $paramsC->get( 'pos_brutto_calculation', 1 ) : 0;
	}

	public function getItems() {
		return $this->items;
	}

	public function getTotal() {
		return $this->total;
	}

	public function getCurrencies() {
		return $this->currencies;
	}


	public function calculateOrderItems($items) {

		$itemsIdA 	= array();// array of all items IDs - only IDs - ALL ASKED ORDERS
		$itemsIdS	= '';// string of all items IDs used in SQL query IN
		$itemsId	= array();// all items sorted by ID - the ID is the key of the array
		$itemsTotal	= array();// ALL TOTAL ROWS OF EACH ASKED ORDER ITEMS

		$price		= new PhocacartPrice();
		//$price->getPriceFormat($v['amount'],0,1);

		if (!empty($items)) {
			foreach($items as $k => $v) {

				$id 			= (int)$v->id;
				$itemsIdA[] 	= $id;
				$itemsId[$id] 	= $v;
				$currencyId		= $v->currency_id;
				if ($currencyId > 0 && !isset($currency[$currencyId])) {
					$this->currencies[$currencyId] = $v->currency_code;
				}
			}

			if (!empty($itemsIdA)) {
				$itemsIdS = implode(',', $itemsIdA);
			}

			if ($itemsIdS != '') {
				$itemsTotal = PhocacartOrder::getItemsTotal($itemsIdS);
				$itemsTRC 	= PhocacartOrder::getItemsTaxRecapitulation($itemsIdS);
			}

			$this->items = $itemsId;



		/*	$itemsTotalSorted = array();
			if (!empty($itemsTotal)) {
				foreach($itemsTotal as $k => $v) {
					$id 			= $v['id'];
					$orderId 		= $v['order_id'];

					$itemsTotalSorted[$orderId][$id] = $v;
				}
			}*/


			if (!empty($itemsTotal)) {


				// Define defaults so we can add (+)
				foreach($this->currencies as $k => $v) {
					$this->total[$k]['brutto'] 		= 0;
					$this->total[$k]['netto'] 		= 0;
					$this->total[$k]['rounding'] 	= 0;
					$this->total[$k]['discount'] 	= 0;
					// no tax here as there are different rates for tax
				}


				foreach($itemsTotal as $k => $v) {



					$id 			= $v['order_id'];
					$itemId			= $v['item_id']; //for example, there can be different types of VAT: 5% 10% 20%
					$itemKey		= PhocacartTax::getTaxKey($v['item_id'], $v['item_id_c'], $v['item_id_r'], $v['item_id_p']);// but different type ov VATs can be extended through country and region TAXES
					$currencyId		= $this->items[$id]->currency_id;
					$r				= $this->items[$id]->currency_exchange_rate;



					switch($v['type']) {

						case 'brutto':
							$brutto = isset($v['amount_currency']) && $v['amount_currency'] > 0 ? $v['amount_currency'] : $v['amount'] * $r;
							if (isset($this->items[$id]->brutto)) {
								$this->items[$id]->brutto += $price->roundPrice($brutto);
							} else {
								$this->items[$id]->brutto = $price->roundPrice($brutto);
							}
							$this->total[$currencyId]['brutto'] += $price->roundPrice($brutto);
						break;


						case 'sbrutto':
						case 'pbrutto':
						case 'dbrutto':

						break;



						case 'snetto':
						case 'pnetto':
						case 'netto':

							$netto = $v['amount']*$r;
							if (isset($this->items[$id]->netto)) {
								$this->items[$id]->netto += $price->roundPrice($netto);

							} else {
								$this->items[$id]->netto = $price->roundPrice($netto);
							}
							$this->total[$currencyId]['netto'] += $price->roundPrice($netto);
						break;

						case 'rounding':

							$rounding =  isset($v['amount_currency']) && $v['amount_currency'] > 0 ? $v['amount_currency'] : $v['amount']*$r;
							if (isset($this->items[$id]->rounding)) {
								$this->items[$id]->rounding += $price->roundPrice($rounding);
							} else {
								$this->items[$id]->rounding = $price->roundPrice($rounding);
							}

							$this->total[$currencyId]['rounding'] += $price->roundPrice($rounding);
						break;

						case 'stax':
						case 'ptax':
						case 'tax':
							$tax = $v['amount']*$r;
							if (isset($this->items[$id]->tax[$itemKey])) {
								$this->items[$id]->tax[$itemKey] += $price->roundPrice($tax);
							} else {
								$this->items[$id]->tax[$itemKey] = $price->roundPrice($tax);
							}

							if (isset($this->items[$id]->taxsum)) {
								$this->items[$id]->taxsum += $price->roundPrice($tax);// sum of all taxes in one order
							} else {
								$this->items[$id]->taxsum = $price->roundPrice($tax);// sum of all taxes in one order
							}

							if (isset($this->total[$currencyId]['tax'][$itemKey])) {
								$this->total[$currencyId]['tax'][$itemKey] += $price->roundPrice($tax);
							} else {
								$this->total[$currencyId]['tax'][$itemKey] = $price->roundPrice($tax);
							}


						break;

						case 'dnetto':
							$dNetto = $v['amount']*$r;
							if (isset($this->items[$id]->discount)) {
								$this->items[$id]->discount += $price->roundPrice($dNetto);

							} else {
								$this->items[$id]->discount = $price->roundPrice($dNetto);

							}
							$this->total[$currencyId]['discount'] += $price->roundPrice($dNetto);
						break;

					}
				}

			}


			if (!empty($itemsTRC)) {


				// Define defaults so we can add (+)
				foreach($this->currencies as $k => $v) {
					$this->total[$k]['trcnetto'] 	= 0;
					$this->total[$k]['trcrounding'] = 0;
					$this->total[$k]['trcbrutto'] 	= 0;
					// no tax here as there are different rates for tax
				}


				foreach($itemsTRC as $k => $v) {



					$id 			= $v['order_id'];
					$itemId			= $v['item_id']; //for example, there can be different types of VAT: 5% 10% 20%
					$itemKey		= PhocacartTax::getTaxKey($v['item_id'], $v['item_id_c'], $v['item_id_r'], $v['item_id_p']);// but different type ov VATs can be extended through country and region TAXES
					$currencyId		= $this->items[$id]->currency_id;
					$r				= $this->items[$id]->currency_exchange_rate;



					switch($v['type']) {

						case 'tax':
							$netto 			= $v['amount_netto']*$r;
							$tax 			= $v['amount_tax']*$r;
							//$brutto 		= $v['amount_brutto']*$r;
						//	$brutto 		= isset($v['amount_brutto_currency']) && $v['amount_brutto_currency'] > 0 ? $v['amount_brutto_currency'] : $v['amount_brutto'] * $r;


							if (isset($this->items[$id]->trctax[$itemKey])) {
								$this->items[$id]->trctax[$itemKey] += $price->roundPrice($tax);
							} else {
								$this->items[$id]->trctax[$itemKey] = $price->roundPrice($tax);
							}

							if (isset($this->items[$id]->trctaxsum)) {
								$this->items[$id]->trctaxsum += $price->roundPrice($tax);// sum of all taxes in one order
							} else {
								$this->items[$id]->trctaxsum = $price->roundPrice($tax);// sum of all taxes in one order
							}

							if (isset($this->total[$currencyId]['trctax'][$itemKey])) {
								$this->total[$currencyId]['trctax'][$itemKey] += $price->roundPrice($tax);
							} else {
								$this->total[$currencyId]['trctax'][$itemKey] = $price->roundPrice($tax);
							}


							if (isset($this->items[$id]->trcnetto)) {
								$this->items[$id]->trcnetto += $price->roundPrice($netto);
							} else {
								$this->items[$id]->trcnetto = $price->roundPrice($netto);
							}

							if (isset($this->total[$currencyId]['trcnetto'])) {
								$this->total[$currencyId]['trcnetto'] += $price->roundPrice($netto);
							} else {
								$this->total[$currencyId]['trcnetto'] = $price->roundPrice($netto);
							}


						break;

						case 'brutto':

							$brutto 		= isset($v['amount_brutto_currency']) && $v['amount_brutto_currency'] > 0 ? $v['amount_brutto_currency'] : $v['amount_brutto'] * $r;

							if (isset($this->items[$id]->trcbrutto)) {
								$this->items[$id]->trcbrutto += $price->roundPrice($brutto);
							} else {
								$this->items[$id]->trcbrutto = $price->roundPrice($brutto);
							}

							if (isset($this->total[$currencyId]['trcbrutto'])) {
								$this->total[$currencyId]['trcbrutto'] += $price->roundPrice($brutto);
							} else {
								$this->total[$currencyId]['trcbrutto'] = $price->roundPrice($brutto);
							}



						break;


						case 'trcrounding':

							$rounding 		= isset($v['amount_brutto_currency']) && $v['amount_brutto_currency'] > 0 ? $v['amount_brutto_currency'] : $v['amount_brutto'] * $r;


							if (isset($this->items[$id]->trcrounding)) {
								$this->items[$id]->trcrounding += $price->roundPrice($rounding);
							} else {
								$this->items[$id]->trcrounding = $price->roundPrice($rounding);
							}

							if (isset($this->total[$currencyId]['trcrounding'])) {
								$this->total[$currencyId]['trcrounding'] += $price->roundPrice($rounding);
							} else {
								$this->total[$currencyId]['trcrounding'] = $price->roundPrice($rounding);
							}

						break;

						/*case 'brutto':
							$brutto = isset($v['amount_brutto_currency']) && $v['amount_brutto_currency'] > 0 ? $v['amount_brutto_currency'] : $v['amount_brutto'] * $r;
							if (isset($this->items[$id]->trcbrutto)) {
								$this->items[$id]->trcbrutto += $brutto;
							} else {
								$this->items[$id]->trcbrutto = $brutto;
							}
							$this->total[$currencyId]['trcbrutto'] += $brutto;
						break;



						case 'netto':

							$netto = $v['amount_netto']*$r;
							if (isset($this->items[$id]->trcnetto)) {
								$this->items[$id]->trcnetto += $netto;

							} else {
								$this->items[$id]->trcnetto = $netto;
							}
							$this->total[$currencyId]['trcnetto'] += $netto;

						break;

						case 'rounding':

							$rounding = isset($v['amount_brutto_currency']) && $v['amount_brutto_currency'] > 0 ? $v['amount_brutto_currency'] : $v['amount_brutto'] * $r;
							if (isset($this->items[$id]->trcrounding)) {
								$this->items[$id]->trcrounding += $rounding;
							} else {
								$this->items[$id]->trcrounding = $rounding;
							}

							$this->total[$currencyId]['trcrounding'] += $rounding;
						break;


						case 'tax':
							$tax = $v['amount_tax']*$r;
							if (isset($this->items[$id]->trctax[$itemKey])) {
								$this->items[$id]->trctax[$itemKey] += $tax;
							} else {
								$this->items[$id]->trctax[$itemKey] = $tax;
							}

							if (isset($this->items[$id]->trctaxsum)) {
								$this->items[$id]->trctaxsum += $tax;// sum of all taxes in one order
							} else {
								$this->items[$id]->trctaxsum = $tax;// sum of all taxes in one order
							}

							if (isset($this->total[$currencyId]['trctax'][$itemKey])) {
								$this->total[$currencyId]['trctax'][$itemKey] += $tax;
							} else {
								$this->total[$currencyId]['trctax'][$itemKey] = $tax;
							}


						break;

					/*	case 'dnetto':
							$dNetto = $v['amount']*$r;
							if (isset($this->items[$id]->discount)) {
								$this->items[$id]->discount += $dNetto;

							} else {
								$this->items[$id]->discount = $dNetto;

							}
							$this->total[$currencyId]['discount'] += $dNetto;
						break;*/

					}
				}

			}

			return true;
		}

	}
}
?>
