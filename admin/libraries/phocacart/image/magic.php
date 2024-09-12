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
use Joomla\CMS\Filesystem\File;
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class PhocacartImageMagic
{
	/**
	* need GD library (first PHP line WIN: dl("php_gd.dll"); UNIX: dl("gd.so");
	* www.boutell.com/gd/
	* interval.cz/clanky/php-skript-pro-generovani-galerie-obrazku-2/
	* cz.php.net/imagecopyresampled
	* www.linuxsoft.cz/sw_detail.php?id_item=871
	* www.webtip.cz/art/wt_tech_php/liquid_ir.html
	* php.vrana.cz/zmensovani-obrazku.php
	* diskuse.jakpsatweb.cz/
	*
	* @param string $fileIn Vstupni soubor (mel by existovat)
	* @param string $fileOut Vystupni soubor, null ho jenom zobrazi (taky kdyz nema pravo se zapsat :)
	* @param int $width Vysledna sirka (maximalni)
	* @param int $height Vysledna vyska (maximalni)
	* @param bool $crop Orez (true, obrazek bude presne tak velky), jinak jenom Resample (udane maximalni rozmery)
	* @param int $typeOut IMAGETYPE_type vystupniho obrazku
	* @return bool Chyba kdyz vrati false
	*/
	public static function imageMagic($fileIn, $fileOut = null, $width = null, $height = null, $crop = null, $typeOut = null, $watermarkParams = array(), $frontUpload = 0, $manager = '', &$errorMsg = '') {

		$params 		= PhocacartUtils::getComponentParameters();
		$jfile_thumbs	= $params->get( 'jfile_thumbs', 1 );
		$jpeg_quality	= $params->get( 'thumbnail_quality', 100 );
		$jpeg_quality	= PhocacartImage::getJpegQuality($jpeg_quality);
		$webp_quality	= $params->get( 'thumbnail_quality', 100 );
		$webp_quality	= PhocacartImage::getJpegQuality($webp_quality);
		$avif_quality	= $params->get( 'thumbnail_quality', 100 );
		$avif_quality	= PhocacartImage::getJpegQuality($avif_quality);

		$png_quality	= $params->get( 'thumbnail_quality', 100 );
		$png_quality	= PhocacartImage::getPngQuality($png_quality);

		$create_webp_copy	= $params->get( 'create_webp_copy', 0);

		$fileWatermark = '';

		/* // While front upload we don't display the process page
		if ($frontUpload == 0) {
			$stopText = PhocacartRenderProcess::displayStopThumbnailsCreating();
			echo $stopText;
		}*/
		// Memory - - - - - - - -
/*		$memory = 8;
		$memoryLimitChanged = 0;
		$memory = (int)ini_get( 'memory_limit' );
		if ($memory == 0) {
			$memory = 8;
		}*/
		// - - - - - - - - - - -

		if ($fileIn !== '' && File::exists($fileIn)) {

			// array of width, height, IMAGETYPE, "height=x width=x" (string)
	        list($w, $h, $type) = GetImageSize($fileIn);

			if ($w > 0 && $h > 0) {// we got the info from GetImageSize

		        // size of the image
		        if ($width == null || $width == 0) { // no width added
		            $width = $w;
		        }
				else if ($height == null || $height == 0) { // no height, adding the same as width
		            $height = $width;
		        }
				if ($height == null || $height == 0) { // no height, no width
		            $height = $h;
		        }

		        // miniaturizing
		        if (!$crop) { // new size - nw, nh (new width/height)
		            $scale = (($width / $w) < ($height / $h)) ? ($width / $w) : ($height / $h); // smaller rate
		            $src = array(0,0, $w, $h);
		            $dst = array(0,0, floor($w*$scale), floor($h*$scale));
		        }
		        else { // will be cropped
		            $scale = (($width / $w) > ($height / $h)) ? ($width / $w) : ($height / $h); // greater rate
		            $newW = $width/$scale;    // check the size of in file
		            $newH = $height/$scale;

		            // which side is larger (rounding error)
		            if (($w - $newW) > ($h - $newH)) {
		                $src = array(floor(($w - $newW)/2), 0, floor($newW), $h);
		            }
		            else {
		                $src = array(0, floor(($h - $newH)/2), $w, floor($newH));
		            }

		            $dst = array(0,0, floor($width), floor($height));
		        }

				// Watermark - - - - - - - - - - -
				if (!empty($watermarkParams) && ($watermarkParams['create'] == 1 || $watermarkParams['create'] == 2)) {

					$thumbnailSmall		= false;
					$thumbnailMedium	= false;
					$thumbnailLarge		= false;
					$thumb_name_prefix = 'phoca_thumb';


					$thumbnailMedium	= preg_match("/".$thumb_name_prefix."_m_/i", $fileOut);
					$thumbnailLarge 	= preg_match("/".$thumb_name_prefix."_l_/i", $fileOut);

					$path				= PhocacartPath::getPath($manager);
					$fileName 			= PhocacartFile::getTitleFromFilenameWithExt($fileIn);

					// Which Watermark will be used
					// If watermark is in current directory use it else use Default
					$fileWatermarkMedium		= false;
					$fileWatermarkLarge			= false;
					$fileWatermarkMediumPng  	= str_replace($fileName, 'watermark-medium.png', $fileIn);
					$fileWatermarkLargePng  	= str_replace($fileName, 'watermark-large.png', $fileIn);
					$fileWatermarkMediumWebp  	= str_replace($fileName, 'watermark-medium.webp', $fileIn);
					$fileWatermarkLargeWebp  	= str_replace($fileName, 'watermark-large.webp', $fileIn);
					$fileWatermarkMediumAvif  	= str_replace($fileName, 'watermark-medium.avif', $fileIn);
					$fileWatermarkLargeAvif  	= str_replace($fileName, 'watermark-large.avif', $fileIn);

					$fileWatermarkMediumRoot		= false;
					$fileWatermarkLargeRoot			= false;
					$fileWatermarkMediumPngRoot  	= $path['orig_abs_ds'] . 'watermark-medium.png';
					$fileWatermarkLargePngRoot  	= $path['orig_abs_ds'] . 'watermark-large.png';
					$fileWatermarkMediumWebpRoot  	= $path['orig_abs_ds'] . 'watermark-medium.webp';
					$fileWatermarkLargeWebpRoot  	= $path['orig_abs_ds'] . 'watermark-large.webp';
					$fileWatermarkMediumAvifRoot  	= $path['orig_abs_ds'] . 'watermark-medium.avif';
					$fileWatermarkLargeAvifRoot  	= $path['orig_abs_ds'] . 'watermark-large.avif';

					if ($type == IMAGETYPE_WEBP) {
						if (File::exists($fileWatermarkMediumWebp)) {
							$fileWatermarkMedium = $fileWatermarkMediumWebp;
						} else if (File::exists($fileWatermarkMediumPng)) {
							$fileWatermarkMedium = $fileWatermarkMediumPng;
						} else if (File::exists($fileWatermarkMediumAvif)) {
							$fileWatermarkMedium = $fileWatermarkMediumAvif;
						}

						if (File::exists($fileWatermarkMediumWebpRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumWebpRoot;
						} else if (File::exists($fileWatermarkMediumPngRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumPngRoot;
						} else if (File::exists($fileWatermarkMediumAvifRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumAvifRoot;
						}

						if (File::exists($fileWatermarkLargeWebp)) {
							$fileWatermarkLarge = $fileWatermarkLargeWebp;
						} else if (File::exists($fileWatermarkLargePng)) {
							$fileWatermarkLarge = $fileWatermarkLargePng;
						} else if (File::exists($fileWatermarkLargeAvif)) {
							$fileWatermarkLarge = $fileWatermarkLargeAvif;
						}

						if (File::exists($fileWatermarkLargeWebpRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargeWebpRoot;
						} else if (File::exists($fileWatermarkLargePngRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargePngRoot;
						} else if (File::exists($fileWatermarkLargeAvifRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargeAvifRoot;
						}

					} else if ($type ==  IMAGETYPE_AVIF){
						if (File::exists($fileWatermarkMediumAvif)) {
							$fileWatermarkMedium = $fileWatermarkMediumAvif;
						} else if (File::exists($fileWatermarkMediumPng)) {
							$fileWatermarkMedium = $fileWatermarkMediumPng;
						} else if (File::exists($fileWatermarkMediumWebp)) {
							$fileWatermarkMedium = $fileWatermarkMediumWebp;
						}

						if (File::exists($fileWatermarkMediumAvifRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumAvifRoot;
						} else if (File::exists($fileWatermarkMediumPngRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumPngRoot;
						} else if (File::exists($fileWatermarkMediumWebpRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumWebpRoot;
						}

						if (File::exists($fileWatermarkLargeAvif)) {
							$fileWatermarkLarge = $fileWatermarkLargeAvif;
						} else if (File::exists($fileWatermarkLargePng)) {
							$fileWatermarkLarge = $fileWatermarkLargePng;
						} else if (File::exists($fileWatermarkLargeWebp)) {
							$fileWatermarkLarge = $fileWatermarkLargeWebp;
						}

						if (File::exists($fileWatermarkLargeAvifRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargeAvifRoot;
						} else if (File::exists($fileWatermarkLargePngRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargePngRoot;
						} else if (File::exists($fileWatermarkLargeWebpRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargeWebpRoot;
						}

					} else {
						if (File::exists($fileWatermarkMediumPng)) {
							$fileWatermarkMedium = $fileWatermarkMediumPng;
						} else if (File::exists($fileWatermarkMediumWebp)) {
							$fileWatermarkMedium = $fileWatermarkMediumWebp;
						} else if (File::exists($fileWatermarkMediumAvif)) {
							$fileWatermarkMedium = $fileWatermarkMediumAvif;
						}

						if (File::exists($fileWatermarkMediumPngRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumPngRoot;
						} else if (File::exists($fileWatermarkMediumWebpRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumWebpRoot;
						} else if (File::exists($fileWatermarkMediumAvifRoot)) {
							$fileWatermarkMediumRoot = $fileWatermarkMediumAvifRoot;
						}

						if (File::exists($fileWatermarkLargePng)) {
							$fileWatermarkLarge = $fileWatermarkLargePng;
						} else if (File::exists($fileWatermarkLargeWebp)) {
							$fileWatermarkLarge = $fileWatermarkLargeWebp;
						} else if (File::exists($fileWatermarkLargeAvif)) {
							$fileWatermarkLarge = $fileWatermarkLargeAvif;
						}

						if (File::exists($fileWatermarkLargePngRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargePngRoot;
						} else if (File::exists($fileWatermarkLargeWebpRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargeWebpRoot;
						} else if (File::exists($fileWatermarkLargeAvifRoot)) {
							$fileWatermarkLargeRoot = $fileWatermarkLargeAvifRoot;
						}
					}


					clearstatcache();

					// Which Watermark will be used
					if ($thumbnailMedium) {
						if ($watermarkParams['create'] == 1 && $fileWatermarkMedium) {
							$fileWatermark  = $fileWatermarkMedium;
						} else if ($watermarkParams['create'] == 2 && $fileWatermarkMediumRoot) {
							$fileWatermark = $fileWatermarkMediumRoot;
						} else if ($fileWatermarkMedium) {
							$fileWatermark = $fileWatermarkMedium;
						} else if ($fileWatermarkMediumRoot) {
							$fileWatermark = $fileWatermarkMediumRoot;
						} else {
							$fileWatermark	= '';
						}
					} else if ($thumbnailLarge) {
						if ($watermarkParams['create'] == 1 && $fileWatermarkLarge) {
							$fileWatermark  = $fileWatermarkLarge;
						} else if ($watermarkParams['create'] == 2 && $fileWatermarkLargeRoot) {
							$fileWatermark = $fileWatermarkLargeRoot;
						} else if ($fileWatermarkLarge) {
							$fileWatermark = $fileWatermarkLarge;
						} else if ($fileWatermarkLargeRoot) {
							$fileWatermark = $fileWatermarkLargeRoot;
						} else {
							$fileWatermark	= '';
						}
					} else {
						$fileWatermark  = '';
					}


					if (!File::exists($fileWatermark)) {
						$fileWatermark = '';
					}

					if ($fileWatermark != '') {
						list($wW, $hW, $typeW)	= GetImageSize($fileWatermark);


						switch ($watermarkParams['x']) {
							case 'left':
								$locationX	= 0;
							break;

							case 'right':
								$locationX	= $dst[2] - $wW;
							break;

							case 'center':
							Default:
								$locationX	= ($dst[2] / 2) - ($wW / 2);
							break;
						}

						switch ($watermarkParams['y']) {
							case 'top':
								$locationY	= 0;
							break;

							case 'bottom':
								$locationY	= $dst[3] - $hW;
							break;

							case 'middle':
							Default:
								$locationY	= ($dst[3] / 2) - ($hW / 2);
							break;
						}
					}
				} else {
					$fileWatermark = '';
				}
			}



			/*if ($memory < 50) {
				ini_set('memory_limit', '50M');
				$memoryLimitChanged = 1;
			}*/
			// Resampling
			// in file

			// Watemark
			if ($fileWatermark != '') {

				$ext = File::getExt($fileWatermark);

				if ($ext == 'webp') {
					if (!function_exists('ImageCreateFromWEBP')) {
						$errorMsg = 'ErrorNoWEBPFunction';
						return false;
					}
					$waterImage1=ImageCreateFromWEBP($fileWatermark);
					//imagealphablending($waterImage1, false);
					//imagesavealpha($waterImage1, true);
				} else if ($ext == 'avif') {
					if (!function_exists('ImageCreateFromAVIF')) {
						$errorMsg = 'ErrorNoAVIFFunction';
						return false;
					}
					$waterImage1=ImageCreateFromAVIF($fileWatermark);
					//imagealphablending($waterImage1, false);
					//imagesavealpha($waterImage1, true);
				} else {
					if (!function_exists('ImageCreateFromPNG')) {
						$errorMsg = 'ErrorNoPNGFunction';
						return false;
					}
					$waterImage1=ImageCreateFromPNG($fileWatermark);
					//imagealphablending($waterImage1, false);
					//imagesavealpha($waterImage1, true);
				}

			}

			// End Watermark - - - - - - - - - - - - - - - - - -

	        switch($type) {
	            case IMAGETYPE_JPEG:
					if (!function_exists('ImageCreateFromJPEG')) {
						$errorMsg = 'ErrorNoJPGFunction';
						return false;
					}
					//$image1 = ImageCreateFromJPEG($fileIn);
					try {
					    $image1 = ImageCreateFromJPEG($fileIn);
                    } catch(\Exception $exception) {
                        $errorMsg = 'ErrorJPGFunction';
                        return false;
                    }
                break;

	            case IMAGETYPE_PNG :
					if (!function_exists('ImageCreateFromPNG')) {
						$errorMsg = 'ErrorNoPNGFunction';
						return false;
					}
					//$image1 = ImageCreateFromPNG($fileIn);
					try {
                        $image1 = ImageCreateFromPNG($fileIn);
                    } catch(\Exception $exception) {
						$errorMsg = 'ErrorPNGFunction';
						return false;
					}
                break;

	            case IMAGETYPE_GIF :
					if (!function_exists('ImageCreateFromGIF')) {
						$errorMsg = 'ErrorNoGIFFunction';
						return false;
					}
					//$image1 = ImageCreateFromGIF($fileIn);
					try {
                        $image1 = ImageCreateFromGIF($fileIn);
                    } catch(\Exception $exception) {
                        $errorMsg = 'ErrorGIFFunction';
                        return false;
                    }
                break;

                case IMAGETYPE_WEBP:
                    if (!function_exists('ImageCreateFromWEBP')) {
                        $errorMsg = 'ErrorNoWEBPFunction';
                        return false;
                    }
                    //$image1 = ImageCreateFromGIF($fileIn);
                    try {
                        $image1 = ImageCreateFromWEBP($fileIn);
                    } catch(\Exception $exception) {
                        $errorMsg = 'ErrorWEBPFunction';
                        return false;
                    }
                break;
					case IMAGETYPE_AVIF:
					if (!function_exists('imagecreatefromavif')) {
						$errorMsg = 'ErrorNoAVIFFunction';
						return false;
					}
					//$image1 = ImageCreateFromGIF($fileIn);
					try {
						$image1 = imagecreatefromavif($fileIn);
					} catch(\Exception $exception) {
						$errorMsg = 'ErrorAVIFFunction';
						return false;
					}
				break;
	            case IMAGETYPE_WBMP:
					if (!function_exists('ImageCreateFromWBMP')) {
						$errorMsg = 'ErrorNoWBMPFunction';
						return false;
					}
					//$image1 = ImageCreateFromWBMP($fileIn);
					try{
					    $image1 = ImageCreateFromWBMP($fileIn);
                    } catch(\Exception $exception) {
                        $errorMsg = 'ErrorWBMPFunction';
                        return false;
                    }
                break;
	            default:
					$errorMsg = 'ErrorNotSupportedImage';
					return false;
                break;
	        }

			if ($image1) {

				$image2 = @ImageCreateTruecolor($dst[2], $dst[3]);
				if (!$image2) {
					$errorMsg = 'ErrorNoImageCreateTruecolor';
					return false;
				}

				switch($type) {
					case IMAGETYPE_PNG:
                    case IMAGETYPE_WEBP:
					case IMAGETYPE_AVIF:
						// Possible FR FR1
						$correctWhite = 0;
						if ($correctWhite == 1) {
							// It can happen that GD makes the white background with very wrong quality
							// - white color will be dirty (this happens on JPG or not transparent PNG)
							// So we cannot use JPG or not transparent PNG
							// And when we use transparent PNG, it can have bad quality of borders - gritty
							// So we will use transparent PNG as source but we want to do not transparent
							// PNG as destination. Normally in such case the PNG has black background
							// so we need to add white retangle as background - such white background will
							// be nice without dirty effects
							$white = imagecolorallocate($image2,  255, 255, 255);
							imagefilledrectangle($image2, 0, 0, $dst[2],$dst[3], $white);
						} else {
							//imagealphablending($image1, false);
							@imagealphablending($image2, false);
							//imagesavealpha($image1, true);
							@imagesavealpha($image2, true);
						}



					break;
				}

				ImageCopyResampled($image2, $image1, $dst[0],$dst[1], $src[0],$src[1], $dst[2],$dst[3], $src[2],$src[3]);

				// Watermark - - - - - -
				if ($fileWatermark != '') {


					//imagecolortransparent($waterImage1, imagecolorallocate($waterImage1, 0, 0, 0));
					//imagepalettetotruecolor($waterImage1);
                	//imagealphablending($waterImage1, true);
                	//imagesavealpha($waterImage1, true);
					//imagecolortransparent($image2, imagecolorallocate($image2, 0, 0, 0));
					//imagepalettetotruecolor($image2);
                	imagealphablending($image2, true);// Needed for webp and avif transparency
                	//imagesavealpha($image2, true);
					ImageCopy($image2, $waterImage1, (int)$locationX, (int)$locationY, 0, 0, (int)$wW, (int)$hW);
				}
				// End Watermark - - - -


	            // Display the Image - not used
	            if ($fileOut == null) {
	                header("Content-type: ". image_type_to_mime_type($typeOut));
	            }

				// Create the file
		        if ($typeOut == null) {    // no bitmap
		            $typeOut = ($type == IMAGETYPE_WBMP) ? IMAGETYPE_PNG : $type;
		        }

				switch($typeOut) {
		            case IMAGETYPE_JPEG:
						if (!function_exists('ImageJPEG')) {
							$errorMsg = 'ErrorNoJPGFunction';
							return false;
						}

						if ($jfile_thumbs == 1) {
							ob_start();
							if (!@ImageJPEG($image2, NULL, $jpeg_quality)) {
								ob_end_clean();
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
							$imgJPEGToWrite = ob_get_contents();
							ob_end_clean();

							if(!File::write( $fileOut, $imgJPEGToWrite)) {
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
						} else {
							if (!@ImageJPEG($image2, $fileOut, $jpeg_quality)) {
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
						}

						// WEBP COPY
						if($create_webp_copy == 1) {
							if (!self::doWebpCopy($image1, $dst, $src, $fileOut, $jfile_thumbs, $errorMsg)) {
								return false;
							}
						}


						// Possible FR FR2 - adding copyright of IPTC to thumbnails
						/*
						$fileIn ... original image
						$fileOut ... destination image (e.g. thumbnail)

						$copyright = array();
						$info = array();
						$data = '';
						$size = getimagesize($fileIn, $info);
						if(isset($info['APP13'])){
							$iptc = iptcparse($info['APP13']);
							if(isset($iptc['2#116'][0]) && $iptc['2#116'][0] != ''){
								$iptcEmbed = array('2#116' => $iptc['2#116'][0]);
								foreach($iptcEmbed as $tag => $string) {
									$tag = substr($tag, 2);
									// iptc_make_tag function can be found here, see below:
									// https://www.php.net/manual/en/function.iptcembed.php (example 1)
									$data .= iptc_make_tag(2, $tag, $string);
								}
								$content = iptcembed($data, $fileOut);
								// User Joomla! methods to write files
								$fw = fopen($fileOut, 'w');
								fwrite($fw, $content);
								fclose($fw);
							}
						}

						function iptc_make_tag($rec, $data, $value) {
							$length = strlen($value);
							$retval = chr(0x1C) . chr($rec) . chr($data);

							if($length < 0x8000)
							{
								$retval .= chr($length >> 8) .  chr($length & 0xFF);
							}
							else
							{
								$retval .= chr(0x80) .
										   chr(0x04) .
										   chr(($length >> 24) & 0xFF) .
										   chr(($length >> 16) & 0xFF) .
										   chr(($length >> 8) & 0xFF) .
										   chr($length & 0xFF);
							}

							return $retval . $value;
						}

						*/
					break;

					case IMAGETYPE_PNG :
						if (!function_exists('ImagePNG')) {
							$errorMsg = 'ErrorNoPNGFunction';
							return false;
						}

						if ($jfile_thumbs == 1) {
							ob_start();
							if (!@ImagePNG($image2, NULL, $png_quality)) {
								ob_end_clean();
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
							$imgPNGToWrite = ob_get_contents();
							ob_end_clean();

							if(!File::write( $fileOut, $imgPNGToWrite)) {
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
						} else {
							if (!@ImagePNG($image2, $fileOut, $png_quality)) {
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
						}
						// WEBP COPY
						if($create_webp_copy == 1) {
							if (!self::doWebpCopy($image1, $dst, $src, $fileOut, $jfile_thumbs, $errorMsg)) {
								return false;
							}
						}
					break;

					case IMAGETYPE_GIF :
						if (!function_exists('ImageGIF')) {
							$errorMsg = 'ErrorNoGIFFunction';
							return false;
						}

						if ($jfile_thumbs == 1) {
							ob_start();
							if (!@ImageGIF($image2, NULL)) {
								ob_end_clean();
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
							$imgGIFToWrite = ob_get_contents();
							ob_end_clean();

							if(!File::write( $fileOut, $imgGIFToWrite)) {
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
						} else {
							if (!@ImageGIF($image2, $fileOut)) {
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
						}

						// WEBP COPY
						if($create_webp_copy == 1) {
							if (!self::doWebpCopy($image1, $dst, $src, $fileOut, $jfile_thumbs, $errorMsg)) {
								return false;
							}
						}
					break;

                    case IMAGETYPE_WEBP :
                        if (!function_exists('ImageWEBP')) {
                            $errorMsg = 'ErrorNoWEBPFunction';
                            return false;
                        }

                        if ($jfile_thumbs == 1) {
                            ob_start();
                            if (!@imagewebp($image2, NULL, $webp_quality)) {
                                ob_end_clean();
                                $errorMsg = 'ErrorWriteFile';
                                return false;
                            }
                            $imgWEBPToWrite = ob_get_contents();
                            ob_end_clean();

                            if(!File::write( $fileOut, $imgWEBPToWrite)) {
                                $errorMsg = 'ErrorWriteFile';
                                return false;
                            }
                        } else {
							if (!@imagewebp($image2, $fileOut, $webp_quality)) {
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
						}
					break;

					case IMAGETYPE_AVIF :
						if (!function_exists('ImageAVIF')) {
							$errorMsg = 'ErrorNoAVIFFunction';
							return false;
						}

						if ($jfile_thumbs == 1) {
							ob_start();
							if (!@imageavif($image2, NULL, $avif_quality)) {
								ob_end_clean();
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
							$imgAVIFToWrite = ob_get_contents();
							ob_end_clean();

							if(!File::write( $fileOut, $imgAVIFToWrite)) {
								$errorMsg = 'ErrorWriteFile';
								return false;
							}
						} else {
							if (!@imageavif($image2, $fileOut, $avif_quality)) {
                                $errorMsg = 'ErrorWriteFile';
                                return false;
                            }
                        }

						// WEBP COPY
						if($create_webp_copy == 1) {
							if (!self::doWebpCopy($image1, $dst, $src, $fileOut, $jfile_thumbs, $errorMsg)) {
								return false;
							}
						}
                    break;

					default:
						$errorMsg = 'ErrorNotSupportedImage';
						return false;
                    break;
				}

				// free memory
				ImageDestroy($image1);
	            ImageDestroy($image2);
				if (isset($waterImage1)) {
					ImageDestroy($waterImage1);
				}

				/*if ($memoryLimitChanged == 1) {
					$memoryString = $memory . 'M';
					ini_set('memory_limit', $memoryString);
				}*/
	             $errorMsg = ''; // Success
				 return true;
	        } else {
				$errorMsg = 'Error1';
				return false;
			}
			/*if ($memoryLimitChanged == 1) {
				$memoryString = $memory . 'M';
				ini_set('memory_limit', $memoryString);
			}*/
	    }
		$errorMsg = 'Error2';
		return false;
	}

	public static function doWebpCopy($image1, $dst, $src, $fileOut, $jfile_thumbs, &$errorMsg) {


		$params 		= PhocacartUtils::getComponentParameters();
		$jfile_thumbs	= $params->get( 'jfile_thumbs', 1 );
		$thumb_quality	= $params->get( 'thumbnail_quality', 100 );
		$thumb_quality	= PhocacartImage::getJpegQuality($thumb_quality);

		$image2 = @ImageCreateTruecolor($dst[2], $dst[3]);
		if (!$image2) {
			$errorMsg = 'ErrorNoImageCreateTruecolor';
			return false;
		}

		//imagealphablending($image1, false);
		@imagealphablending($image2, false);
		//imagesavealpha($image1, true);
		@imagesavealpha($image2, true);


		ImageCopyResampled($image2, $image1, $dst[0],$dst[1], $src[0],$src[1], $dst[2],$dst[3], $src[2],$src[3]);

		if (!function_exists('ImageWEBP')) {
			$errorMsg = 'ErrorNoWEBPFunction';
			return false;
		}

		$fileOut = PhocacartFile::changeFileExtension($fileOut, 'webp');

		if ($jfile_thumbs == 1) {
			ob_start();
			if (!@imagewebp($image2, NULL, $thumb_quality)) {
				ob_end_clean();
				$errorMsg = 'ErrorWriteFile';
				return false;
			}
			$imgWEBPToWrite = ob_get_contents();
			ob_end_clean();

			if(!File::write( $fileOut, $imgWEBPToWrite)) {
				$errorMsg = 'ErrorWriteFile';
				return false;
			}
		} else {
			if (!@imagewebp($image2, $fileOut, $thumb_quality)) {
				$errorMsg = 'ErrorWriteFile';
				return false;
			}
		}
		return true;

	}
}
?>
