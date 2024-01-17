<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
jimport( 'joomla.application.component.view');

class PhocaCartCpViewPhocaCartImageA extends HtmlView
{
	function display($tpl = null){

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}

		$pC 				       	= PhocacartUtils::getComponentParameters();
		$width 						= $pC->get( 'copypaste_width', 0 );// 448;
		$height 					= $pC->get( 'copypaste_height', 0 );// 338;
		$copypaste_overwrite_file 	= $pC->get( 'copypaste_overwrite_file', 1 );
		$copypaste_enable			= $pC->get( 'copypaste_enable', 0 );
		$copypaste_folder_creation	= $pC->get( 'copypaste_folder_creation', 0 );
		$copypaste_folder			= $pC->get( 'copypaste_folder', '' );

		$copypaste_folder			= PhocacartText::filterValue($copypaste_folder, 'folder');

		$app			= Factory::getApplication();
		$title			= $app->input->get( 'imagetitle', '', 'string'  );
		$format			= $app->input->get( 'imageformat', '', 'string'  );
		$image			= $app->input->get( 'image', '', 'base64'  );

		$sE = '<span class="ph-result-txt ph-error-txt">';
		$eE = '</span>';
		$sM = '<span class="ph-result-txt ph-success-txt">';
		$eM = '</span>';

		if ($copypaste_enable == 0) {
			$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR_COPY_PASTE_IMAGES_FUNCTION_DISABLED') . $eE);
			echo json_encode($response);
			return;
		}


		$image 			= base64_decode($image);
		if ($image == '') {
			$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR_NO_IMAGE_PASTED') . $eE);
			echo json_encode($response);
			return;
		}

		$path = PhocacartPath::getPath('productimage');






		$imgFormat = 'png';
		if (strpos($format, 'image/jpg') !== false || strpos($format, 'image/jpeg') !== false) {
			$imgFormat = 'jpg';
		} else if (strpos($format, 'image/avif') !== false) {
			$imgFormat = 'avif';
		} else if (strpos($format, 'image/webp') !== false) {
			$imgFormat = 'webp';
		}

		$imgName = strtolower(trim(PhocacartText::filterValue($title, 'alphanumeric'))). '.'.$imgFormat;

		if ($copypaste_folder_creation == 0) {
			$folder = '';
		} else if ($copypaste_folder_creation == 1 ) {
			$folder = $copypaste_folder;
			if ($folder != '') {

				if (!Joomla\CMS\Filesystem\Folder::exists($path['orig_abs_ds'] . $folder)){
					if (!Joomla\CMS\Filesystem\Folder::create($path['orig_abs_ds'] . $folder)){
						$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR_WRITING_FOLDER') . $eE);
						echo json_encode($response);
						return;
					}
				}

				$folder = $folder . '/';
			}

		} else if ($copypaste_folder_creation == 2) {
			$folder = substr($imgName, 0, 1);
			if ($folder != '') {
				if (!Joomla\CMS\Filesystem\Folder::exists($path['orig_abs_ds'] . $folder)) {
					if (!Joomla\CMS\Filesystem\Folder::create($path['orig_abs_ds'] . $folder)) {
						$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR_WRITING_FOLDER') . $eE);
						echo json_encode($response);
						return;
					}
				}

				$folder = $folder . '/';
			}
		}

		$pathImageName = $path['orig_abs_ds'] . $folder .$imgName;


		if (Joomla\CMS\Filesystem\File::exists($pathImageName) && $copypaste_overwrite_file == 0) {

			$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR_IMAGE_ALREADY_EXITS') . $eE);
			echo json_encode($response);
			return;
		} else {
			if (Joomla\CMS\Filesystem\File::write($pathImageName, $image)) {
				if (Joomla\CMS\Filesystem\File::exists($pathImageName)) {

					list($w, $h, $type) = GetImageSize($pathImageName);

					$width = $width == 0 ? $w : $width;
					$height = $height == 0 ? $h : $height;

					$scale = (($width / $w) > ($height / $h)) ? ($width / $w) : ($height / $h); // greater rate
					$newW = $width/$scale;    // check the size of in file
					$newH = $height/$scale;

					$fileIn = $pathImageName;
					$fileOut= $pathImageName;

					// which side is larger (rounding error)
					if (($w - $newW) > ($h - $newH)) {
						$src = array(floor(($w - $newW)/2), 0, floor($newW), $h);
					} else {
						//$src = array(0, floor(($h - $newH)/2), $w, floor($newH));
						$src = array(0, 0, $w, floor($newH));// go from top
					}

					$dst = array(0,0, floor($width), floor($height));

					switch($type) {
						case IMAGETYPE_JPEG:
							if (!function_exists('ImageCreateFromJPEG')) {
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoJPGFunction' . $eE);
								echo json_encode($response);
								return;
							}
							try {
								$image1 = ImageCreateFromJPEG($fileIn);
							} catch(\Exception $exception) {
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoJPGFunction' . $eE);
								echo json_encode($response);
								return;
							}
						break;

						case IMAGETYPE_PNG :
							if (!function_exists('ImageCreateFromPNG')) {
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoPNGFunction' . $eE);
								echo json_encode($response);
								return;
							}
							try {
								$image1 = ImageCreateFromPNG($fileIn);
							} catch(\Exception $exception) {
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoPNGFunction' . $eE);
								echo json_encode($response);
								return;
							}
						break;

						case IMAGETYPE_WEBP:
							if (!function_exists('ImageCreateFromWEBP')) {
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoWEBPFunction' . $eE);
								echo json_encode($response);
								return;
							}
							//$image1 = ImageCreateFromGIF($fileIn);
							try {
								$image1 = ImageCreateFromWEBP($fileIn);
							} catch(\Exception $exception) {
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoWEBPFunction' . $eE);
								echo json_encode($response);
								return;
							}
						break;

						case IMAGETYPE_AVIF:
							if (!function_exists('ImageCreateFromAVIF')) {
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoAVIFFunction' . $eE);
								echo json_encode($response);
								return;
							}
							try {
								$image1 = ImageCreateFromAVIF($fileIn);
							} catch(\Exception $exception) {
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoAVIFFunction' . $eE);
								echo json_encode($response);
								return;
							}
						break;

						default:
							$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNotSupportedImage' . $eE);
							echo json_encode($response);
							return;
						break;
					}

					if ($image1) {

						$image2 = @ImageCreateTruecolor($dst[2], $dst[3]);
						if (!$image2) {
							$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoImageCreateTruecolor' . $eE);
							echo json_encode($response);
							return;
						}


						ImageCopyResampled($image2, $image1, $dst[0], $dst[1], $src[0], $src[1], $dst[2], $dst[3], $src[2], $src[3]);


						switch ($type) {
							case IMAGETYPE_JPEG:
								if (!function_exists('ImageJPEG')) {
									$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoJPGFunction' . $eE);
									echo json_encode($response);
									return;
								}

								if (!@ImageJPEG($image2, $fileOut, 100)) {

									$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR_WRITING_IMAGE_FILE') . $eE);
									echo json_encode($response);
									return;
								}
							break;

							case IMAGETYPE_PNG :
								if (!function_exists('ImagePNG')) {
									$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoPNGFunction' . $eE);
									echo json_encode($response);
									return;
								}

								if (!@ImagePNG($image2, $fileOut)) {
									$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorWriteFile' . $eE);
									echo json_encode($response);
									return;
								}

							break;

							case IMAGETYPE_WEBP :
								if (!function_exists('ImageWEBP')) {
									$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoWEBPFunction' . $eE);
									echo json_encode($response);
									return;
								}

								if (!@imagewebp($image2, $fileOut)) {
									$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR_WRITING_IMAGE_FILE') . $eE);
									echo json_encode($response);
									return;
								}
							break;

							case IMAGETYPE_AVIF :
								if (!function_exists('ImageAVIF')) {
									$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNoAVIFFunction' . $eE);
									echo json_encode($response);
									return;
								}

								if (!@imageavif($image2, $fileOut)) {
									$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR_WRITING_IMAGE_FILE') . $eE);
									echo json_encode($response);
									return;
								}
							break;

							default:
								$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorNotSupportedImage' . $eE);
								echo json_encode($response);
								return;
							break;
						}

						ImageDestroy($image1);
						ImageDestroy($image2);
					}

				}
			} else {
				$response = array('status' => '0', 'error' => $sE . Text::_('COM_PHOCACART_ERROR') . ': ErrorWriteFile' . $eE);
				echo json_encode($response);
				return;
			}

			$response = array(
			'status'	=> '1',
			'file'		=> $folder .$imgName,
			'message' 	=> $sM . Text::_('COM_PHOCACART_SUCCESS_IMAGE_PASTED') . $eM);
			echo json_encode($response);
			return;

		}
	}
}
?>
