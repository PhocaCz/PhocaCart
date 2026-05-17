<?php
/**
 * @package    phocacart
 * @subpackage Controllers
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controllers/phocacartcommon.php';

class PhocaCartCpControllerPhocaCartSubscription extends PhocaCartCpControllerPhocaCartCommon
{
    protected $view_item = 'phocacartsubscription';
    protected $view_list = 'phocacartsubscriptions';

    public function getModel($name = 'PhocaCartSubscription', $prefix = 'PhocaCartCpModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
