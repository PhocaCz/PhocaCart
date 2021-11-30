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
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;



class PhocacartNewsletter {



	public static function storeSubscriber($name, $email, $privacy) {


	    // PHOCA EMAIL COMPONENT NEEDED
        $comPhocaemail	= PhocacartUtilsExtension::getExtensionInfo('com_phocaemail');
        if($comPhocaemail) {


            $lang = Factory::getLanguage();
            $lang->load('com_phocaemail');

            if (File::exists(JPATH_ADMINISTRATOR . '/components/com_phocaemail/helpers/phocaemail.php')) {
                require_once(JPATH_ADMINISTRATOR . '/components/com_phocaemail/helpers/phocaemail.php');
            }
            if (File::exists(JPATH_ADMINISTRATOR . '/components/com_phocaemail/helpers/phocaemailutils.php')) {
                require_once(JPATH_ADMINISTRATOR . '/components/com_phocaemail/helpers/phocaemailutils.php');
            }
            if (File::exists(JPATH_ADMINISTRATOR . '/components/com_phocaemail/helpers/phocaemailsendnewsletteremail.php')) {
                require_once(JPATH_ADMINISTRATOR . '/components/com_phocaemail/helpers/phocaemailsendnewsletteremail.php');
            }

            if (File::exists(JPATH_ADMINISTRATOR . '/components/com_phocaemail/tables/phocaemailsubscriber.php')) {
                require_once(JPATH_ADMINISTRATOR . '/components/com_phocaemail/tables/phocaemailsubscriber.php');
            }
            Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_phocaemail/tables');


            $app = Factory::getApplication();
            $db = Factory::getDBO();
            $pE = ComponentHelper::getParams('com_phocaemail');
            $pC = PhocacartUtils::getComponentParameters();

            $newsletter_enable = $pC->get('newsletter_enable', 0);
            $newsletter_activate = $pC->get('newsletter_activate', 0);
            $newsletter_mailinglist = $pC->get('newsletter_mailinglist', array());

            if ($newsletter_enable == 0) {
                return false;
            }


            $data = array();

            $data['name'] = $name;
            $data['email'] = $email;
            $data['privacy'] = (int)$privacy;
            $data['date'] = gmdate('Y-m-d H:i:s');
            $data['date_register'] = gmdate('Y-m-d H:i:s');
            $data['token'] = PhocaEmailHelper::getToken();

            $data['active'] = 0;
            if ($newsletter_activate == 1){
                $data['active'] = 1;
                $data['date_active'] = gmdate('Y-m-d H:i:s');
            }
            $data['published']  	= 1;
            $data['hits'] 		    = 0;
            $data['type'] 		    = 2;// Phoca Cart

            // Test - if there is active user, inactive user with many requests,
            $query = 'SELECT a.id, a.active, a.hits'
                . ' FROM #__phocaemail_subscribers AS a'
                . ' WHERE a.email = '.$db->quote($data['email'])
                . ' LIMIT 1';
            $db->setQuery( $query );
            $userSub = $db->loadObject();

            // X) ACTIVE USER
            if (isset($userSub->active) && $userSub->active == 1) {
                return false;//COM_PHOCAEMAIL_YOUR_SUBSCRIPTION_IS_ACTIVE
            }

            // X) UPDATE HITS - ATTEMPTS
            if (isset($userSub->hits) && (int)$userSub->hits > 0) {
                $userSub->hits++;// This attempts must be counted
                $data['hits'] = (int)$userSub->hits;
            } else {
                $data['hits'] = 1;
            }

            // X) NOT ACTIVE BUT STORED IN DATABASE
            $allowedHits = (int)$pE->get('count_subscription', 5);

            if (isset($userSub->hits) && (int)$userSub->hits > (int)$allowedHits) {
                return false;//COM_PHOCAEMAIL_YOUR_SUBSCRIPTION_IS_BLOCKED_PLEASE_CONTACT_ADMINISTRATOR
            }

            // X) USER EXISTS BUT IS INACTIVE AND ALLOWED TO SUBSCRIBE
            if (isset($userSub->active) && (int)$userSub->active != 1 && isset($userSub->id) && (int)$userSub->id > 0) {
                $data['id'] = (int)$userSub->id;
            }

            // X) SEEMS LIKE USER IS NOT IN DATABASE, ADD IT - user id will be automatically created
            // ... ok

            // X) IF REGISTERED USER - ASSIGN AN ACCOUNT TO HIM/HER
            $query = 'SELECT u.id'
                . ' FROM #__users AS u'
                . ' WHERE u.email = '.$db->quote($data['email'])
                . ' LIMIT 1';
            $db->setQuery( $query );
            $registeredUser = $db->loadObject();
            if (isset($registeredUser->id) && $registeredUser->id > 0) {
                $data['userid'] = (int)$registeredUser->id;
            }


            $row = Table::getInstance('phocaemailsubscriber', 'Table', array());



            if (!$row->bind($data)) {
                $db->setError($row->getError());
                return false;
            }

            if (!$row->check()) {
                $db->setError($row->getError());
                return false;
            }

            if (!$row->store()) {
                $db->setError($row->getError());
                return false;
            }

            if (!empty($newsletter_mailinglist) && (int)$row->id > 0) {
                PhocaEmailSendNewsletterEmail::storeLists((int)$row->id, $newsletter_mailinglist, '#__phocaemail_subscriber_lists', 'id_subscriber');
            }

            if ($newsletter_activate == 2){
				// Send activation email
				$send = PhocaEmailSendNewsletterEmail::sendNewsLetterEmail($name, $email, 'activate');
				if ($send) {

				} else {

				}
			}

            return true;
        }

	}

	public static function updateNewsletterInfoByUser($userId, $newsletter = 0) {


		$db 	= Factory::getDBO();
		$query = ' UPDATE #__phocacart_users'
				.' SET newsletter = '.(int)$newsletter
				.' WHERE user_id = '.(int)$userId;
		$db->setQuery($query);
		$db->execute();
		return true;
	}

}
