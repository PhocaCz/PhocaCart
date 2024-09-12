<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

//phocacartimport('phocacart.email.emailemail');
//phocacartimport('phocacart.email.emailfactory');

class PhocacartEmail
{


	public static function sendEmail($from = '', $fromName = '', $recipient = '', $subject = '', $body = '', $mode = false, $cc = array(), $bcc = array(), $attachment = null, $attachmentString = '', $attachmentFilename = '', $replyTo = null, $replyToName = null) {

	//public static function sendEmail($from = '', $fromName = '', $recipient, $subject, $body, $mode = false, $cc = array(), $bcc = array(), $attachment = null, $replyTo = null, $replyToName = null) {

		$config			= Factory::getConfig();// Factory::getApplication()->get('mailfrom', '');

		if ($from == '') {
			$from 		= $config->get('mailfrom');
		}
		if ($fromName == '') {
			$fromName 		= $config->get('fromname');
		}

		if ($replyTo == '') {
			$replyTo 		= $from;
		}
		if ($replyToName == '') {
			$replyToName 		= $fromName;
		}

		if ($cc == '') {
			$cc = array();
		}
		if ($bcc == '') {
			$bcc = array();
		}

		// REMOVE DUPLICITY EMAIL ADDRESS: recepient vs. cc vs. bcc
		$dR 	= array(0 => $recipient);
		$dCc	= is_array($cc) ? $cc : array(0 => $cc);
		$dBcc	= is_array($bcc) ? $bcc : array(0 => $bcc);

		if (!empty($dCc)) {
			foreach($dCc as $k => $v) {
				if (in_array($v, $dR)) {
					unset($dCc[$k]);
				}
			}
		}

		if (!empty($dBcc)) {
			foreach($dBcc as $k => $v) {
				if (in_array($v, $dR)) {
					unset($dBcc[$k]);
				} else if (in_array($v, $dCc)) {
					unset($dBcc[$k]);
				}
			}
		}
		// Set back cleaned arrays
		$cc		= $dCc;
		$bcc	= $dBcc;


		// Attachment
		/*if (!empty($tmpl['attachment'])) {
			$i = 0;
			foreach ($tmpl['attachment'] as $key => $value) {
				if(isset($data['attachment'][$i]) && $data['attachment'][$i]) {
					if (File::exists($tmpl['attachment_full'][$i])) {
						$attachmentArray[] = $tmpl['attachment_full'][$i];
					} else {
						$warning[]	= Text::_('COM_PHOCAEMAIL_ERROR_FILE_NOT_EXISTS').': '. $tmpl['attachment_full'][$i];
					}
				}
				$i++;
			}
		}*/
		$subject 	= html_entity_decode($subject, ENT_QUOTES);
		$body 		= html_entity_decode($body, ENT_QUOTES);

		$mail 		= PhocacartEmailFactory::getMailer();


		$body 		= $body . PhocacartUtilsInfo::getInfo($mode);


		$sendMail = $mail->sendMailA($from, $fromName, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $attachmentString, $attachmentFilename, $replyTo, $replyToName);



		if (is_object($sendMail) && $sendMail->getMessage()) {
			PhocacartLog::add(2, 'Error sending email', 0, $sendMail->getMessage() . ', Mail From: '.$from );
			return false;
		} else if (!$sendMail) {
			PhocacartLog::add(2, 'Error sending email', 0,  'No error data set, Mail From: '.$from );
			return false;
		} else {
			return true;
		}

	}



	public static function sendQuestionMail ($data, $url, $tmpl) {

		$app						= Factory::getApplication();
		$db 						= Factory::getDBO();
		$sitename 					= $app->get( 'sitename' );
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$numCharEmail				= $paramsC->get( 'max_char_question_email', 2000 );
		$send_email_question		= $paramsC->get( 'send_email_question', 0 );
		$send_email_question_others	= $paramsC->get( 'send_email_question_others', '' );

		//get all selected users
		$query = 'SELECT name, email, sendEmail' .
		' FROM #__users' .
		' WHERE id = '.(int)$send_email_question;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		$subject = $sitename .' - '.Text::_( 'COM_PHOCACART_NEW_QUESTION' );
		if (isset($data['product']->title) && $data['product']->title != '') {
			$subject .= ', '.Text::_('COM_PHOCACART_PRODUCT'). ': '. $data['product']->title;
		}
		if (isset($data['category']->title) && $data['category']->title != '') {
			$subject .= ', '.Text::_('COM_PHOCACART_CATEGORY'). ': '. $data['category']->title;
		}



		if (isset($data['name']) && $data['name'] != '') {
			$fromname = $data['name'];
		} else {
			$fromname = 'Unknown';
		}

		if (isset($data['email']) && $data['email'] != '') {
			$mailfrom = $data['email'];
		} else {
			$mailfrom = isset($rows[0]->email) ? $rows[0]->email : '';
		}

		if (isset($data['message']) && $data['message'] != '') {
			$message = $data['message'];
		} else {
			$message = Text::_('COM_PHOCACART_NO_MESSAGE');
		}


		$email = '';
		if (isset($rows[0]->email)) {
			$email = $rows[0]->email;
		}

		$message = str_replace("</p>", "\n", $message );
		$message = strip_tags($message);


		$layoutE 			= new FileLayout('email_ask_question', null, array('component' => 'com_phocacart'));
		$d					= array();
		$d['fromname']		= $fromname;
		$d['name']			= isset($data['name']) ? $data['name'] : '';
		$d['email']			= isset($data['email']) ? $data['email'] : '';
		$d['phone']			= isset($data['phone']) ? $data['phone'] : '';
		$d['subject']		= $subject;
		$d['message']		= $message;
		$d['numcharemail']	= $numCharEmail;
		$d['url']			= $url;
		$d['sitename']		= $sitename;

		$body = $layoutE->render($d);


		$subject = html_entity_decode($subject, ENT_QUOTES);
		$body = html_entity_decode($body, ENT_QUOTES);

		$notify = false;
		// Send email to selected user
		if ($mailfrom != '' && $send_email_question != '' && $email != '') {
			$notify = PhocacartEmail::sendEmail('', '', $email, $subject, $body, true, null, null, null, '', '', $mailfrom, $fromname);
		}


		// Send email to others
		$emailOthers	= '';
		$bcc			= null;
		if (isset($send_email_question_others) && $send_email_question_others != '') {
			$bcc = explode(',', $send_email_question_others );
			if (isset($bcc[0]) && MailHelper::isEmailAddress($bcc[0])) {
				$emailOthers = $bcc[0];
			}
		}

		$notifyOthers = false;
		if ($emailOthers != '' && MailHelper::isEmailAddress($emailOthers)) {
			$notifyOthers = PhocacartEmail::sendEmail('', '', $emailOthers, $subject, $body, true, null, $bcc, null, '', '', $mailfrom, $fromname);
		}


		if ($notify || $notifyOthers) {
			return true;
		}
		return false;
	}

	public static function sendSubmitItemMail ($dataItem, $dataContact, $dataParameter, $url, $tmpl) {

		$app							= Factory::getApplication();
		$db 							= Factory::getDBO();
		$sitename 						= $app->get( 'sitename' );
		$paramsC 						= PhocacartUtils::getComponentParameters();
		$numCharEmail				    = $paramsC->get( 'max_char_submit_item_email', 2000 );
		$send_email_submit_item			= $paramsC->get( 'send_email_submit_item', 0 );
		$send_email_submit_item_others	= $paramsC->get( 'send_email_submit_item_others', '' );


		//get all selected users
		$query = 'SELECT name, email, sendEmail' .
		' FROM #__users' .
		' WHERE id = '.(int)$send_email_submit_item;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		$subject = $sitename .' - '.Text::_( 'COM_PHOCACART_NEW_ITEM_SUBMITTED' );



		if (isset($dataContact['name']) && $dataContact['name'] != '') {
			$fromname = $dataContact['name'];
		} else {
			$fromname = 'Unknown';
		}

		if (isset($dataContact['email']) && $dataContact['email'] != '') {
			$mailfrom = $dataContact['email'];
		} else {
			$mailfrom = isset($rows[0]->email) ? $rows[0]->email : '';
		}

		/*if (isset($dataItem['title']) && $dataItem['title'] != '') {
			$message[] = $dataItem['title'];
		} else {
			$message[] = '';
		}*/

		/*if (isset($dataContact['title']) && $dataContact['title'] != '') {
			$message .= $dataContact['title'];
		} else {
			$message .= '';
		}*/

		if (isset($dataContact['message']) && $dataContact['message'] != '') {
			$message = $dataContact['message'];
		} else {
			$message = '';
		}


		$email = '';
		if (isset($rows[0]->email)) {
			$email = $rows[0]->email;
		}



		$message = str_replace("</p>", "\r\n", $message );
		$message = str_replace("<br>", "\r\n", $message );
		$message = strip_tags($message);



		$layoutE 			= new FileLayout('email_submit_item', null, array('component' => 'com_phocacart'));
		$d					= array();
		$d['fromname']		= $fromname;
		$d['name']			= isset($dataContact['name']) ? $dataContact['name'] : '';
		$d['email']			= isset($dataContact['email']) ? $dataContact['email'] : '';
		$d['phone']			= isset($dataContact['phone']) ? $dataContact['phone'] : '';
		$d['title']			= isset($dataItem['title']) ? $dataItem['title'] : '';
		$d['subject']		= $subject;
		$d['message']		= $message;
		$d['numcharemail']	= $numCharEmail;
		$d['url']			= $url;
		$d['sitename']		= $sitename;

		$body = $layoutE->render($d);


		$subject = html_entity_decode($subject, ENT_QUOTES);
		$body = html_entity_decode($body, ENT_QUOTES);

		$notify = false;
		// Send email to selected user
		if ($mailfrom != '' && $send_email_submit_item != '' && $email != '') {
			$notify = PhocacartEmail::sendEmail('', '', $email, $subject, $body, true, null, null, null, '', '', $mailfrom, $fromname);
		}


		// Send email to others
		$emailOthers	= '';
		$bcc			= null;
		if (isset($send_email_submit_item_others) && $send_email_submit_item_others != '') {
			$bcc = explode(',', $send_email_submit_item_others );
			if (isset($bcc[0]) && MailHelper::isEmailAddress($bcc[0])) {
				$emailOthers = $bcc[0];
			}
		}

		$notifyOthers = false;
		if ($emailOthers != '' && MailHelper::isEmailAddress($emailOthers)) {
			$notifyOthers = PhocacartEmail::sendEmail('', '', $emailOthers, $subject, $body, true, null, $bcc, null, '', '', $mailfrom, $fromname);
		}


		if ($notify || $notifyOthers) {
			return true;
		}
		return false;
	}
}
?>
