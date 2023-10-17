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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Application\CMSApplication;

class PhocacartPath
{
	public static function getPath( $manager = '') {

		$group 		= PhocacartUtilsSettings::getManagerGroup($manager);



		$app			= Factory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		// Folder where to stored files for download
		$productImagePath		= $paramsC->get( 'product_image_path', 'images/phocacartproducts' );
		$categoryImagePath		= $paramsC->get( 'category_image_path', 'images/phocacartcategories' );
		$downloadFolder			= $paramsC->get( 'download_folder', 'phocacartdownload' );
		$downloadFolderPublic	= $paramsC->get( 'public_download_folder', 'phocacartdownloadpublic' );
		$attachmentFolder		= $paramsC->get( 'attachment_folder', 'phocacartattachment' );
		$uploadFolder			= $paramsC->get( 'upload_folder', 'phocacartupload' );// frontend upload
		// Absolute path which can be outside public_html - if this will be set, download folder will be ignored
		$absolutePath		= $paramsC->get( 'absolute_path', '' );
		$absolutePathUpload	= $paramsC->get( 'absolute_path_upload', '' );

		// Path of preview and play
		//$downloadFolderPap 			= JPath::clean($downloadFolderPap);
		//$path['orig_abs_pap'] 		= JPATH_ROOT .  '/' . $downloadFolderPap;
		//$path['orig_abs_pap_ds'] 	= $path['orig_abs_pap'] . '/' ;

		$path['media_abs_front_ds']			= JPATH_ROOT . '/media/com_phocacart/images/' ;

		if ($group['f'] == 8) {
			$attachmentFolder 				= Path::clean($attachmentFolder);
			$path['orig_abs'] 				= JPATH_ROOT . '/' . $attachmentFolder;
			$path['orig_abs_ds'] 			= JPATH_ROOT . '/' . $attachmentFolder . '/';

			$attachmentFolderRel 			= Path::clean($attachmentFolder);
			$path['orig_rel'] 				= '../' . $attachmentFolderRel;
			$path['orig_rel_ds'] 			= '../' . $attachmentFolderRel .'/';
			$path['orig_rel_path'] 			= Uri::root() . $attachmentFolderRel;
			$path['orig_rel_path_ds'] 		= Uri::root() . $attachmentFolderRel .'/';

		} else if ($group['f'] == 6) {
			$downloadFolderPublic 			= Path::clean($downloadFolderPublic);
			$path['orig_abs'] 				= JPATH_ROOT . '/' . $downloadFolderPublic;
			$path['orig_abs_ds'] 			= JPATH_ROOT . '/' . $downloadFolderPublic . '/';

			$downloadFolderPublicRel 		= Path::clean($downloadFolderPublic);
			$path['orig_rel'] 				= '../' . $downloadFolderPublicRel;
			$path['orig_rel_ds'] 			= '../' . $downloadFolderPublicRel .'/';
			$path['orig_rel_path'] 			= Uri::root() . $downloadFolderPublicRel;
			$path['orig_rel_path_ds'] 		= Uri::root() . $downloadFolderPublicRel .'/';

		} else if ($group['f'] == 4) {
			// Images Categories
			$path['orig_abs'] 				= JPATH_ROOT . '/' . $categoryImagePath ;
			$path['orig_abs_ds'] 			= $path['orig_abs'] .'/' ;
			$path['orig_rel'] 				= $categoryImagePath ;
			$path['orig_rel_ds'] 			= $path['orig_rel'] . '/' ;
		} else if ($group['f'] == 5) {
			// Images Products
			$path['orig_abs'] 				= JPATH_ROOT . '/' . $productImagePath ;
			$path['orig_abs_ds'] 			= $path['orig_abs'] . '/';
			$path['orig_rel'] 				= $productImagePath ;
			$path['orig_rel_ds'] 			= $path['orig_rel'] . '/' ;
		}  else if ($group['f'] == 7) {
			// Standard Path - Upload
			if ($absolutePathUpload != '') {
				$uploadFolder 				    = Path::clean($absolutePathUpload);
				$path['orig_abs'] 				= $uploadFolder;
				$path['orig_abs_ds'] 			= Path::clean($path['orig_abs'] . '/');
				$path['orig_rel'] 				= '';
				$path['orig_rel_ds'] 			= '';

			} else {
				$uploadFolder 				    = Path::clean($uploadFolder);
				$path['orig_abs'] 				= JPATH_ROOT . '/' . $uploadFolder;
				$path['orig_abs_ds'] 			= JPATH_ROOT . '/' . $uploadFolder . '/';

				$uploadFolderRel 				= Path::clean($uploadFolder);
				$path['orig_rel_ds'] 			= '../' . $uploadFolderRel;
				$path['orig_rel_ds'] 			= '../' . $uploadFolderRel .'/';
				$path['orig_rel_path'] 			= Uri::root() . $uploadFolderRel;
				$path['orig_rel_path_ds'] 		= Uri::root() . $uploadFolderRel .'/';
			}
		} else if ($group['f'] == 3) {
			// Standard Path - Download
			if ($absolutePath != '') {
				$downloadFolder 				= Path::clean($absolutePath);
				$path['orig_abs'] 				= $downloadFolder;
				$path['orig_abs_ds'] 			= Path::clean($path['orig_abs'] . '/');
				$path['orig_rel'] 				= '';
				$path['orig_rel_ds'] 			= '';

			} else {
				$downloadFolder 				= Path::clean($downloadFolder);
				$path['orig_abs'] 				= JPATH_ROOT . '/' . $downloadFolder;
				$path['orig_abs_ds'] 			= JPATH_ROOT . '/' . $downloadFolder . '/';

				$downloadFolderRel 				= Path::clean($downloadFolder);
				$path['orig_rel_ds'] 			= '../' . $downloadFolderRel;
				$path['orig_rel_ds'] 			= '../' . $downloadFolderRel .'/';
				$path['orig_rel_path'] 			= Uri::root() . $downloadFolderRel;
				$path['orig_rel_path_ds'] 		= Uri::root() . $downloadFolderRel .'/';
			}
		} else {
			$path['orig_abs'] 				= JPATH_ROOT . '/tmp';
			$path['orig_abs_ds'] 			= JPATH_ROOT . '/tmp/';
			$path['orig_rel'] 				= '';
			$path['orig_rel_ds'] 			= '';
		}
		return $path;
	}

	public static function getPathMedia() {

		// TO DO - create a singleton
		$option 						= 'com_phocacart';
		$instance 						= new StdClass();
		$baseFront						= Uri::root(true);
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
		//$app    		= CMSApplication::getInstance('site');
		//$router 		= $app->getRouter();
		//$uri 			= $router->build($link);
		$router 		= Factory::getContainer()->get(SiteRouter::class);
		$uri             = $router->build($link);
		$uriS			= $uri->toString();
		$pos 			= strpos($uriS, 'administrator');

		if ($pos === false) {
			$uriL = str_replace(Uri::root(true), '', $uriS);
			$uriL = ltrim($uriL, '/');
			$formatLink = Uri::root(false). $uriL;

			//$formatLink = $uriS;
		} else {
			$formatLink = Uri::root(false). str_replace(Uri::root(true).'/administrator/', '', $uri->toString());
		}


		return $formatLink;
	}
}
?>
