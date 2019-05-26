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

class PhocacartAccess
{
	public $login 			= 0; // 0 ... not logged in, 1 ... logged in, 2 ... guest checkout
	public $addressadded 	= 0;
	public $addressedit 	= 0;
	public $addressview		= 0;
	public $shippingadded 	= 0;
	public $shippingedit 	= 0;
	public $shippingview	= 0;
	public $shippingnotused	= 0;
    public $shippingdisplayeditbutton = 1;
	public $paymentadded 	= 0;
	public $paymentedit 	= 0;
	public $paymentview		= 0;
	public $paymentnotused	= 0;
    public $paymentdisplayeditbutton = 1;
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
        $this->shippingdisplayeditbutton = 1;// if shipping method is only one and automatically selected then set to zero
		$this->paymentadded		= 0;
		$this->paymentedit		= 0;
		$this->paymentview		= 0;
		$this->paymentnotused	= 0;
        $this->paymentdisplayeditbutton = 1;// if payment method is only one and automatically selected then set to zero
		$this->confirm			= 0;
	}
}
?>
