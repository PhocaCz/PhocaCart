<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartEmail
{
	
	
	public static function sendEmail($from = '', $fromName = '', $recipient, $subject, $body, $mode = false, $cc = null, $bcc = null, $attachment = null, $replyTo = null, $replyToName = null) {
	
		$config			= JFactory::getConfig();
		
		if ($from == '') {
			$from 		= $config->get('mailfrom');
		} 
		if ($fromName == '') {
			$fromName 		= $config->get('fromname');
		} 
		
		
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
		
		$subject = html_entity_decode($subject, ENT_QUOTES);
		$body = html_entity_decode($body, ENT_QUOTES);
		
		$mail = JFactory::getMailer();
		return $mail->sendMail($from, $fromName, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment,
		$replyTo, $replyToName);
	}
	
	public static function completeMail($body, $replace) {
	
		
		if (isset($replace['name'])) {
			$body = str_replace('{name}', $replace['name'], $body);
		}
		if (isset($replace['email'])) {
			$body = str_replace('{email}', $replace['email'], $body);
		}
		if (isset($replace['downloadlink'])) {
			$body = str_replace('{downloadlink}', $replace['downloadlink'], $body);
		}
		
		if (isset($replace['orderlink'])) {
			$body = str_replace('{orderlink}', $replace['orderlink'], $body);
		}

		return $body;
	}
}
?>