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
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\String\StringHelper;

class PhocacartUtilsAdmindescription
{
    public $display_admin_description;

    public function __construct() {
        $pC							        = PhocacartUtils::getComponentParameters();
        $this->display_admin_description	= $pC->get( 'display_admin_description', 35 );
    }

    public function isActive() {

	    if ((int)$this->display_admin_description > 0) {
	        return true;
        }
	    return false;
    }

    public function getAdminDescription($description) {

        $description = strip_tags($description ?? '');

        if (StringHelper::strlen($description) < $this->display_admin_description || StringHelper::strlen($description) == $this->display_admin_description) {
            return $description;
        } else {
            return StringHelper::substr($description, 0, $this->display_admin_description) . ' ...';
        }
    }
}

