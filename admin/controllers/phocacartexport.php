<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocaCartExport extends PhocaCartCpControllerPhocaCartCommon {

	function download() {

		$db		= Factory::getDBO();
		$user	= Factory::getUser();
		$app	= Factory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$import_export_type	= $paramsC->get( 'import_export_type', 0 );
		$prefix = '';
		$suffix = '';


		// Possible parameters when download problems
		// Export - ajax and pagination is used
		// Download - no ajax and pagination used
		$limitOffset 	= $paramsC->get( 'export_download_limit_offset', 0 );
		$limitCount 	= $paramsC->get( 'export_download_limit_count', 0 );
		$rowH			= array();// Specific Header for divided files
		$rowF			= array();// Specific Footer for divided files

		$q = 'SELECT a.item'
				.' FROM #__phocacart_export AS a'
			    .' WHERE a.user_id = '.(int) $user->id;

		if ((int)$limitCount > 0) {
			$q .= ' AND a.type = 0';// only standard items (not header, no footer)
		}

		$q .= ' ORDER BY a.id';

		if ((int)$limitCount > 0) {
			$q .= ' LIMIT '.(int)$limitOffset. ', '.(int)$limitCount;
		}

		$db->setQuery($q);
		$rows = $db->loadColumn();

		if ((int)$limitCount > 0) {
			// Partialy download - download divided to more download files - we need to do header and footer for each file
			$q = 'SELECT a.item'
				.' FROM #__phocacart_export AS a'
			    .' WHERE a.user_id = '.(int) $user->id
				.' AND a.type = 1' //Header
				.' ORDER BY a.id';
			$db->setQuery($q);
			$rowH = $db->loadColumn();

			$q = 'SELECT a.item'
				.' FROM #__phocacart_export AS a'
			    .' WHERE a.user_id = '.(int) $user->id
				.' AND a.type = 2' //Header
				.' ORDER BY a.id';
			$db->setQuery($q);
			$rowF = $db->loadColumn();
		}

		$o = '';
		if (!empty($rows)) {

			// Header only - in case of divided file
			if (!empty($rowH)) {
				foreach ($rowH as $k => $v) {
					$o .= $v. "\n";
				}
			}

			// All items
			foreach ($rows as $k => $v) {
				$o .= $v. "\n";
			}

			// Footer only - in case of divided file
			if (!empty($rowF)) {
				foreach ($rowF as $k => $v) {
					$o .= $v. "\n";
				}
			}



		} else {
			$message = Text::_( 'COM_PHOCACART_THERE_IS_NO_FILE_READY_TO_DOWNLOAD_EXPORT_PRODUCTS_FIRST' );
			$app->enqueueMessage($message, 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartexports');
			return;
		}

		$download = PhocacartDownload::downloadContent($o, $prefix, $suffix);
		if ($download) {
			$q = 'TRUNCATE TABLE #__phocacart_export;';
			$db->setQuery($q);
			$db->execute();
		}
		exit;
	}

}
?>
