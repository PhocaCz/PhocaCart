<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;

require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocaCartImport extends PhocaCartCpControllerPhocaCartCommon {


	public $t;

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


		// line cca: 588: libraries/cms/layout/file.php
		//$layout	= new FileLayout('product_import', null, array('client' => 0, 'component' => 'com_phocacart'));
		$layout	= new FileLayout('product_import', null, array('component' => 'com_phocacart'));
		/*if ($this->t['import_export_type'] == 0) {
			$d['type'] = 'csv';
		} else {
			$d['type'] = 'xml';
		}*/

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
		return;
	}

}
?>
