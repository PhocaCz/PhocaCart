<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartCartRendercheckout extends PhocacartCart
{
	protected $fullitems;
	protected $total;
	public $params;
	
	
	public function __construct() {
		parent::__construct();
	}
	
	
	public function render() {
		
		$app					= JFactory::getApplication();
		$pC 					= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$uri 					= JFactory::getURI();
		$url['action']			= $uri->toString();
		$url['actionbase64']	= base64_encode($url['action']);
		$url['linkcheckout']	= JRoute::_(PhocacartRoute::getCheckoutRoute());
		
		
		if (empty($this->fullitems)) {
			$this->fullitems = $this->getFullItems();// get them from parent
			
			// SUBTOTAL
			if (empty($this->total)) {
				$this->total = $this->getTotal();
			}
		
			// COUPONTITLE
			if (empty($this->coupon['title'])) {
				$this->coupon['title'] = $this->getCouponTitle();
			}
		}

		$layout 				= new JLayoutFile('cart_checkout', null, array('component' => 'com_phocacart'));
		$d						= array();
		$d['params']			= $pC;
		$d['fullitems']			= $this->fullitems;
		$d['total']				= $this->total;
		$d['fullitemsgroup']	= $this->fullitemsgroup;
		$d['coupontitle']		= $this->coupon['title'];
		$d['couponvalid']		= $this->coupon['valid'];
		$d['shippingcosts']		= $this->shipping['costs'];
		$d['paymentcosts']		= $this->payment['costs'];
		$d['action']			= $url['action'];
		$d['actionbase64']		= $url['actionbase64'];
		$d['linkcheckout']		= $url['linkcheckout'];

		return $layout->render($d);	
	}
}
?>