<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartFeed
{
	public static function getFeed($id) {
	
		$db = JFactory::getDBO();
		
		$q = ' SELECT a.id, a.title, a.item_params, a.feed_params, a.header, a.footer, a.root, a.item'
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
}
?>