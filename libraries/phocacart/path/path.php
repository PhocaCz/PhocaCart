<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class PhocaCartPath
{
	public static function getPath( $manager = '') {
	
		$group 		= PhocaCartSettings::getManagerGroup($manager);
		
		$paramsC	= JComponentHelper::getParams( 'com_phocacart' );
		// Folder where to stored files for download
		$downloadFolder		= $paramsC->get( 'download_folder', 'phocacartdownload' );
		//$downloadFolderPap	= $paramsC->get( 'download_folder_pap', 'phocadownloadpap' );
		// Absolute path which can be outside public_html - if this will be set, download folder will be ignored
		$absolutePath		= $paramsC->get( 'absolute_path', '' );
		
		// Path of preview and play
		//$downloadFolderPap 			= JPath::clean($downloadFolderPap);
		//$path['orig_abs_pap'] 		= JPATH_ROOT .  '/' . $downloadFolderPap;
		//$path['orig_abs_pap_ds'] 	= $path['orig_abs_pap'] . '/' ;
		
		$path['media_abs_front_ds']			= JPATH_ROOT . '/media/com_phocacart/images/' ;
	
		if ($group['f'] == 4) {
			// Images Categories
			$path['orig_abs'] 				= JPATH_ROOT . '/images/phocacartcategories' ;
			$path['orig_abs_ds'] 			= $path['orig_abs'] .'/' ;
			$path['orig_rel'] 				= 'images/phocacartcategories' ;
			$path['orig_rel_ds'] 			= $path['orig_rel'] . '/' ;
		} else if ($group['f'] == 5) {
			// Images Products
			$path['orig_abs'] 				= JPATH_ROOT . '/images/phocacartproducts' ;
			$path['orig_abs_ds'] 			= $path['orig_abs'] . '/';
			$path['orig_rel'] 				= 'images/phocacartproducts' ;
			$path['orig_rel_ds'] 			= $path['orig_rel'] . '/' ;
		} else if ($group['f'] == 3) {
			// Standard Path - Download	
			if ($absolutePath != '') {
				$downloadFolder 				= JPath::clean($absolutePath);
				$path['orig_abs'] 				= $downloadFolder;
				$path['orig_abs_ds'] 			= JPath::clean($path['orig_abs'] . '/');
				$path['orig_rel'] 				= '';
				$path['orig_rel_ds'] 			= '';
				
			} else {
				$downloadFolder 				= JPath::clean($downloadFolder);
				$path['orig_abs'] 				= JPATH_ROOT . '/' . $downloadFolder;
				$path['orig_abs_ds'] 			= JPATH_ROOT . '/' . $downloadFolder . '/';
				
				$downloadFolderRel 				= JPath::clean($downloadFolder);
				$path['orig_rel_ds'] 			= '../' . $downloadFolderRel;
				$path['orig_rel_ds'] 			= '../' . $downloadFolderRel .'/';
			}
		}
		return $path;
	}
	
	public static function getPathMedia() {
		
		// TO DO - create a singleton
		$option 						= 'com_phocacart';
		$instance 						= new StdClass();
		$baseFront						= JURI::root(true);
		$instance->media_css_abs		= JPATH_ROOT . '/' .  'media'. '/' .  $option . '/' .  'css' . '/';
		$instance->media_img_abs		= JPATH_ROOT . '/' .  'media'. '/' .  $option . '/' .  'images' . '/';
		$instance->media_js_abs			= JPATH_ROOT . '/' .  'media'. '/' .  $option . '/' .  'js' . '/';
		$instance->media_css_rel		= 'media/'. $option .'/css/';
		$instance->media_img_rel		= 'media/'. $option .'/images/';
		$instance->media_js_rel			= 'components/'. $option .'/assets/';
		$instance->media_css_rel_full	= $baseFront  . '/' . $instance->media_css_rel;
		$instance->media_img_rel_full	= $baseFront  . '/' . $instance->media_img_rel;
		$instance->media_js_rel_full	= $baseFront  . '/' . $instance->media_js_rel;
		return $instance;
	
	}
	
	public static function getRightPathLink($link) {
		$app    		= JApplication::getInstance('site');
		$router 		= $app->getRouter();
		$uri 			= $router->build($link);
		$uriS			= $uri->toString();
		
		$pos 			= strpos($uriS, 'administrator');
		
		if ($pos === false) {
			$uriL = str_replace(JURI::root(true), '', $uriS);
			$uriL = ltrim($uriL, '/');
			$formatLink = JURI::root(false). $uriL;
			//$formatLink = $uriS;
		} else {
			$formatLink = JURI::root(false). str_replace(JURI::root(true).'/administrator/', '', $uri->toString());
		}
		

		return $formatLink;
	}
}
?>