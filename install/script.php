<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
jimport( 'joomla.filesystem.folder' );


class com_phocacartInstallerScript
{
	function install($parent) {
		//echo '<p>' . JText::_('COM_PHOCAGALLLERY_INSTALL_TEXT') . '</p>';
		
		
		$folder[0][0]	=	'images' . '/phocacartcategories' ;
		$folder[0][1]	= 	JPATH_ROOT . '/' . $folder[0][0];
		
		$folder[1][0]	=	'images' . '/phocacartproducts' ;
		$folder[1][1]	= 	JPATH_ROOT . '/' . $folder[1][0];
		
		$folder[2][0]	=	'phocacartdownload' ;
		$folder[2][1]	= 	JPATH_ROOT . '/' . $folder[2][0];
		
		$folder[3][0]	=	'phocacartdownloadpublic' ;
		$folder[3][1]	= 	JPATH_ROOT . '/' . $folder[3][0];
		
		$folder[4][0]	=	'plugins/pcs' ; // Shipping
		$folder[4][1]	= 	JPATH_ROOT . '/' . $folder[4][0];
		
		$folder[5][0]	=	'plugins/pcp' ; // Payment 
		$folder[5][1]	= 	JPATH_ROOT . '/' . $folder[5][0];
		
		$folder[6][0]	=	'plugins/pcv' ; // View 
		$folder[6][1]	= 	JPATH_ROOT . '/' . $folder[6][0];
		
		$message = '';
		$error	 = array();
		foreach ($folder as $key => $value)
		{
			if (!JFolder::exists( $value[1]))
			{
				if (JFolder::create( $value[1], 0755 ))
				{
					
					$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
					JFile::write($value[1]."/index.html", $data);
					$message .= '<div><b><span style="color:#009933">Folder</span> ' . $value[0] 
							   .' <span style="color:#009933">created!</span></b></div>';
					$error[] = 0;
				}	 
				else
				{
					$message .= '<div><b><span style="color:#CC0033">Folder</span> ' . $value[0]
							   .' <span style="color:#CC0033">creation failed!</span></b> Please create it manually.</div>';
					$error[] = 1;
				}
			}
			else//Folder exist
			{
				$message .= '<div><b><span style="color:#009933">Folder</span> ' . $value[0] 
							   .' <span style="color:#009933">exists!</span></b></div>';
				$error[] = 0;
			}
		}

		// Enable plugins
		$db  = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set($db->quoteName('enabled') . ' = 1');
		$query->where(
		'('.$db->quoteName('name') . ' = ' . $db->quote('plg_pcp_cash_on_delivery')
		. ' OR '
		. $db->quoteName('name') . ' = ' . $db->quote('plg_pcp_paypal_standard')
		. ' OR '
		. $db->quoteName('name') . ' = ' . $db->quote('plg_pcs_shipping_standard')
		. ' OR '
		. $db->quoteName('name') . ' = ' . $db->quote('plg_pcp_pos_cash')
		. ')');
		$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
		$db->setQuery($query);
		$db->execute();
		
		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));
		
		JFactory::getApplication()->enqueueMessage($message, 'message');
		$parent->getParent()->setRedirectURL('index.php?option=com_phocacart');
		
	}
	function uninstall($parent) {
		//echo '<p>' . JText::_('COM_PHOCACART_UNINSTALL_TEXT') . '</p>';
	}

	function update($parent) {
		//echo '<p>' . JText::sprintf('COM_PHOCACART_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
		
		$folder[0][0]	=	'images/phocacartcategories' ;
		$folder[0][1]	= 	JPATH_ROOT . '/' . $folder[0][0];
		
		$folder[1][0]	=	'images/phocacartproducts' ;
		$folder[1][1]	= 	JPATH_ROOT . '/' . $folder[1][0];
		
		$folder[2][0]	=	'phocacartdownload' ;
		$folder[2][1]	= 	JPATH_ROOT . '/' . $folder[2][0];
		
		$folder[3][0]	=	'phocacartdownloadpublic' ;
		$folder[3][1]	= 	JPATH_ROOT . '/' . $folder[3][0];
		
		$folder[4][0]	=	'plugins/pcs' ;
		$folder[4][1]	= 	JPATH_ROOT . '/' . $folder[4][0];
		
		$folder[5][0]	=	'plugins/pcp' ;
		$folder[5][1]	= 	JPATH_ROOT . '/' . $folder[5][0];
		
		$folder[6][0]	=	'plugins/pcv' ;
		$folder[6][1]	= 	JPATH_ROOT . '/' . $folder[6][0];
		
		$message = '';
		$error	 = array();
		foreach ($folder as $key => $value)
		{
			if (!JFolder::exists( $value[1]))
			{
				if (JFolder::create( $value[1], 0755 ))
				{
					
					$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
					JFile::write($value[1]."/index.html", $data);
					$message .= '<div><b><span style="color:#009933">Folder</span> ' . $value[0] 
							   .' <span style="color:#009933">created!</span></b></div>';
					$error[] = 0;
				}	 
				else
				{
					$message .= '<div><b><span style="color:#CC0033">Folder</span> ' . $value[0]
							   .' <span style="color:#CC0033">creation failed!</span></b> Please create it manually.</div>';
					$error[] = 1;
				}
			}
			else//Folder exist
			{
				$message .= '<div><b><span style="color:#009933">Folder</span> ' . $value[0] 
							   .' <span style="color:#009933">exists!</span></b></div>';
				$error[] = 0;
			}
		}
		
		$msg =  JText::_('COM_PHOCACART_UPDATE_TEXT');
		$msg .= ' (' . JText::_('COM_PHOCACART_VERSION'). ': ' . $parent->get('manifest')->version . ')';
		
		//$parent->getParent()->setRedirectURL('index.php?option=com_phocacart');
		$app		= JFactory::getApplication();
		$app->enqueueMessage($msg, 'message');
		$app->redirect(JRoute::_('index.php?option=com_phocacart'));
	}

	function preflight($type, $parent) {
		//echo '<p>' . JText::_('COM_PHOCACART_PREFLIGHT_' . $type . '_TEXT') . '</p>';
		
	}

	function postflight($type, $parent)  {
		//echo '<p>' . JText::_('COM_PHOCACART_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
		
	}
}