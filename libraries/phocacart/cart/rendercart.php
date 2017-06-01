<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
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
		$d['params']			= $this->params;
		$d['fullitems']			= $this->fullitems;
		$d['total']				= $this->total;
		$d['coupontitle']		= $this->coupon['title'];
		$d['couponvalid']		= $this->coupon['valid'];
		$d['shippingcosts']		= $this->shipping['costs'];
		$d['paymentcosts']		= $this->payment['costs'];

		return $layout->render($d);
	}
	
	
	public function getCartCountItems() {
		
		
		if (empty($this->fullitems)) {
			$this->fullitems = $this->getFullItems();// get them from parent
		}
		return count($this->fullitems[0]);
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