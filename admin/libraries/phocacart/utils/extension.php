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
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

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

		$elementFolder = $element . $folder;

		if( is_null( $element ) ) {
			throw new Exception('Function Error: No element added', 500);
			return false;
		}

		if( !array_key_exists( $elementFolder, self::$extension ) ) {

			$db		= Factory::getDbo();
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

			$cache 			= Factory::getCache('_system','callback');
			$extensionData	=  $cache->get(array($db, 'loadObject'), null, $element, false);
			if (isset($extensionData->enabled) && $extensionData->enabled == 1) {
				self::$extension[$elementFolder] = 1;
			} else if(isset($extensionData->enabled) && $extensionData->enabled == 0) {
				self::$extension[$elementFolder] = 2;
			} else {
				self::$extension[$elementFolder] = 0;
			}
		}

		return self::$extension[$elementFolder];

	}

	public static function getExtensionLoadInfo( &$extension, $element = null, $type = 'component', $folder = '', $version = '') {

		$elementFolder = $element . $folder;

		if( is_null( $element ) ) {
			return false;
		}


		if( !array_key_exists( $elementFolder, self::$extensionLoad ) ) {


			$table 					= Table::getInstance('extension');

			$key = array();
			$key['type']	= $type;
			$key['element']	= $element;
			if ($type == 'plugin') {
				$key['folder'] = $folder;
			}

			$extensionId = $table->find((array)$key);


			if (isset($extensionId) && (int)$extensionId > 0 && $table->load((int)$extensionId)){


				$extension['installed'] = true;
				$extension['enabled']   = (bool) $table->enabled;

				if (!empty($table->manifest_cache)) {
					$manifest = json_decode($table->manifest_cache);

					if (version_compare($extension['version'], @$manifest->version, 'gt')) {
						$extension['versioncurrent'] = $manifest->version;
					}
				}
			}
			self::$extensionLoad[$elementFolder] = $extension;
		}

		return self::$extensionLoad[$elementFolder];
	}


	public static function getExtensionsObtainTypeButton($type, $downloadSource, $extension) {


		// We are now in Joomla 4
		$download = '';
		if (isset($downloadSource['4']) && $downloadSource['4'] != '') {
			$download = $downloadSource['4'];// $downloadSource['5']

		}

		$extensionVersion = $extension['version4'];// $extension['version5']


		$s = PhocacartRenderStyle::getStyles();
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


		if (($type == 0 || $type == 4) && $extension['installed'] == false) {

			$link  	= $download;
			$icon 	= 'shopping-cart';
			$class	= 'btn-buy';
			$text	= Text::_('COM_PHOCACART_BUY_NOW');
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
							$text	= Text::_('COM_PHOCACART_REQUEST') . ' ('.$extensionVersion.')';
							$target = 'target="_blank"';

							$iconTxt 	= 'ok';
							$classTxt 	= 'ph-success-txt';
							$textTxt 	= Text::_('COM_PHOCACART_INSTALLED');

						} else if ($type == 4) {
							// 0 - Paid but update - installed but updated paid version found
							$link = $download;
							$icon 	= 'shopping-cart';
							$class	= 'btn-buy';
							$text	= Text::_('COM_PHOCACART_REQUEST') . ' ('.$extensionVersion.')';
							$target = 'target="_blank"';

							$iconTxt 	= 'ok';
							$classTxt 	= 'ph-success-txt';
							$textTxt 	= Text::_('COM_PHOCACART_INSTALLED');

						} else if ($type == 1) {
							// Direct Install
							$link   = '';
							if ($download != '') {
								$link = Route::_('index.php?option=com_phocacart&task=phocacartextension.install&link=' . base64_encode($download) . '&' . JSession::getFormToken() . '=1', false);//SEC
							}
							$icon 	= 'refresh';
							$class	= 'btn-success';
							$text	= Text::_('COM_PHOCACART_UPDATE') . ' ('.$extensionVersion.')';
							$target = '';

							$iconTxt 	= 'ok';
							$classTxt 	= 'ph-success-txt';
							$textTxt 	= Text::_('COM_PHOCACART_INSTALLED');

						} else {
							// 2 - Download, 3 Download/Register
							$link = $download;
							$icon 	= 'download';
							$class	= 'btn-primary';
							$text	= Text::_('COM_PHOCACART_DOWNLOAD') . ' ('.$extensionVersion.')';
							$target = 'target="_blank"';

							$iconTxt 	= 'ok';
							$classTxt 	= 'ph-success-txt';
							$textTxt 	= Text::_('COM_PHOCACART_INSTALLED');
						}

					} else {

						// Installed - version OK
						$iconTxt 	= 'ok';
						$classTxt 	= 'ph-success-txt';
						$textTxt 	= Text::_('COM_PHOCACART_INSTALLED');
					}
				} else {

					$iconTxt 	= 'remove';
					$classTxt 	= 'ph-disabled-txt';
					$textTxt 	= Text::_('COM_PHOCACART_DISABLED');
				}
			} else {
				if ($type == 1) {
					// Direct Install
					$link   = '';
					if ($download != '') {
						$link = Route::_('index.php?option=com_phocacart&task=phocacartextension.install&link=' . base64_encode($download) . '&' . Session::getFormToken() . '=1', false);
					}
					$icon 	= 'download-alt';
					$class	= 'btn-success';
					$text	= Text::_('COM_PHOCACART_INSTALL');
					$target = '';

				} else {
					// 2 - Download, 3 Download/Register
					$link 	= $download;
					$icon 	= 'download';
					$class	= 'btn-primary';
					$text	= Text::_('COM_PHOCACART_DOWNLOAD');
					$target = 'target="_blank"';
				}
			}
		}

		$o .= '<div class="ph-center ph-extension-button">';
		if ($textTxt != '' && $classTxt != '' && $iconTxt != '') {
			$o .= '<div class="'.$classTxt.'"><span class="'.$s['i'][$iconTxt].'"></span> '.$textTxt.'</div>';
		}
		if ($link != '' && $icon != '' && $text != '') {

			$o .= '<a href="'.$link.'" '.$target.' class="btn btn-small '.$class.'">';
			$o .= '<span class="'.$s['i'][$icon].'"></span> ';
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
