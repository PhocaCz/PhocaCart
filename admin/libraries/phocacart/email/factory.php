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



if (!class_exists('PhocaCartLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/loader.php');
}
JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

phocacartimport('phocacart.email.mail');

class PhocacartEmailFactory extends Factory{

	public static $mailer = null;

	public static function getMailer()
	{

		if (!self::$mailer)
		{
			self::$mailer = PhocacartEmailFactory::createMailer();

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
		$mail = PhocacartEmailMail::getInstance('PhocaCart');

		// Clean the email address
		$mailfrom = MailHelper::cleanLine($mailfrom);

		// Set default sender without Reply-to if the mailfrom is a valid address
		if (MailHelper::isEmailAddress($mailfrom))
		{
			// Wrap in try/catch to catch phpmailerExceptions if it is throwing them
			try
			{
				// Check for a false return value if exception throwing is disabled
				if ($mail->setFrom($mailfrom, MailHelper::cleanLine($fromname), false) === false)
				{
					PhocacartLog::add(2, 'Error sending email', 0, __METHOD__ . '() could not set the sender data. Warning: ' . Log::WARNING, 'Mail From: '.$mailfrom );
					Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
				}
			}
			catch (phpmailerException $e)
			{
				PhocacartLog::add(2, 'Error sending email', 0, __METHOD__ . '() could not set the sender data. Warning: ' . Log::WARNING, 'Mail From: '.$mailfrom );
				Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
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
?>
