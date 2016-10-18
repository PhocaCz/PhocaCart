<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view'); 

class PhocaCartCpViewPhocaCartThumbA extends JViewLegacy
{
	function display($tpl = null){
		
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}
		
		$app		= JFactory::getApplication();
		$fileName	= $app->input->get( 'filename', '', 'string'  );
		$fileName	= rawUrlDecode($fileName);
		$manager	= $app->input->get( 'manager', '', 'string'  );
		$path		= PhocaCartPath::getPath($manager);
		$absPath	= $path['orig_abs_ds'] . $fileName;
		
		if (!JFile::exists($absPath)) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('COM_PHOCACART_ERROR_FILE_NOT_EXIST') . ' ('.rawUrlDecode($fileName).')</span>');
			echo json_encode($response);
			return;
		
		}
		
		$folder 		= PhocaCartFile::getFolderFromTheFile($fileName);
		$absPathFolder	= $path['orig_abs_ds'] . $folder;
		
		/*
		if (!JFolder::exists($absPathFolder . 'thumbs')) {
			if (JFolder::create( $absPathFolder . 'thumbs', 0755 )) {
				$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
				JFile::write($absPathFolder . 'thumbs/'."index.html", $data);
			} else {
				$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . JText::_('COM_PHOCACART_ERROR_CREATING_THUMBS_FOLDER') . ' ('.$folder . 'thumbs'.')</span>');
				echo json_encode($response);
				return;
			}		
		}*/
		
		
		$thumbnail = PhocaCartFileThumbnail::getOrCreateThumbnail($fileName, '', 1, 1, 1, 0, $manager);
		
		//DO THUMBNAILS and return if true or false
		$string = $thumbnail;
		if (is_array($thumbnail)) {
			$string = '';
			foreach ($thumbnail as $k => $v) {
				$string .= '['.$k.'] ... '.$v.'<br />';
			}
		}
		
		$msg = $string;
		$response = array(
		'status' => '1',
		'error' => '',
		'message' => '<span class="ph-result-txt ph-success-txt">'.$msg.'</span>');
		echo json_encode($response);
		return;
	
		/*$msg = JText::_('COM_PHOCACART_SUCCESS_THUMBNAIL_EXISTS');
		$response = array(
		'status' => '1',
		'error' => '',
		'message' => '<span class="ph-result-txt ph-success-txt">'.$msg.'</span>');
		echo json_encode($response);
		return;*/
			
		$app	= JFactory::getApplication();
		$params			= &$app->getParams();
		
		
		$ratingVote 	= $app->input->get( 'ratingVote', 0, 'post', 'int'  );
		$ratingId 		= $app->input->get( 'ratingId', 0, 'post', 'int'  );// ID of File
		$format 		= $app->input->get( 'format', '', 'post', 'string'  );
		$task 			= $app->input->get( 'task', '', 'get', 'string'  );
		$view 			= $app->input->get( 'view', '', 'get', 'string'  );
		$small			= $app->input->get( 'small', 1, 'get', 'string'  );//small or large rating icons
		
		$paramsC 		= JComponentHelper::getParams('com_phocadownload');
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
		
			$user 		=JFactory::getUser();
			
		
			$neededAccessLevels	= PhocaDownloadAccess::getNeededAccessLevels();
			$access				= PhocaDownloadAccess::isAccess($user->getAuthorisedViewLevels(), $neededAccessLevels);
		
			
			$post['fileid'] 	= (int)$ratingId;
			$post['userid']		= $user->id;
			$post['rating']		= (int)$ratingVote;

			
			if ($format != 'json') {
				$msg = JText::_('COM_PHOCADOWNLOAD_ERROR_WRONG_RATING') ;
				$response = array(
					'status' => '0',
					'error' => $msg);
				echo json_encode($response);
				return;
			}
			
			if ((int)$post['fileid'] < 1) {
				$msg = JText::_('COM_PHOCADOWNLOAD_ERROR_FILE_NOT_EXISTS');
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
				$msg = JText::_('COM_PHOCADOWNLOAD_RATING_ALREADY_RATED_FILE');
				$response = array(
					'status' => '0',
					'error' => '',
					'message' => $msg);
				echo json_encode($response);
				return;
			} else {
				if ((int)$post['rating']  < 1 || (int)$post['rating'] > 5) {
					
					$msg = JText::_('COM_PHOCADOWNLOAD_ERROR_WRONG_RATING');
					$response = array(
					'status' => '0',
					'error' => $msg);
					echo json_encode($response);
					return;
				}
				
				if ($access > 0 && $user->id > 0) {
					if(!$model->rate($post)) {
						$msg = JText::_('COM_PHOCADOWNLOAD_ERROR_RATING_FILE');
						$response = array(
						'status' => '0',
						'error' => $msg);
						echo json_encode($response);
						return;
					} else {
						$msg = JText::_('COM_PHOCADOWNLOAD_SUCCESS_RATING_FILE');
						$response = array(
						'status' => '1',
						'error' => '',
						'message' => $msg);
						echo json_encode($response);
						return;
					} 
				} else {
					$msg = JText::_('COM_PHOCADOWNLOAD_NOT_AUTHORISED_ACTION');
						$response = array(
						'status' => '0',
						'error' => $msg);
						echo json_encode($response);
						return;
				}
			}
		} else {
			$msg = JText::_('COM_PHOCADOWNLOAD_NOT_AUTHORISED_ACTION');
			$response = array(
			'status' => '0',
			'error' => $msg);
			echo json_encode($response);
			return;
		}
	}
}
?>