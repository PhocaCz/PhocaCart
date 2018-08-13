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

class PhocacartOrderCalculation
{
	
	protected $items 		= array();
	protected $total 		= array();
	protected $taxes 		= array();
	protected $currencies 	= array();
	protected $type			= array(0,1);// e-shop, POS, ...
	
	public function __construct() {
		
		$app						= JFactory::getApplication();
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
		
		//$price		= new PhocacartPrice();
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
					$currencyId		= $this->items[$id]->currency_id;
					$r				= $this->items[$id]->currency_exchange_rate;
					
		
					
					switch($v['type']) {
						
						case 'brutto':
							$brutto = isset($v['amount_currency']) && $v['amount_currency'] > 0 ? $v['amount_currency'] : $v['amount'] * $r;
							if (isset($this->items[$id]->brutto)) {
								$this->items[$id]->brutto += $brutto;
							} else {
								$this->items[$id]->brutto = $brutto;
							}
							$this->total[$currencyId]['brutto'] += $brutto;
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
								$this->items[$id]->netto += $netto;
								
							} else {
								$this->items[$id]->netto = $netto;
							}
							$this->total[$currencyId]['netto'] += $netto;
						break;
						
						case 'rounding':
							
							$rounding =  isset($v['amount_currency']) && $v['amount_currency'] > 0 ? $v['amount_currency'] : $v['amount']*$r;
							if (isset($this->items[$id]->rounding)) {
								$this->items[$id]->rounding += $rounding;
							} else {
								$this->items[$id]->rounding = $rounding;	
							}
							
							$this->total[$currencyId]['rounding'] += $rounding;
						break;
						
						case 'stax':
						case 'ptax':
						case 'tax':
							$tax = $v['amount']*$r;
							if (isset($this->items[$id]->tax[$itemId])) {
								$this->items[$id]->tax[$itemId] += $tax;
							} else {
								$this->items[$id]->tax[$itemId] = $tax;
							}
							
							if (isset($this->items[$id]->taxsum)) {
								$this->items[$id]->taxsum += $tax;// sum of all taxes in one order
							} else {
								$this->items[$id]->taxsum = $tax;// sum of all taxes in one order
							}
							
							if (isset($this->total[$currencyId]['tax'][$itemId])) {
								$this->total[$currencyId]['tax'][$itemId] += $tax;
							} else {
								$this->total[$currencyId]['tax'][$itemId] = $tax;
							}
							
							
						break;
						
						case 'dnetto':
							$dNetto = $v['amount']*$r;
							if (isset($this->items[$id]->discount)) {
								$this->items[$id]->discount += $dNetto;
								
							} else {
								$this->items[$id]->discount = $dNetto;
								
							}
							$this->total[$currencyId]['discount'] += $dNetto;
						break;
						
					}
				}
				
				return true;
				
			}
			
			return false;
		}
		
	}
}
?>