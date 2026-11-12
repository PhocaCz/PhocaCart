<?php
/**
 * @package    phocacart
 * @subpackage Models
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Phoca\PhocaCart\MVC\Model\AdminModelTrait;
use Joomla\CMS\Factory;

class PhocaCartCpModelPhocaCartBookingSlot extends AdminModel
{
    use AdminModelTrait;

    protected $option      = 'com_phocacart';
    protected $text_prefix = 'com_phocacart';

    public function getTable($type = 'PhocaCartBookingSlot', $prefix = 'Table', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_phocacart.phocacartbookingslot', 'phocacartbookingslot', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartbookingslot.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }
}
