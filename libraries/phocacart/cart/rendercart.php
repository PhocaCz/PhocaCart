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
/*
phocacart import('phocacart.cart.cart');
phocacart import('phocacart.price.price');
*/
class PhocacartCartRendercart extends PhocacartCart
{

	protected $fullitems;
	protected $total;
	public $params;

	public function __construct() {
		
		parent::__construct();
	}
	
	
	public function render() {
		
		$pC 					= PhocacartUtils::getComponentParameters();
		
		if (empty($this->fullitems)) {
			$this->fullitems = $this->getFullItems();// get them from parent
		}

		if (!empty($this->fullitems)) {

			// SUBTOTAL
			if (empty($this->total)) {
				$this->total = $this->getTotal();
			}
			
			// COUPONTITLE
			if (empty($this->coupon['title'])) {
				$this->coupon['title'] = $this->getCouponTitle();
			}
		}
		
		// Final Brutto
	/*	if ($this->total['brutto']) {
			$this->total['fbrutto'] = $this->total['brutto'];
			if ($this->couponvalid) {
				$this->total['fbrutto'] = $this->total['brutto'] - $this->total['cbrutto'];
			}
		}*/
		
		
		$layout 				= new JLayoutFile('cart_cart', null, array('component' => 'com_phocacart'));
		$d						= array();
		$d['paramsmodule']		= $this->params; // Module Parameters
		$d['params']			= $pC; // Component Parameters
		$d['fullitems']			= $this->fullitems;
		$d['total']				= $this->total;
		$d['fullitemsgroup']	= $this->fullitemsgroup;
		$d['coupontitle']		= $this->coupon['title'];
		$d['couponvalid']		= $this->coupon['valid'];
		$d['shippingcosts']		= $this->shipping['costs'];
		$d['paymentcosts']		= $this->payment['costs'];
		$d['countitems']		= $this->getCartCountItems();
		//$d['action']			= $url['action'];
		//$d['actionbase64']		= $url['actionbase64'];
		//$d['linkcheckout']		= $url['linkcheckout'];
		$d['pathitem'] 			= PhocacartPath::getPath('productimage');

		return $layout->render($d);
	}
	
	
	public function getCartCountItems() {
		
		
		if (empty($this->fullitems)) {
			$this->fullitems = $this->getFullItems();// get them from parent
		}
		
		$count = 0;
		if (!empty($this->fullitems[0])) {
			foreach($this->fullitems[0] as $k => $v) {
				if (isset($v['quantity']) && (int)$v['quantity'] > 0) {
					$count += (int)$v['quantity'];
				}
			}
		}
		return $count;
	}
	
	
	public function getCartTotalItems() {
		
		// SUBTOTAL
		if (empty($this->total)) {
			$this->total = $this->getTotal();
		}
		
		// COUPONTITLE
		if (empty($this->coupontitle)) {
			$this->coupon['title'] = $this->getCouponTitle();
		}
		return $this->total;
	}
}
?>