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

class PhocacartAccessRights
{
	public $user    = false;
	public $params  = array();


	public function __construct() {

		$this->user         = JFactory::getUser();
        $this->params 		= PhocacartUtils::getComponentParameters() ;

	}

	public function canDisplayPrice() {


	    $display_price                  = $this->params->get('display_price', 1);
	    $display_price_access_levels    = $this->params->get('display_price_access_levels', '');


        // 0) Display price for no one
        if ($display_price == 0) {
            return false;
        }

        // 1) Display price for all
        if ($display_price == 1) {
            return true;
        }

        // 2) Based on access levels
        if ($display_price == 2) {
            $levels = $this->user->getAuthorisedViewLevels();
            if (!is_array($display_price_access_levels)) {
                if (in_array((int)$display_price_access_levels, $levels)) {
                    return true;
                }
            } else {
                if (count(array_intersect($display_price_access_levels, $levels))) {
                    return true;
                }
            }
            return false;
        }

        // 3) Based on customer group
        // If user is inside at least on customer group which allows displaying the price, the price can be displayed for him
        if ($display_price == 3) {
            $userGroups = PhocacartGroup::getGroupsById($this->user->id, 1, 2);
            if (!empty($userGroups)) {
                foreach ($userGroups as $k => $v) {
                    if ($v['display_price'] == 1 && $v['published'] == 1) {
                        return true;
                    }
                }
            }
            return false;
        }

        return true; // As default, display prices

    }

    public function canDisplayAddtocart() {


        $display_addtocart                  = $this->params->get('display_addtocart', 1);
        $display_addtocart_access_levels    = $this->params->get('display_addtocart_access_levels', '');


        // 0) Display price for no one
        if ($display_addtocart == 0) {
            return false;
        }

        // 1) Display price for all
        if ($display_addtocart == 1) {
            return true;
        }

        // 2) Based on access levels
        if ($display_addtocart == 2) {
            $levels = $this->user->getAuthorisedViewLevels();
            if (!is_array($display_addtocart_access_levels)) {
                if (in_array((int)$display_addtocart_access_levels, $levels)) {
                    return true;
                }
            } else {
                if (count(array_intersect($display_addtocart_access_levels, $levels))) {
                    return true;
                }
            }
            return false;
        }

        // 3) Based on customer group
        // If user is inside at least on customer group which allows displaying the add to cart, the add to cart can be displayed for him
        if ($display_addtocart == 3) {
            $userGroups = PhocacartGroup::getGroupsById($this->user->id, 1, 2);
            if (!empty($userGroups)) {
                foreach ($userGroups as $k => $v) {
                    if ($v['display_addtocart'] == 1 && $v['published'] == 1) {
                        return true;
                    }
                }
            }
            return false;
        }

        return true; // As default, display add to cart

    }

    public function canDisplayAttributes() {


        $display_attributes                  = $this->params->get('display_attributes', 1);
        $display_attributes_access_levels    = $this->params->get('display_attributes_access_levels', '');


        // 0) Display price for no one
        if ($display_attributes == 0) {
            return false;
        }

        // 1) Display price for all
        if ($display_attributes == 1) {
            return true;
        }

        // 2) Based on access levels
        if ($display_attributes == 2) {
            $levels = $this->user->getAuthorisedViewLevels();
            if (!is_array($display_attributes_access_levels)) {
                if (in_array((int)$display_attributes_access_levels, $levels)) {
                    return true;
                }
            } else {
                if (count(array_intersect($display_attributes_access_levels, $levels))) {
                    return true;
                }
            }
            return false;
        }

        // 3) Based on customer group
        // If user is inside at least on customer group which allows displaying the attributes, the attributes can be displayed for him
        if ($display_attributes == 3) {
            $userGroups = PhocacartGroup::getGroupsById($this->user->id, 1, 2);
            if (!empty($userGroups)) {
                foreach ($userGroups as $k => $v) {
                    if ($v['display_attributes'] == 1 && $v['published'] == 1) {
                        return true;
                    }
                }
            }
            return false;
        }

        return true; // As default, display attributes

    }
}
?>
