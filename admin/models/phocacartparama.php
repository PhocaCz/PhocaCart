<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocaCartParamA extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	protected	$formNameR;
	protected	$formName;

	public function setFormName($formNameR, $formName) {
		$this->formNameR 	= $formNameR;
		$this->formName 	= $formName;
	}

	protected function canDelete($record) {
		return parent::canDelete($record);
	}

	protected function canEditState($record) {
		return parent::canEditState($record);
	}

	// PhocacartPayment
	// PhocacartShipping
	public function getTable($type = 'PhocacartPayment', $prefix = 'Table', $config = array()) {
		$app	= Factory::getApplication();
		$type	= $app->input->get( 'type', '', 'int'  );
		if ($type == 2) {
			$type = 'PhocacartShipping';
		} else {
			$type = 'PhocacartPayment';
		}
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {

		$form 	= $this->loadForm($this->formNameR, $this->formName, array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData() {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartparama.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}


	/*
	 * Add additional parameter of the payment method to load it in payment options (x001) - pcp
	 * Add additional parameter of the shipping method to load it in shipping options (x001) - pcs
	 */

	protected function preprocessForm(Form $form, $data, $group = 'pcp') {


		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$app	= Factory::getApplication();
		$type	= $app->input->get( 'type', '', 'int'  );
		if ($type == 2) {
			$group 	= 'pcs';
			$folder = 'pcs';
		} else {
			$group 	= 'pcp';
			$folder = 'pcp';
		}

		// Initialise variables.
		//$folder		= 'pcp';//$this->getState('item.folder');
		//$element	= $this->getState('item.element');
		$app		= Factory::getApplication();
		$method		= $app->input->get( 'method', '', 'string'  );// get the method, when start or when changed the select box
		$element	= $method;
		$lang		= Factory::getLanguage();
		$client		= ApplicationHelper::getClientInfo(0);

		if (empty($folder) || empty($element)) {
		    return false;
			//$app = Factory::getApplication();
			//$app->re-direct(Route::_('index.php?option=com_phocapdf&view=phocapdfcp',false), Text::_('COM_PHOCACART_NO_FOLDER_OR_ELEMENT_FOUND'));
		}
		// Try 1.6 format: /plugins/folder/element/element.xml
		$formFile = Path::clean($client->path.'/plugins/'.$folder.'/'.$element.'/'.$element.'.xml');


		if (!file_exists($formFile)) {
			// Try 1.5 format: /plugins/folder/element/element.xml
			$formFile = Path::clean($client->path.'/plugins/'.$folder.'/'.$element.'.xml');
			if (!file_exists($formFile)) {
				throw new Exception(Text::_('COM_PHOCACART_ERROR_FILE_NOT_FOUND') . ': '. $folder . ': '.$element.'.xml');
				return false;
			}
		}

		// Load the core and/or local language file(s).
		$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR)
			||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element);

		if (file_exists($formFile)) {
			// Get the plugin form.
			if (!$form->loadFile($formFile, false, '//form')) {
				throw new Exception(Text::_('COM_PHOCACART_ERROR_LOADFILE_FAILED'));
			}
		}

		// Attempt to load the xml file.
		if (!$xml = simplexml_load_file($formFile)) {
			throw new Exception(Text::_('COM_PHOCACART_ERROR_LOADFILE_FAILED'));
		}

		// Get the help data from the XML file if present.
		$help = $xml->xpath('/extension/help');
		if (!empty($help)) {
			$helpKey = trim((string) $help[0]['key']);
			$helpURL = trim((string) $help[0]['url']);

			$this->helpKey = $helpKey ? $helpKey : $this->helpKey;
			$this->helpURL = $helpURL ? $helpURL : $this->helpURL;
		}

		// Trigger the default form events.
		parent::preprocessForm($form, $data);
	}
}
?>
