<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\Constants\WishListType;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocaCartCpModelPhocacartWishlist extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	public function getTable($type = 'PhocacartWishlist', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		return $this->loadForm('com_phocacart.phocacartwishlist', 'phocacartwishlist', array('control' => 'jform', 'load_data' => $loadData));
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartwishlist.data', []);

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		$table->title		= htmlspecialchars_decode((string)$table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
            $table->date = Factory::getDate()->toSql();

            // Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = $this->getDatabase();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_wishlists WHERE user_id = '. (int) $table->user_id);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'user_id = '. (int) $table->user_id;
		return $condition;
	}

    public function sendWatchdogEmails() : bool
    {
        $params = PhocacartUtils::getComponentParameters();
        if (!$params->get('watchdog_enable', 0)) {
            $this->setError(Text::_('COM_PHOCACART_ERROR_WATCHDOG_NOT_ENABLED'));
            return false;
        }

        $db = $this->getDatabase();
        $limit = $params->get('watchdog_send_limit', 20);

        $app   = Factory::getApplication();
        if (I18nHelper::useI18n()) {
            $defLang = I18nHelper::getDefLanguage();
        } else {
            $defLang = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
        }
        $language = $app->getLanguage();

        // First find users that signed to any of products, which is on stock again
        $query = $db->getQuery(true)
            ->select('DISTINCT u.id, u.name, u.username, u.email, w.language')
            ->from($db->quoteName('#__phocacart_wishlists', 'w'))
            ->join('INNER', $db->quoteName('#__phocacart_products', 'p'), 'p.id = w.product_id')
            ->join('INNER', $db->quoteName('#__users', 'u'), 'u.id = w.user_id')
            ->where('w.type = ' . WishListType::WatchDog)
            ->where('p.stock > 0')
            ->where('p.published = 1')
            ->where('u.block = 0')
            ->order('w.id')
            ->setLimit($limit + 1);

        $db->setQuery($query);
        $users = $db->loadObjectList();

        if (count($users) > $limit) {
            $this->setState('watchdog_repeat', true);
            array_pop($users);
        } else {
            $this->setState('watchdog_repeat', false);
        }

        $successCount = 0;

        foreach ($users as $user) {
            $lang = $user->language;

            // Now load products for this user
            $query = $db->getQuery(true)
                ->select('w.id, p.title, p.title_long, p.alias, p.sku, p.catid')
                ->select('GROUP_CONCAT(DISTINCT c.id) as catid, COUNT(pc.category_id) AS count_categories, p.catid AS preferred_catid');

            if (I18nHelper::isI18n()) {
                $query->select(I18nHelper::sqlCoalesce(['title', 'alias'], 'c', 'cat', 'groupconcatdistinct', '', '', false, true));
            }
            $query->from($db->quoteName('#__phocacart_wishlists', 'w'))
                ->join('INNER', $db->quoteName('#__phocacart_products', 'p'), 'p.id = w.product_id')
                ->join('LEFT', $db->quoteName('#__phocacart_product_categories', 'pc'), 'pc.product_id =  p.id')
                ->join('LEFT', $db->quoteName('#__phocacart_categories', 'c'), 'c.id =  pc.category_id')
                ->where('w.type = ' . WishListType::WatchDog)
                ->where('w.user_id = ' . $user->id)
                ->where('w.language = ' . $db->quote($lang))
                ->where('p.stock > 0')
                ->order('w.id')
                ->setLimit($limit);
            I18nHelper::query($query, '#__phocacart_products_i18n', ['title' => '', 'alias' => ''], ['title_long' => ''], 'p', $lang);
            I18nHelper::query($query, '#__phocacart_categories_i18n', [], [], 'c', $lang);

            $db->setQuery($query);
            $products = $db->loadObjectList('id');

            if (!$lang || $lang === '*') {
                $lang = $defLang;
            }

            $mailProducts = [];
            foreach ($products as $product) {

                  if (isset($product->count_categories) && (int)$product->count_categories > 1) {

                        $catidA	        = explode(',', $product->catid);
                        $cattitleA	    = explode(',', $product->cattitle);
                        $cataliasA	    = explode(',', $product->catalias);
                        if (isset($product->preferred_catid) && (int)$product->preferred_catid > 0) {
                            $key  = array_search((int)$product->preferred_catid, $catidA);
                        } else {
                            $key = 0;
                        }
                        $product->catid	    = $catidA[$key];
                        $product->cattitle 	= $cattitleA[$key];
                        $product->catalias 	= $cataliasA[$key];
                  }

                // TODO Force useI18n from admin
                $mailProducts[] = [
                    'product_title' => $product->title_long ?: $product->title,
                    'product_sku'  => $product->sku,
                    'product_url'  => Route::link('site', PhocacartRoute::getProductCanonicalLink($product->id, $product->catid, $product->alias, $product->catalias, (int)$product->preferred_catid, $lang), false, Route::TLS_IGNORE, true),
                ];
            }

            $mailData = [
                'user_name' => $user->name,
                'user_username' => $user->username,
                'user_email' => $user->email,
                'products' => $mailProducts,
                'site_name' => $app->get('sitename'),
                'site_url' => Uri::root(),
            ];

            /* TODO bypass Joomla Issue https://github.com/joomla/joomla-cms/issues/39228 */
            $language->load('com_phocacart', JPATH_ADMINISTRATOR, $lang, true);
            $language->load('com_phocacart', JPATH_SITE, $lang, true);
            $mailer = new MailTemplate('com_phocacart.watchdog', $lang);
            $mailer->addTemplateData($mailData);
            $mailer->addRecipient($user->email, $user->name);
            try {
                $mailer->send();

                // Finally delete sent products
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__phocacart_wishlists'))
                    ->whereIn('id', array_keys($products));

                $db->setQuery($query);
                $db->execute();

                $successCount++;
            } catch (\Exception $exception) {
                try {
                    $this->setError(Text::sprintf('COM_PHOCACART_ERROR_WATCHDOG_EMAIL_ERROR', $user->email));
                    Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');
                } catch (\RuntimeException $exception) {
                    Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
                }
            }
        }

        $this->setState('watchdog_count', $successCount);
        return true;
    }
}

