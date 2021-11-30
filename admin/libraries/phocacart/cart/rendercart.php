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
use Joomla\CMS\Layout\FileLayout;
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

		$this->setInstance(1);//cart
		parent::__construct();
	}


	public function render() {

		$pC 					= PhocacartUtils::getComponentParameters();
		$s                      = PhocacartRenderStyle::getStyles();
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

		$app	= Factory::getApplication();
		$d		= array();
		$d['s'] = $s;
		if($app->isClient('administrator')) {
			// client = 0, ask phoca cart frontend layouts
			$d['client'] = 1;//admin
			$layout 				= new FileLayout('cart_cart', null, array('component' => 'com_phocacart', 'client' => 0));

		} else {
			$d['client'] = 0;//frontend
			$layout 				= new FileLayout('cart_cart', null, array('component' => 'com_phocacart'));
		}

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

}
?>
