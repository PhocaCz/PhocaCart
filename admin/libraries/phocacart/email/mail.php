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



class PhocacartEmailMail extends Mail{

	public function __construct($exceptions = true)
  	{
    	parent::__construct($exceptions);
  	}

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
?>
