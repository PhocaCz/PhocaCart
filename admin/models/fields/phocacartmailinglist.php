<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldPhocaCartMailingList extends FormField
{
    protected $type = 'PhocaCartMailingList';

    protected function getInput()
    {


        // Initialize variables.
        $html = array();


        // Initialize some field attributes.
        $attr = '';
        $attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
        $attr .= $this->element['maxlength'] ? ' maxlength="' . (int)$this->element['maxlength'] . '"' : '';
        $attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
        $attr .= ((string)$this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $attr .= ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '" ' : ' ';
        $multiple = ((string)$this->element['multiple'] == 'true') ? TRUE : FALSE;
        $manager = $this->element['manager'] ? (string)$this->element['manager'] : '';

        $value = $this->value;

        // PHOCA EMAIL COMPONENT NEEDED
        $comPhocaemail = PhocacartUtilsExtension::getExtensionInfo('com_phocaemail');
        if ($comPhocaemail) {

            $order = 'ordering ASC';
            $db = Factory::getDBO();
            $query = 'SELECT a.id AS value, a.title AS text'
                . ' FROM #__phocaemail_lists AS a'
                . ' WHERE a.published = 1'
                . ' ORDER BY ' . $order;
            $db->setQuery($query);
            $lists = $db->loadObjectList();

            if ($multiple) {
                $name = $this->name;
                $attr .= ' multiple="multiple"';
            } else {
                $name = $this->name;

            }


        } else {
            $lists = array();
            $name = $this->name;
        }

		$data               = $this->getLayoutData();
		$data['options']    = (array)$lists;
		$data['value']      = $value;

		return $this->getRenderer($this->layout)->render($data);

        //$html = HTMLHelper::_('select.genericlist', $lists, $name, $attr, 'value', 'text', $value, 'id');

        //return $html;

    }
}

?>
