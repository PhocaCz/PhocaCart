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

class PhocacartPlugin
{


	public static function getPluginMethods($plugin) {

		$p = array();
		if (!isset($plugin['name'])) {$p[0] = false;return $p;}
		if (!isset($plugin['group'])) {$p[0] = false;return $p;}
		if (!isset($plugin['title'])) {$plugin['title'] = '';}
		if (!isset($plugin['selectitle'])) {$plugin['selectitle'] = JText::_('COM_PHOCACART_SELECT_PLUGIN');}
		if (!isset($plugin['returnform'])) {$plugin['returnform'] = 1;}
		

		$db 	= JFactory::getDBO();
		$lang	= JFactory::getLanguage();
		$client	= JApplicationHelper::getClientInfo(0);
		$query = 'SELECT a.extension_id , a.name, a.element, a.folder'
				.' FROM #__extensions AS a'
				.' WHERE a.type = '.$db->quote('plugin')
				.' AND a.enabled = 1'
				.' AND a.folder = ' . $db->quote($plugin['group']);

		if ($plugin['name'] != '') {
			$query .= 'AND a.element = '. $db->quote($plugin['name']);
		}

		$query .= ' ORDER BY a.ordering';
		$db->setQuery($query);
		$plugins = $db->loadObjectList();


		if ($plugin['name'] == '') {
			$i 		= 0;
			$p[0]['text'] 	= '- ' .$plugin['selecttitle'].' -';
			$p[0]['value'] 	= '';
		} else {
			$i 		= -1;
		}
		if (!empty($plugins)) {
			foreach($plugins as $k => $v) {

				// Load the core and/or local language file(s).
				$folder 	= $plugin['group'];
				$element	= $v->element;
			$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, $lang->getDefault(), false, false);

				$i++;

				$name = JText::_(strtoupper($v->name) );
				$name = str_replace('Plugin', '', $name);
				$name = str_replace($plugin['title'] . ' -', '', $name);

				$p[$i]['text'] = JText::_($name);
				$p[$i]['value'] = $v->element;
			}

		}

		if ($plugin['returnform'] == 0) {
			return $plugins;
		}


		if ($plugin['name'] != '' && !empty($p[0])) {
			return $p[0];
		}
		return $p;
	}
}
?>
