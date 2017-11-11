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
defined( '_JEXEC' ) or die( 'Restricted access' );

class PhocacartUtilsExtension
{
	private static $extension = array();
	private static $extensionLoad = array();
	
	private function __construct(){}
	
	/**
	 * Get information about extension.
	 *
	 * @param	string	Extension element (com_cpanel, com_admin, ...)
	 * @param	string	Extension type (component, plugin, module, ...)
	 * @param	string	Folder type (content, editors, search, ...)
	 *
	 * @return	int ( 0 ... extension not installed
	 *                1 ... extension installed and enabled
	 *                2 ... extension installed but not enabled )
	 */

	public static function getExtensionInfo( $element = null, $type = 'component', $folder = '' ) {
		if( is_null( $element ) ) {
			throw new Exception('Function Error: No element added', 500);
			return false;
		}
		if( !array_key_exists( $element, self::$extension ) ) {
			
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			if ($type == 'component'){
				$query->select('extension_id AS id, element AS "option", params, enabled');
			} else {
				$query->select('extension_id AS "id", element AS "element", params, enabled');
			}
			$query->from('#__extensions');
			$query->where('`type` = '.$db->quote($type));
			if ($folder != '') {
				$query->where('`folder` = '.$db->quote($folder));
			}
			$query->where('`element` = '.$db->quote($element));
			$db->setQuery($query);
			
			$cache 			= JFactory::getCache('_system','callback');
			$extensionData	=  $cache->get(array($db, 'loadObject'), null, $element, false);
			if (isset($extensionData->enabled) && $extensionData->enabled == 1) {
				self::$extension[$element] = 1;
			} else if(isset($extensionData->enabled) && $extensionData->enabled == 0) {
				self::$extension[$element] = 2;
			} else {
				self::$extension[$element] = 0;
			}
		}
		
		return self::$extension[$element];
		
	}
	
	public static function getExtensionLoadInfo( &$extension, $element = null, $type = 'component', $folder = '', $version = '') {
		
		if( is_null( $element ) ) {
			return false;
		}
		
		if( !array_key_exists( $element, self::$extensionLoad ) ) {
			
			
			$table 					= JTable::getInstance('Extension', 'JTable');

			$key['type']	= $type;
			$key['element']	= $element;
			if ($type == 'plugin') {
				$key['folder'] = $folder;
			}
			
			if ($table->load($key)){
				
				$extension['installed'] = true;
				$extension['enabled']   = (bool) $table->enabled;
				$manifest  		= json_decode($table->manifest_cache);
				
				if (version_compare($extension['version'], @$manifest->version, 'gt')){
					$extension['versioncurrent'] = $manifest->version;
				}
			}
			self::$extensionLoad[$element] = $extension;
		}
		
		return self::$extensionLoad[$element];
	}
	
	
	public static function getExtensionsObtainTypeButton($type, $download, $extension) {
		
		$o 		= '';
		
		// BUTTON
		$link 		= '';
		$icon 		= '';
		$text 		= '';
		$class		= '';
		$target 	= '';
		
		// TEXT
		$iconTxt 	= '';
		$classTxt 	= '';
		$textTxt 	= '';
		
		if ($type == 0 && $extension['installed'] == false) {
			
			$link  	= $download;
			$icon 	= 'shopping-cart';
			$class	= 'btn-buy';
			$text	= JText::_('COM_PHOCACART_BUY_NOW');
			$target = 'target="_blank"';
			
		} else  {
			if ($extension['installed']) {
				if ($extension['enabled']) {
					if ($extension['versioncurrent']) {
						
						if ($type == 0) {
							// 0 - Paid but update - installed but updated paid version found
							$link = $download;
							$icon 	= 'shopping-cart';
							$class	= 'btn-buy';
							$text	= JText::_('COM_PHOCACART_REQUEST') . ' ('.$extension['version'].')';
							$target = 'target="_blank"';
							
							$iconTxt 	= 'ok';
							$classTxt 	= 'ph-success-txt';
							$textTxt 	= JText::_('COM_PHOCACART_INSTALLED');
							
						} else if ($type == 1) {
							// Direct Install
							$link = JRoute::_('index.php?option=com_phocacart&task=phocacartextension.install&link=' . base64_encode($download) . '&' . JSession::getFormToken() . '=1', false);//SEC
							$icon 	= 'refresh';
							$class	= 'btn-success';
							$text	= JText::_('COM_PHOCACART_UPDATE') . ' ('.$extension['version'].')';
							$target = '';
							
							$iconTxt 	= 'ok';
							$classTxt 	= 'ph-success-txt';
							$textTxt 	= JText::_('COM_PHOCACART_INSTALLED');
							
						} else {
							// 2 - Download, 3 Download/Register
							$link = $download;
							$icon 	= 'download';
							$class	= 'btn-primary';
							$text	= JText::_('COM_PHOCACART_DOWNLOAD') . ' ('.$extension['version'].')';
							$target = 'target="_blank"';
							
							$iconTxt 	= 'ok';
							$classTxt 	= 'ph-success-txt';
							$textTxt 	= JText::_('COM_PHOCACART_INSTALLED');
						}
						
					} else {
						
						// Installed - version OK
						$iconTxt 	= 'ok';
						$classTxt 	= 'ph-success-txt';
						$textTxt 	= JText::_('COM_PHOCACART_INSTALLED');
					}
				} else {
					
					$iconTxt 	= 'remove';
					$classTxt 	= 'ph-disabled-txt';
					$textTxt 	= JText::_('COM_PHOCACART_DISABLED');
				}
			} else {
				if ($type == 1) {
					// Direct Install
					$link 	= JRoute::_('index.php?option=com_phocacart&task=phocacartextension.install&link=' . base64_encode($download) . '&' . JSession::getFormToken() . '=1', false);
					$icon 	= 'download-alt';
					$class	= 'btn-success';
					$text	= JText::_('COM_PHOCACART_INSTALL');
					$target = '';
					
				} else {
					// 2 - Download, 3 Download/Register
					$link 	= $download;
					$icon 	= 'download';
					$class	= 'btn-primary';
					$text	= JText::_('COM_PHOCACART_DOWNLOAD');
					$target = 'target="_blank"';
				}
			}
		}
		
		$o .= '<div class="ph-center ph-extension-button">';
		if ($textTxt != '' && $classTxt != '' && $iconTxt != '') {
			$o .= '<div class="'.$classTxt.'"><span class="glyphicon glyphicon-'.$iconTxt.'"></span> '.$textTxt.'</div>';
		}
		if ($link != '' && $icon != '' && $text != '') {
			$o .= '<a href="'.$link.'" '.$target.' class="btn btn-small '.$class.'">';
			$o .= '<span class="glyphicon glyphicon-'.$icon.'"></span> ';
			$o .= $text . '</a>';
		}
		$o .= '</div>';
		
		
		return $o;
		
	}
	
	
	
	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>