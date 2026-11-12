<?php
/**
 * @package    phocacart
 * @subpackage Controllers
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controllers/phocacartcommon.php';

class PhocaCartCpControllerPhocaCartBookingSlot extends PhocaCartCpControllerPhocaCartCommon
{
    protected $view_item = 'phocacartbookingslot';
    protected $view_list = 'phocacartbookingslots';

    public function getModel($name = 'PhocaCartBookingSlot', $prefix = 'PhocaCartCpModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
