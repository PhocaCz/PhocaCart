<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartAccess
{
	public $login 			= 0; // 0 ... not logged in, 1 ... logged in, 2 ... guest checkout
	public $addressadded 	= 0;
	public $addressedit 	= 0;
	public $addressview		= 0;
	public $shippingadded 	= 0;
	public $shippingedit 	= 0;
	public $shippingview	= 0;
	public $shippingnotused	= 0;
	public $paymentadded 	= 0;
	public $paymentedit 	= 0;
	public $paymentview		= 0;
	public $paymentnotused	= 0;
	public $confirm			= 0;

	public function __construct() {
		
		$this->login			= 0;// User is only logged in
		$this->addressadded 	= 0;// Address added and stored without errors
		$this->addressedit		= 0;// Address will be edited
		$this->addressview		= 0;
		$this->shippingadded	= 0;
		$this->shippingedit		= 0;
		$this->shippingview		= 0;
		$this->shippingnotused	= 0;
		$this->paymentadded		= 0;
		$this->paymentedit		= 0;
		$this->paymentview		= 0;
		$this->paymentnotused	= 0;
		$this->confirm			= 0;
	}
}
?>