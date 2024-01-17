<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Component\ComponentHelper;
jimport( 'joomla.application.component.view');

class PhocaCartCpViewPhocaCartThumbA extends HtmlView
{
	function display($tpl = null){

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}

		$app		= Factory::getApplication();
		$fileName	= $app->input->get( 'filename', '', 'string'  );
		$fileName	= rawUrlDecode($fileName);
		$manager	= $app->input->get( 'manager', '', 'string'  );
		$path		= PhocacartPath::getPath($manager);
		$absPath	= $path['orig_abs_ds'] . $fileName;


		if (trim($fileName) == '') {
			$response = array(
				'status' => '2');
			echo json_encode($response);
			return;

		}

		if (!File::exists($absPath)) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_ERROR_FILE_NOT_EXIST') . ' ('.rawUrlDecode($fileName).')</span>');
			echo json_encode($response);
			return;

		}

		$folder 		= PhocacartFile::getFolderFromTheFile($fileName);
		$absPathFolder	= $path['orig_abs_ds'] . $folder;

		/*
		if (!Folder::exists($absPathFolder . 'thumbs')) {
			if (Folder::create( $absPathFolder . 'thumbs', 0755 )) {
				$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
				File::write($absPathFolder . 'thumbs/'."index.html", $data);
			} else {
				$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_ERROR_CREATING_THUMBS_FOLDER') . ' ('.$folder . 'thumbs'.')</span>');
				echo json_encode($response);
				return;
			}
		}*/


		$ext = strtolower(File::getExt($fileName));


		switch ($ext) {
			case 'jpg':
			case 'png':
			case 'gif':
			case 'jpeg':
			case 'webp':
			case 'avif':
				$thumbnail = PhocacartFileThumbnail::getOrCreateThumbnail($fileName, '', 1, 1, 1, 0, $manager);

				//DO THUMBNAILS and return if true or false
				$string = $thumbnail;
				if (is_array($thumbnail)) {
					$string = '';
					foreach ($thumbnail as $k => $v) {
						$string .= '['.$k.'] ... '.$v.'<br />';
					}
				}
			break;
			default:
				$string = '<span class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_SELECTED_IMAGE_TYPE_NOT_SUPPORTED') . '</span>';
			break;
		}



		$msg = $string;

		$response = array(
		'status' => '1',
		'error' => '',
		'message' => $msg);// $msg included the span  '<span class="ph-result-txt ph-success-txt"></span>'
		echo json_encode($response);
		return;

		/*$msg = Text::_('COM_PHOCACART_SUCCESS_THUMBNAIL_EXISTS');
		$response = array(
		'status' => '1',
		'error' => '',
		'message' => '<span class="ph-result-txt ph-success-txt">'.$msg.'</span>');
		echo json_encode($response);
		return;*/


		/*
		$app	= Factory::getApplication();
		$params			= &$app->getParams();


		$ratingVote 	= $app->input->get( 'ratingVote', 0, 'post', 'int'  );
		$ratingId 		= $app->input->get( 'ratingId', 0, 'post', 'int'  );// ID of File
		$format 		= $app->input->get( 'format', '', 'post', 'string'  );
		$task 			= $app->input->get( 'task', '', 'get', 'string'  );
		$view 			= $app->input->get( 'view', '', 'get', 'string'  );
		$small			= $app->input->get( 'small', 1, 'get', 'string'  );//small or large rating icons

		$paramsC 		= ComponentHelper::getParams('com_phocadownload');
		$param['displayratingfile'] = $paramsC->get( 'display_rating_file', 0 );

		// Check if rating is enabled - if not then user should not be able to rate or to see updated reating



		if ($task == 'refreshrate' && (int)$param['displayratingfile'] > 0) {
			$ratingOutput 		= PhocaDownloadRate::renderRateFile((int)$ratingId, 1, $small, true);// ID of File
			$response = array(
					'status' => '0',
					'message' => $ratingOutput);
				echo json_encode($response);
				return;
			//return $ratingOutput;

		} else if ($task == 'rate') {

			$user 		=Factory::getUser();


			$neededAccessLevels	= PhocaDownloadAccess::getNeededAccessLevels();
			$access				= PhocaDownloadAccess::isAccess($user->getAuthorisedViewLevels(), $neededAccessLevels);


			$post['fileid'] 	= (int)$ratingId;
			$post['userid']		= $user->id;
			$post['rating']		= (int)$ratingVote;


			if ($format != 'json') {
				$msg = Text::_('COM_PHOCADOWNLOAD_ERROR_WRONG_RATING') ;
				$response = array(
					'status' => '0',
					'error' => $msg);
				echo json_encode($response);
				return;
			}

			if ((int)$post['fileid'] < 1) {
				$msg = Text::_('COM_PHOCADOWNLOAD_ERROR_FILE_NOT_EXISTS');
				$response = array(
					'status' => '0',
					'error' => $msg);
				echo json_encode($response);
				return;
			}

			$model = $this->getModel();

			$checkUserVote	= PhocaDownloadRate::checkUserVoteFile( $post['fileid'], $post['userid'] );

			// User has already rated this category
			if ($checkUserVote) {
				$msg = Text::_('COM_PHOCADOWNLOAD_RATING_ALREADY_RATED_FILE');
				$response = array(
					'status' => '0',
					'error' => '',
					'message' => $msg);
				echo json_encode($response);
				return;
			} else {
				if ((int)$post['rating']  < 1 || (int)$post['rating'] > 5) {

					$msg = Text::_('COM_PHOCADOWNLOAD_ERROR_WRONG_RATING');
					$response = array(
					'status' => '0',
					'error' => $msg);
					echo json_encode($response);
					return;
				}

				if ($access > 0 && $user->id > 0) {
					if(!$model->rate($post)) {
						$msg = Text::_('COM_PHOCADOWNLOAD_ERROR_RATING_FILE');
						$response = array(
						'status' => '0',
						'error' => $msg);
						echo json_encode($response);
						return;
					} else {
						$msg = Text::_('COM_PHOCADOWNLOAD_SUCCESS_RATING_FILE');
						$response = array(
						'status' => '1',
						'error' => '',
						'message' => $msg);
						echo json_encode($response);
						return;
					}
				} else {
					$msg = Text::_('COM_PHOCADOWNLOAD_NOT_AUTHORISED_ACTION');
						$response = array(
						'status' => '0',
						'error' => $msg);
						echo json_encode($response);
						return;
				}
			}
		} else {
			$msg = Text::_('COM_PHOCADOWNLOAD_NOT_AUTHORISED_ACTION');
			$response = array(
			'status' => '0',
			'error' => $msg);
			echo json_encode($response);
			return;
		}
		*/
	}
}
?>
