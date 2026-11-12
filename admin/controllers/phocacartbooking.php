<?php
/**
 * @package    phocacart
 * @subpackage Controllers
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controllers/phocacartcommon.php';

class PhocaCartCpControllerPhocaCartBooking extends PhocaCartCpControllerPhocaCartCommon
{
    protected $view_item = 'phocacartbooking';
    protected $view_list = 'phocacartbookings';

    public function getModel($name = 'PhocaCartBooking', $prefix = 'PhocaCartCpModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
