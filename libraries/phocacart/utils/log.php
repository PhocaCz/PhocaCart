<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
class PhocaCartLog
{
	public static function add( $type = 0, $title = '', $typeid = 0, $description = '') {
	
		$paramsC 			= JComponentHelper::getParams('com_phocacart');
		$enable_logging		= $paramsC->get( 'enable_logging', 0 );
		
		if ($enable_logging == 0) {
			return false;
		}
	
		if ((int)$type > 0 && $title != '' ) {
			$uri			= JFactory::getUri();
			$user			= JFactory::getUser();
			$db				= JFactory::getDBO();
			$ip 			= $_SERVER["REMOTE_ADDR"];
			$incoming_page	= htmlspecialchars($uri->toString());
			$userid			= 0;
			if (isset($user->id) && (int)$user->id > 0) {
				$userid = $user->id;
			}
			
			
			$query = ' INSERT INTO #__phocacart_logs ('
			.$db->quoteName('user_id').', '
			.$db->quoteName('type_id').', '
			.$db->quoteName('type').', '
			.$db->quoteName('title').', '
			.$db->quoteName('ip').', '
			.$db->quoteName('incoming_page').', '
			.$db->quoteName('description').', '
			.$db->quoteName('published').', '
			.$db->quoteName('date').' )'
			. ' VALUES ('
			.$db->quote((int)$userid).', '
			.$db->quote((int)$typeid).', '
			.$db->quote((int)$type).', '
			.$db->quote($title).', '
			.$db->quote($ip).', '
			.$db->quote($incoming_page).', '
			.$db->quote($description).', '
			.$db->quote('1').', '
			.$db->quote(gmdate('Y-m-d H:i:s')).' )';
			
			$db->setQuery($query);
			$db->execute();

			return true;
		}
		return false;
		
	}
}
?>