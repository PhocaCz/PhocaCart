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

class PhocacartEmailMail extends JMail{


	public static function getInstance($id = 'Joomla', $exceptions = true)
	{
		if (empty(self::$instances[$id]))
		{
			self::$instances[$id] = new PhocacartEmailMail($exceptions);
		}

		return self::$instances[$id];
	}

	// PHOCAEDIT
	//public function sendMail($from, $fromName, $recipient, $subject, $body, $mode = false, $cc = null, $bcc = null, $attachment = null,
	//	$replyTo = null, $replyToName = null)
	public function sendMailA($from, $fromName, $recipient, $subject, $body, $mode = false, $cc = null, $bcc = null, $attachment = null, $attachmentString = '', $attachmentFileName = '', $replyTo = null, $replyToName = null)
	{


		$this->setSubject($subject);
		$this->setBody($body);

		// Are we sending the email as HTML?
		$this->isHtml($mode);

		/*
		 * Do not send the message if adding any of the below items fails
		 */

		if ($this->addRecipient($recipient) === false)
		{
			return false;
		}

		if ($this->addCc($cc) === false)
		{

			return false;
		}


		if ($this->addBcc($bcc) === false)
		{
			return false;
		}

		// PHOCAEDIT
		if ($attachmentString != '' && $attachmentFileName != '') {
			if	($this->addStringAttachment($attachmentString, $attachmentFileName) === false)
			{
				return false;
			}
		}

		//public function addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream', $disposition = 'attachment')
		if ($this->addAttachment($attachment) === false)
		{
			return false;
		}

		// Take care of reply email addresses
		if (is_array($replyTo))
		{
			$numReplyTo = count($replyTo);

			for ($i = 0; $i < $numReplyTo; $i++)
			{
				if ($this->addReplyTo($replyTo[$i], $replyToName[$i]) === false)
				{
					return false;
				}
			}
		}
		elseif (isset($replyTo))
		{
			if ($this->addReplyTo($replyTo, $replyToName) === false)
			{
				return false;
			}
		}

		// Add sender to replyTo only if no replyTo received
		$autoReplyTo = (empty($this->ReplyTo)) ? true : false;

		if ($this->setSender(array($from, $fromName, $autoReplyTo)) === false)
		{

			return false;
		}


		return $this->Send();
	}

}



class PhocacartEmailFactory extends JFactory{

	public static $mailer = null;

	public static function getMailer()
	{
		if (!self::$mailer)
		{
			self::$mailer = self::createMailer();
		}

		$copy = clone self::$mailer;

		return $copy;
	}

	protected static function createMailer()
	{
		$conf = self::getConfig();

		$smtpauth = ($conf->get('smtpauth') == 0) ? null : 1;
		$smtpuser = $conf->get('smtpuser');
		$smtppass = $conf->get('smtppass');
		$smtphost = $conf->get('smtphost');
		$smtpsecure = $conf->get('smtpsecure');
		$smtpport = $conf->get('smtpport');
		$mailfrom = $conf->get('mailfrom');
		$fromname = $conf->get('fromname');
		$mailer = $conf->get('mailer');

		// Create a JMail object
		$mail = PhocacartEmailMail::getInstance();

		// Clean the email address
		$mailfrom = JMailHelper::cleanLine($mailfrom);

		// Set default sender without Reply-to if the mailfrom is a valid address
		if (JMailHelper::isEmailAddress($mailfrom))
		{
			// Wrap in try/catch to catch phpmailerExceptions if it is throwing them
			try
			{
				// Check for a false return value if exception throwing is disabled
				if ($mail->setFrom($mailfrom, JMailHelper::cleanLine($fromname), false) === false)
				{
					PhocacartLog::add(2, 'Error sending email', 0, __METHOD__ . '() could not set the sender data. Warning: ' . JLog::WARNING, 'Mail From: '.$mailfrom );
					JLog::add(__METHOD__ . '() could not set the sender data.', JLog::WARNING, 'mail');
				}
			}
			catch (phpmailerException $e)
			{
				PhocacartLog::add(2, 'Error sending email', 0, __METHOD__ . '() could not set the sender data. Warning: ' . JLog::WARNING, 'Mail From: '.$mailfrom );
				JLog::add(__METHOD__ . '() could not set the sender data.', JLog::WARNING, 'mail');
			}
		}

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp':
				$mail->useSmtp($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
				break;

			case 'sendmail':
				$mail->isSendmail();
				break;

			default:
				$mail->isMail();
				break;
		}

		return $mail;
	}
}



class PhocacartEmail
{


	public static function sendEmail($from = '', $fromName = '', $recipient = '', $subject = '', $body = '', $mode = false, $cc = array(), $bcc = array(), $attachment = null, $attachmentString = '', $attachmentFilename = '', $replyTo = null, $replyToName = null) {

	//public static function sendEmail($from = '', $fromName = '', $recipient, $subject, $body, $mode = false, $cc = array(), $bcc = array(), $attachment = null, $replyTo = null, $replyToName = null) {

		$config			= JFactory::getConfig();

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
					if (JFile::exists($tmpl['attachment_full'][$i])) {
						$attachmentArray[] = $tmpl['attachment_full'][$i];
					} else {
						$warning[]	= JText::_('COM_PHOCAEMAIL_ERROR_FILE_NOT_EXISTS').': '. $tmpl['attachment_full'][$i];
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

		$app						= JFactory::getApplication();
		$db 						= JFactory::getDBO();
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

		$subject = $sitename .' - '.JText::_( 'COM_PHOCACART_NEW_QUESTION' );
		if (isset($data['product']->title) && $data['product']->title != '') {
			$subject .= ', '.JText::_('COM_PHOCACART_PRODUCT'). ': '. $data['product']->title;
		}
		if (isset($data['category']->title) && $data['category']->title != '') {
			$subject .= ', '.JText::_('COM_PHOCACART_CATEGORY'). ': '. $data['category']->title;
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
			$message = JText::_('COM_PHOCACART_NO_MESSAGE');
		}


		$email = '';
		if (isset($rows[0]->email)) {
			$email = $rows[0]->email;
		}

		$message = str_replace("</p>", "\n", $message );
		$message = strip_tags($message);


		$layoutE 			= new JLayoutFile('email_ask_question', null, array('component' => 'com_phocacart'));
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
			$notify = PhocacartEmail::sendEmail($mailfrom, $fromname, $email, $subject, $body, true, null, null, null, '', '', $mailfrom, $fromname);
		}


		// Send email to others
		$emailOthers	= '';
		$bcc			= null;
		if (isset($send_email_question_others) && $send_email_question_others != '') {
			$bcc = explode(',', $send_email_question_others );
			if (isset($bcc[0]) && JMailHelper::isEmailAddress($bcc[0])) {
				$emailOthers = $bcc[0];
			}
		}

		$notifyOthers = false;
		if ($emailOthers != '' && JMailHelper::isEmailAddress($emailOthers)) {
			$notifyOthers = PhocacartEmail::sendEmail($mailfrom, $fromname, $emailOthers, $subject, $body, true, null, $bcc, null, '', '', $mailfrom, $fromname);
		}


		if ($notify || $notifyOthers) {
			return true;
		}
		return false;
	}

	public static function sendSubmitItemMail ($dataItem, $dataContact, $dataParameter, $url, $tmpl) {

		$app							= JFactory::getApplication();
		$db 							= JFactory::getDBO();
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

		$subject = $sitename .' - '.JText::_( 'COM_PHOCACART_NEW_ITEM_SUBMITTED' );



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



		$layoutE 			= new JLayoutFile('email_submit_item', null, array('component' => 'com_phocacart'));
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
			$notify = PhocacartEmail::sendEmail($mailfrom, $fromname, $email, $subject, $body, true, null, null, null, '', '', $mailfrom, $fromname);
		}


		// Send email to others
		$emailOthers	= '';
		$bcc			= null;
		if (isset($send_email_submit_item_others) && $send_email_submit_item_others != '') {
			$bcc = explode(',', $send_email_submit_item_others );
			if (isset($bcc[0]) && JMailHelper::isEmailAddress($bcc[0])) {
				$emailOthers = $bcc[0];
			}
		}

		$notifyOthers = false;
		if ($emailOthers != '' && JMailHelper::isEmailAddress($emailOthers)) {
			$notifyOthers = PhocacartEmail::sendEmail($mailfrom, $fromname, $emailOthers, $subject, $body, true, null, $bcc, null, '', '', $mailfrom, $fromname);
		}


		if ($notify || $notifyOthers) {
			return true;
		}
		return false;
	}
}
?>
