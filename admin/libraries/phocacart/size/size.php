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

class PhocacartSize
{
    public $p;

    public function __construct() {
        $this->p    = PhocacartUtils::getComponentParameters();
    }

    public function getSizeFormat($value, $type = 'size') {

        $suffix = '';
        if ($value > 0) {

            switch($type) {

                case 'weight':
                    $suffix = $this->p->get('unit_weight');
                break;

                case 'volume':
                    $suffix = $this->p->get('unit_volume');
                break;
                case 'size':
                default:
                    $suffix = $this->p->get('unit_size');
                break;

            }

            return $this->roundSize($value). ' '.$suffix;

        }

        return false;

    }

    public function roundSize($value) {

        // Possible parameter
        //$paramsC 					= PhocacartUtils::getComponentParameters();
        $rounding_size		        = 2;//$paramsC->get( 'rounding_size', 2 );
        return round($value, $rounding_size, 2);
    }
}
