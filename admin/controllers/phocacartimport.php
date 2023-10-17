<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Layout\FileLayout;
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocaCartImport extends PhocaCartCpControllerPhocaCartCommon {



	public function upload() {

		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app		= Factory::getApplication();
		$db 		= Factory::getDBO();
		$user 		= Factory::getUser();
		$userId		= $user->id;
		$redirect	= 'index.php?option=com_phocacart&view=phocacartimports';
		//$file		= Factory::getApplication()->input->files->get( 'Filedata', null, 'raw');
		$file		= Factory::getApplication()->input->files->get( 'Filedata');

		$paramsC 				= PhocacartUtils::getComponentParameters();
		$fgets_line_length		= $paramsC->get( 'fgets_line_length', 24576 );



		if (!File::exists($file['tmp_name'])) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_FILE_NOT_EXIST'), 'error');
			$app->redirect($redirect);
		}

		if (!isset($file['name'])) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_FILE_NOT_EXIST'), 'error');
			$app->redirect($redirect);
		}

		$ext =  File::getExt($file['name']);

		if ($ext != 'csv' && $ext != 'txt' && $ext != 'xml') {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_FILE_TYPE_NOT_SUPPORTED'), 'error');
			$app->redirect($redirect);
		}

		$valA = array();

		switch($ext) {

			case 'xml':

				$row = 1;
				$xml = simplexml_load_file($file['tmp_name']);

				if ($xml) {
					if (!empty($xml->product)) {
						foreach($xml->product as $k => $v) {

							$dataC = (string)$v->asXML();
							$dataC = str_replace(array("\n","\r"), "", $dataC);
							$valA[] = '('.(int)$userId.', '.(int)$row.', '.$db->quote($dataC).', 0, 1)';
							$row++;
						}
					}

				}

			break;

			case 'csv':
			case 'txt':
			default:

				$row = 1;
				if (($handle = fopen($file['tmp_name'], "r")) !== false) {

					while (($data = fgets($handle, (int)$fgets_line_length)) !== false) {

						$dataC = PhocacartUtils::convertEncoding($data);

						// First two rows are headers
						$type = 0;
						if ($row == 1) {
							$type = 1;
						} else if ($row == 2) {
							$type = 1;
						}

						$valA[] = '('.(int)$userId.', '.(int)$row.', '.$db->quote($dataC).', '.(int)$type.', 0)';

						$row++;
					}
					fclose($handle);
				}



			break;


		}


		$valS = '';
		if (!empty($valA)) {
			$valS = implode(', ', $valA);

			$q = ' TRUNCATE TABLE #__phocacart_import;';
			$db->setQuery($q);
			$db->execute();

			$q = ' INSERT INTO #__phocacart_import (user_id, row_id, item, type, file_type)'
				.' VALUES '.(string)$valS;
			$db->setQuery($q);
			$db->execute();

			$app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_FILE_UPLOADED'), 'success');
		}

		$app->redirect($redirect);
	}

	/*
	public function import() {


		if (!Session::checkToken('request')) {
			$response = array('status' => '0', 'error' => '<div class="alert alert-error">' . Text::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			return;
		}
		$app		= Factory::getApplication();
		$db			= Factory::getDBO();
		$paramsC 	= PhocacartUtils::getComponentParameters();
		$this->t['import_export_pagination']	= $paramsC->get( 'import_export_pagination', 20 );

		$page		= $app->input->get('p', 0, 'int');
		$last_page	= $app->input->get('lp', 0, 'int');


		$limitOffset 	= ((int)$page * (int)$this->t['import_export_pagination']) - (int)$this->t['import_export_pagination'];
		if ($limitOffset < 0) {
			$limitOffset = 0;
		}
		$limitCount		= $this->t['import_export_pagination'];

		$model = $this->getModel();

		$d = array();
		$d['file_type']			= $model->getFileType();
		$d['products'] 			= $model->getUploadedProducts($limitOffset, $limitCount);
		$d['productcolumns'] 	= $model->getUploadedProductColumns();// 1 and 2 line - Header - Filtering of columns Set in layout
		$d['page']				= $page;// Pagination
		$d['last_page']			= $last_page;// Pagination



		// IMPORTANT - Layout of component is frontend, but to override it - administration template must be used
		// line cca: 588: libraries/cms/layout/file.php
		$layout	= new FileLayout('product_import', null, array('client' => 0, 'component' => 'com_phocacart'));
		/*if ($this->t['import_export_type'] == 0) {
			$d['type'] = 'csv';
		} else {
			$d['type'] = 'xml';
		}*/
/*
		$output = $layout->render($d);


		if ($d['page'] == $d['last_page']) {
			$q = 'TRUNCATE TABLE #__phocacart_import;'. " ";
			$db->setQuery($q);
			$db->execute();
		}


		//$q = 'INSERT INTO #__phocacart_export (user_id, item, type) VALUES '.(string)$output;

		// Type 0 - standard item, 1 - header, 2 - footer
		//$db->setQuery($q);
		//$db->execute();

		$response = array('status' => '1', 'message' => '<div class="alert alert-success">OK</div>');
		echo json_encode($response);
		//return;
	}*/
}
?>
