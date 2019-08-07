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

class PhocacartFeed
{
	public static function getFeed($id) {

		$db = JFactory::getDBO();

		$q = ' SELECT a.id, a.title, a.item_params, a.feed_params, a.feed_plugin, a.header, a.footer, a.root, a.item'
			.' FROM #__phocacart_feeds AS a'
			.' WHERE a.id = '.(int) $id
			.' AND a.published = 1';
		$db->setQuery($q);

		$feed = $db->loadAssoc();

		if (!empty($feed)) {
			return $feed;
		}

		return false;
	}


	public static function getFeedPluginMethods($namePlugin = '', $returnFormItem = 0) {

		$db 	= JFactory::getDBO();
		$lang	= JFactory::getLanguage();
		$client	= JApplicationHelper::getClientInfo(0);
		$query = 'SELECT a.extension_id , a.name, a.element, a.folder'
				.' FROM #__extensions AS a'
				.' WHERE a.type = '.$db->quote('plugin')
				.' AND a.enabled = 1'
				.' AND a.folder = ' . $db->quote('pcf');

		if ($namePlugin != '') {
			$query .= 'AND a.element = '. $db->quote($namePlugin);
		}

		$query .= ' ORDER BY a.ordering';
		$db->setQuery($query);
		$plugins = $db->loadObjectList();


		if ($namePlugin == '') {
			$i 		= 0;
			$p[0]['text'] 	= '- ' .JText::_('COM_PHOCACART_SELECT_FEED_PLUGIN').' -';
			$p[0]['value'] 	= '';
		} else {
			$i 		= -1;
		}
		if (!empty($plugins)) {
			foreach($plugins as $k => $v) {

				// Load the core and/or local language file(s).
				$folder 	= 'pcf';
				$element	= $v->element;
			$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, $lang->getDefault(), false, false);

				$i++;

				$name = JText::_(strtoupper($v->name) );
				$name = str_replace('Plugin', '', $name);
				$name = str_replace('Phoca Cart Payment -', '', $name);

				$p[$i]['text'] = JText::_($name);
				$p[$i]['value'] = $v->element;
			}

		}

		if ($returnFormItem == 0) {
			return $plugins;
		}


		if ($namePlugin != '' && !empty($p[0])) {
			return $p[0];
		}


		return $p;



	}
}
?>
