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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class PhocacartFileUpload
{
    public static function realMultipleUpload($frontEnd = 0)
    {


        $app = Factory::getApplication();
        $paramsC = PhocacartUtils::getComponentParameters();
        $chunkMethod = $paramsC->get('multiple_upload_chunk', 0);
        $uploadMethod = $paramsC->get('multiple_upload_method', 4);

        $overwriteExistingFiles = $paramsC->get('overwrite_existing_files', 0);

        $app->allowCache(false);

        // Chunk Files
        header('Content-type: text/plain; charset=UTF-8');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Invalid Token
        Session::checkToken('request') or jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 100,
            'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
            'details' => Text::_('COM_PHOCACART_INVALID_TOKEN'))));

        // Set FTP credentials, if given
        $ftp = ClientHelper::setCredentialsFromRequest('ftp');


        //$file 			= Factory::getApplication()->input->get( 'file', '', 'files', 'array' );
        $file = Factory::getApplication()->input->files->get('file', null, 'raw');
        $chunk = Factory::getApplication()->input->get('chunk', 0, '', 'int');
        $chunks = Factory::getApplication()->input->get('chunks', 0, '', 'int');
        $folder = Factory::getApplication()->input->get('folder', '', '', 'path');
        $manager = Factory::getApplication()->input->get('manager', 'file', '', 'string');


        $path = PhocacartPath::getPath($manager);// we use viewback to get right path


        // Make the filename safe
        if (isset($file['name'])) {
            $file['name'] = File::makeSafe($file['name']);
        }
        if (isset($folder) && $folder != '') {
            $folder = $folder . '/';
        }

        $chunkEnabled = 0;
        // Chunk only if is enabled and only if flash is enabled
        if (($chunkMethod == 1 && $uploadMethod == 1) || ($frontEnd == 0 && $chunkMethod == 0 && $uploadMethod == 1)) {
            $chunkEnabled = 1;
        }


        if (isset($file['name'])) {


            // - - - - - - - - - -
            // Chunk Method
            // - - - - - - - - - -
            // $chunkMethod = 1, for frontend and backend
            // $chunkMethod = 0, only for backend
            if ($chunkEnabled == 1) {

                // If chunk files are used, we need to upload parts to temp directory
                // and then we can run e.g. the condition to recognize if the file already exists
                // We must upload the parts to temp, in other case we get everytime the info
                // that the file exists (because the part has the same name as the file)
                // so after first part is uploaded, in fact the file already exists
                // Example: NOT USING CHUNK
                // If we upload abc.jpg file to server and there is the same file
                // we compare it and can recognize, there is one, don't upload it again.
                // Example: USING CHUNK
                // If we upload abc.jpg file to server and there is the same file
                // the part of current file will overwrite the same file
                // and then (after all parts will be uploaded) we can make the condition to compare the file
                // and we recognize there is one - ok don't upload it BUT the file will be damaged by
                // parts uploaded by the new file - so this is why we are using temp file in Chunk method
                $stream = Factory::getStream();// Chunk Files
                $tempFolder = 'pcpluploadtmpfolder' . '/';
                $filepathImgFinal = Path::clean($path['orig_abs_ds'] . $folder . strtolower($file['name']));
                $filepathImgTemp = Path::clean($path['orig_abs_ds'] . $folder . $tempFolder . strtolower($file['name']));
                $filepathFolderFinal = Path::clean($path['orig_abs_ds'] . $folder);
                $filepathFolderTemp = Path::clean($path['orig_abs_ds'] . $folder . $tempFolder);
                $maxFileAge = 60 * 60; // Temp file age in seconds
                $lastChunk = $chunk + 1;
                $realSize = 0;


                // Get the real size - if chunk is uploaded, it is only a part size, so we must compute all size
                // If there is last chunk we can computhe the whole size
                if ($lastChunk == $chunks) {
                    if (File::exists($filepathImgTemp) && File::exists($file['tmp_name'])) {
                        $realSize = filesize($filepathImgTemp) + filesize($file['tmp_name']);
                    }
                }

                // 5 minutes execution time
                @set_time_limit(5 * 60);// usleep(5000);

                // If the file already exists on the server:
                // - don't copy the temp file to final
                // - remove all parts in temp file
                // Because some parts are uploaded before we can run the condition
                // to recognize if the file already exists.

                // Files should be overwritten
                if ($overwriteExistingFiles == 1) {
                    File::delete($filepathImgFinal);
                }

                if (File::exists($filepathImgFinal)) {
                    if ($lastChunk == $chunks) {
                        @Folder::delete($filepathFolderTemp);
                    }


                    jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 108,
                        'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                        'details' => Text::_('COM_PHOCACART_FILE_ALREADY_EXISTS'))));

                }

                if (!PhocacartFileUpload::canUpload($file, $errUploadMsg, $manager, $frontEnd, $chunkEnabled, $realSize)) {

                    // If there is some error, remove the temp folder with temp files
                    if ($lastChunk == $chunks) {
                        @Folder::delete($filepathFolderTemp);
                    }
                    jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 104,
                        'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                        'details' => Text::_($errUploadMsg))));
                }

                // Ok create temp folder and add chunks
                if (!Folder::exists($filepathFolderTemp)) {
                    @Folder::create($filepathFolderTemp);
                }

                // Remove old temp files
                if (Folder::exists($filepathFolderTemp)) {
                    $dirFiles = Folder::files($filepathFolderTemp);
                    if (!empty($dirFiles)) {
                        foreach ($dirFiles as $fileS) {
                            $filePathImgS = $filepathFolderTemp . $fileS;
                            // Remove temp files if they are older than the max age
                            if (preg_match('/\\.tmp$/', $fileS) && (filemtime($filepathImgTemp) < time() - $maxFileAge)) {
                                @File::delete($filePathImgS);
                            }
                        }
                    }
                } else {
                    jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 100,
                        'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                        'details' => Text::_('COM_PHOCACART_ERROR_FOLDER_UPLOAD_NOT_EXISTS'))));
                }

                // Look for the content type header
                if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
                    $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

                if (isset($_SERVER["CONTENT_TYPE"]))
                    $contentType = $_SERVER["CONTENT_TYPE"];

                if (strpos($contentType, "multipart") !== false) {
                    if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {

                        // Open temp file
                        $out = $stream->open($filepathImgTemp, $chunk == 0 ? "wb" : "ab");
                        //$out = fopen($filepathImgTemp, $chunk == 0 ? "wb" : "ab");
                        if ($out) {
                            // Read binary input stream and append it to temp file
                            $in = fopen($file['tmp_name'], "rb");
                            if ($in) {
                                while ($buff = fread($in, 4096)) {
                                    $stream->write($buff);
                                    //fwrite($out, $buff);
                                }
                            } else {
                                jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 101,
                                    'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                                    'details' => Text::_('COM_PHOCACART_ERROR_OPEN_INPUT_STREAM'))));
                            }
                            $stream->close();
                            //fclose($out);
                            @File::delete($file['tmp_name']);
                        } else {
                            jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 102,
                                'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                                'details' => Text::_('COM_PHOCACART_ERROR_OPEN_OUTPUT_STREAM'))));
                        }
                    } else {
                        jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 103,
                            'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                            'details' => Text::_('COM_PHOCACART_ERROR_MOVE_UPLOADED_FILE'))));
                    }
                } else {
                    // Open temp file
                    $out = $stream->open($filepathImgTemp, $chunk == 0 ? "wb" : "ab");
                    //$out = JFile::read($filepathImg);
                    if ($out) {
                        // Read binary input stream and append it to temp file
                        $in = fopen("php://input", "rb");

                        if ($in) {
                            while ($buff = fread($in, 4096)) {
                                $stream->write($buff);
                            }
                        } else {
                            jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 101,
                                'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                                'details' => Text::_('COM_PHOCACART_ERROR_OPEN_INPUT_STREAM'))));
                        }
                        $stream->close();
                        //fclose($out);
                    } else {
                        jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 102,
                            'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                            'details' => Text::_('COM_PHOCACART_ERROR_OPEN_OUTPUT_STREAM'))));
                    }
                }


                // Rename the Temp File to Final File
                if ($lastChunk == $chunks) {

                    /*if(($imginfo = getimagesize($filepathImgTemp)) === FALSE) {
                        Folder::delete($filepathFolderTemp);
                        jexit(json_encode(array( 'jsonrpc' => '2.0', 'result' => 'error', 'code' => 110,
                        'message' => Text::_('COM_PHOCACART_ERROR').': ',
                        'details' => Text::_('COM_PHOCACART_WARNING_INVALIDIMG'))));
                    }*/

                    // Files should be overwritten
                    if ($overwriteExistingFiles == 1) {
                        File::delete($filepathImgFinal);
                    }

                    if (!File::move($filepathImgTemp, $filepathImgFinal)) {

                        Folder::delete($filepathFolderTemp);

                        jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 109,
                            'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                            'details' => Text::_('COM_PHOCACART_ERROR_UNABLE_TO_MOVE_FILE') . '<br />'
                                . Text::_('COM_PHOCACART_CHECK_PERMISSIONS_OWNERSHIP'))));
                    }


                    Folder::delete($filepathFolderTemp);
                }

                if ((int)$frontEnd > 0) {
                    return $file['name'];
                }

                jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'OK', 'code' => 200,
                    'message' => Text::_('COM_PHOCACART_SUCCESS') . ': ',
                    'details' => Text::_('COM_PHOCACART_FILES_UPLOADED'))));


            } else {
                // No Chunk Method


                $filepathImgFinal = Path::clean($path['orig_abs_ds'] . $folder . strtolower($file['name']));
                $filepathFolderFinal = Path::clean($path['orig_abs_ds'] . $folder);


                if (!PhocacartFileUpload::canUpload($file, $errUploadMsg, $manager, $frontEnd, $chunkMethod, 0)) {
                    jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 104,
                        'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                        'details' => Text::_($errUploadMsg))));
                }

                if (File::exists($filepathImgFinal) && $overwriteExistingFiles == 0) {
                    jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 108,
                        'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                        'details' => Text::_('COM_PHOCACART_FILE_ALREADY_EXISTS'))));
                }


                if (!File::upload($file['tmp_name'], $filepathImgFinal, false, true)) {
                    PhocacartLog::add(2, 'Error uploading file - JFile upload Ajax', 0, 'File: ' . $file['name'] . ', File Path: ' . $filepathImgFinal);
                    jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 109,
                        'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                        'details' => Text::_('COM_PHOCACART_ERROR_UNABLE_TO_UPLOAD_FILE') . '<br />'
                            . Text::_('COM_PHOCACART_CHECK_PERMISSIONS_OWNERSHIP'))));
                }

                if ((int)$frontEnd > 0) {
                    return $file['name'];
                }

                jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'OK', 'code' => 200,
                    'message' => Text::_('COM_PHOCACART_SUCCESS') . ': ',
                    'details' => Text::_('COM_PHOCACART_IMAGES_UPLOADED'))));


            }
        } else {
            // No isset $file['name']
            PhocacartLog::add(2, 'Error uploading file - Filename does not exist', 0, 'File: File does not exist - Multiple Upload ');

            jexit(json_encode(array('jsonrpc' => '2.0', 'result' => 'error', 'code' => 104,
                'message' => Text::_('COM_PHOCACART_ERROR') . ': ',
                'details' => Text::_('COM_PHOCACART_ERROR_UNABLE_TO_UPLOAD_FILE'))));
        }

    }


    public static function realSingleUpload($frontEnd = 0)
    {

        $app = Factory::getApplication();
        $paramsC = PhocacartUtils::getComponentParameters();
        //	$chunkMethod 	= $paramsC->get( 'multiple_upload_chunk', 0 );
        //	$uploadMethod 	= $paramsC->get( 'multiple_upload_method', 1 );

        $overwriteExistingFiles = $paramsC->get('overwrite_existing_files', 0);


        Session::checkToken('request') or jexit('ERROR: ' . Text::_('COM_PHOCACART_INVALID_TOKEN'));
        $app->allowCache(false);


        $file = Factory::getApplication()->input->files->get('Filedata', null, 'raw');
        //$file 			= J R equest::getVar( 'Filedata', '', 'files', 'array' );
        $folder = Factory::getApplication()->input->get('folder', '', '', 'path');
        $format = Factory::getApplication()->input->get('format', 'html', '', 'cmd');
        $return = Factory::getApplication()->input->get('return-url', null, 'post', 'base64');//includes field
        $viewBack = Factory::getApplication()->input->get('viewback', '', '', '');
        $manager = Factory::getApplication()->input->get('manager', 'file', '', 'string');
        $tab = Factory::getApplication()->input->get('tab', '', '', 'string');
        $field = Factory::getApplication()->input->get('field');
        $errUploadMsg = '';
        $folderUrl = $folder;
        $tabUrl = '';
        $component = Factory::getApplication()->input->get('option', '', '', 'string');

        $path = PhocacartPath::getPath($manager);// we use viewback to get right path


        // In case no return value will be sent (should not happen)
        if ($component != '' && $frontEnd == 0) {
            $componentUrl = 'index.php?option=' . $component;
        } else {
            $componentUrl = 'index.php';
        }
        if ($tab != '') {
            $tabUrl = '&tab=' . (string)$tab;
        }

        $ftp = ClientHelper::setCredentialsFromRequest('ftp');

        // Make the filename safe
        if (isset($file['name'])) {
            $file['name'] = File::makeSafe($file['name']);
        }


        if (isset($folder) && $folder != '') {
            $folder = $folder . '/';
        }


        // All HTTP header will be overwritten with js message
        if (isset($file['name'])) {
            $filepath = Path::clean($path['orig_abs_ds'] . $folder . strtolower($file['name']));

            if (!PhocacartFileUpload::canUpload($file, $errUploadMsg, $manager, $frontEnd)) {

                if ($errUploadMsg == 'COM_PHOCACART_WARNFILETOOLARGE') {
                    $errUploadMsg = Text::_($errUploadMsg) . ' (' . PhocacartFile::getFileSizeReadable($file['size']) . ')';
                } /* else if ($errUploadMsg == 'COM_PHOCACART_WARNING_FILE_TOOLARGE_RESOLUTION') {
					$imgSize		= phocacartImage::getImageSize($file['tmp_name']);
					$errUploadMsg 	= Text::_($errUploadMsg) . ' ('.(int)$imgSize[0].' x '.(int)$imgSize[1].' px)';
				} */ else {
                    $errUploadMsg = Text::_($errUploadMsg);
                }


                if ($return) {
                    $app->enqueueMessage($errUploadMsg, 'error');
                    $app->redirect(base64_decode($return) . '&manager=' . (string)$manager . '&folder=' . $folderUrl);
                    exit;
                } else {
                    $app->enqueueMessage($errUploadMsg, 'error');
                    $app->redirect($componentUrl);
                    exit;
                }
            }

            if (File::exists($filepath) && $overwriteExistingFiles == 0) {
                if ($return) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_FILE_ALREADY_EXISTS'), 'error');
                    $app->redirect(base64_decode($return) . '&manager=' . (string)$manager . '&folder=' . $folderUrl);
                    exit;
                } else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_FILE_ALREADY_EXISTS'), 'error');
                    $app->redirect($componentUrl);
                    exit;
                }
            }

            if (!File::upload($file['tmp_name'], $filepath, false, true)) {

                PhocacartLog::add(2, 'Error uploading file - JFile upload', 0, 'File: ' . $file['name'] . ', File Path: ' . $filepath);
                if ($return) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_UNABLE_TO_UPLOAD_FILE'), 'error');
                    $app->redirect(base64_decode($return) . '&manager=' . (string)$manager . '&folder=' . $folderUrl);
                    exit;
                } else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_UNABLE_TO_UPLOAD_FILE'), 'error');
                    $app->redirect($componentUrl);
                    exit;
                }
            } else {

                if ((int)$frontEnd > 0) {
                    return $file['name'];
                }


                if ($return) {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_FILE_UPLOADED'));
                    $app->redirect(base64_decode($return) . '&manager=' . (string)$manager . '&folder=' . $folderUrl);
                    exit;
                } else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_FILE_UPLOADED'));
                    $app->redirect($componentUrl);
                    exit;
                }
            }
        } else {
            PhocacartLog::add(2, 'Error uploading file - Filename does not exist', 0, 'File: File does not exist (Single Upload)');
            $msg = Text::_('COM_PHOCACART_ERROR_UNABLE_TO_UPLOAD_FILE');
            if ($return) {
                $app->enqueueMessage($msg);
                $app->redirect(base64_decode($return) . '&manager=' . (string)$manager . '&folder=' . $folderUrl);
                exit;
            } else {
                if ($viewBack != '') {
                    $group = PhocacartUtilsSettings::getManagerGroup($manager);
                    $link = 'index.php?option=com_phocacart&view=phocacartmanager&manager=' . (string)$manager
                        . str_replace('&amp;', '&', $group['c']) . '&' . $tabUrl . '&folder=' . $folder . '&field=' . $field;
                    $app->enqueueMessage($msg);
                    $app->redirect($link);
                } else {
                    $app->enqueueMessage($msg, 'error');
                    $app->redirect('index.php?option=com_phocacart');
                }

            }
        }

    }


    public static function canUpload($file, &$err, $manager = '', $frontEnd = 0, $chunkEnabled = 0, $realSize = 0)
    {


        $paramsC = PhocacartUtils::getComponentParameters();

        if ($frontEnd == 1) {
            $aft = $paramsC->get('allowed_file_types_upload_frontend', '{gif=image/gif}{jpeg=image/jpeg}{jpg=image/jpeg}{png=image/png}{webp=image/webp}');
            //$dft = $paramsC->get( 'disallowed_file_types_upload', '' );
            $allowedMimeType = PhocacartFile::getMimeTypeString($aft);
            //$disallowedMimeType = PhocacartFile::getMimeTypeString($dft);

            $ignoreUploadCh = 0;
            $ignoreUploadCheck = $paramsC->get('ignore_file_types_check', 0);
            if ($ignoreUploadCheck == 1 || $ignoreUploadCheck == 4) {
                $ignoreUploadCh = 1;
            }

        } else {

            $aft = $paramsC->get('allowed_file_types_upload_backend', '{gif=image/gif}{jpeg=image/jpeg}{jpg=image/jpeg}{png=image/png}{webp=image/webp}{tar=application/x-tar}{tgz=application/x-tar}{zip=application/x-zip}{rar=application/x-rar-compressed}{tar=application/tar}{tgz=application/tar}{zip=application/zip}{rar=application/rar-compressed}{pdf=application/pdf}{txt=text/plain}{xml=text/xml}{doc=application/msword}{xls=application/vnd.ms-excel}{ppt=application/powerpoint}{odt=application/vnd.oasis.opendocument.text}{ods=application/vnd.oasis.opendocument.spreadsheet}{odp=application/vnd.oasis.opendocument.presentation}{docx=application/vnd.openxmlformats-officedocument.wordprocessingml.document}{xlsx=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet}{pptx=application/vnd.openxmlformats-officedocument.presentationml.presentation}{mp3=audio/mpeg}{mp4=video/mp4}{epub=application/epub+zip}');
            //$dft = $paramsC->get( 'disallowed_file_types_download', '' );
            $allowedMimeType = PhocacartFile::getMimeTypeString($aft);
            //$disallowedMimeType = PhocacartFile::getMimeTypeString($dft);


            $ignoreUploadCh = 0;
            $ignoreUploadCheck = $paramsC->get('ignore_file_types_check', 0);
            if ($ignoreUploadCheck == 1 || $ignoreUploadCheck == 5) {
                $ignoreUploadCh = 1;
            }
        }


        $paramsL = array();
        $group = PhocacartUtilsSettings::getManagerGroup($manager);
        $paramsL['upload_extensions'] = $allowedMimeType['ext'];
        $paramsL['image_extensions'] = 'gif,jpg,png,jpeg,webp';
        $paramsL['upload_mime'] = $allowedMimeType['mime'];
        //$paramsL['upload_mime_illegal']	='application/x-shockwave-flash,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip,text/html';
        //$paramsL['upload_ext_illegal']	= $disallowedMimeType['ext'];


        // The file doesn't exist
        if (empty($file['name'])) {
            $err = 'COM_PHOCACART_WARNING_INPUT_FILE_UPLOAD';
            return false;
        }
        // Not safe file
        jimport('joomla.filesystem.file');
        if ($file['name'] !== File::makesafe($file['name'])) {
            $err = 'COM_PHOCACART_WARNFILENAME';
            return false;
        }


        $format = strtolower(File::getExt($file['name']));
        if ($ignoreUploadCh == 1) {

        } else {

            $allowable = explode(',', $paramsL['upload_extensions']);
            $allowableImage = explode(',', $paramsL['image_extensions']);
            /*$notAllowable 	= explode( ',', $paramsL['upload_ext_illegal']);
            if(in_array($format, $notAllowable)) {
                $err = 'COM_PHOCACART_WARNFILETYPE_DISALLOWED';
                return false;
            }*/


            //if (!in_array($format, $allowable)) {
            // Check file extensions able to upload
            if ($format == '' || $format == false || (!in_array($format, $allowable))) {
                $err = 'COM_PHOCACART_WARNFILETYPE_NOT_ALLOWED';
                return false;
            }

            // Some views can only upload images, so additional check to allowed file mime types
            if ($group['i'] == 1) {
                if (!in_array($format, $allowableImage)) {
                    $err = 'COM_PHOCACART_WARNFILETYPE_NOT_ALLOWED';
                    return false;
                }
            }
        }


        // Max size of image
        // If chunk method is used, we need to get computed size
        if ((int)$frontEnd > 0) {
            $maxSize = $paramsC->get('upload_maxsize_frontend', 3145728);
        } else {
            $maxSize = $paramsC->get('upload_maxsize', 3145728);
        }

        if ($chunkEnabled == 1) {
            if ((int)$maxSize > 0 && (int)$realSize > (int)$maxSize) {
                $err = 'COM_PHOCACART_WARNFILETOOLARGE';

                return false;
            }
        } else {
            if ((int)$maxSize > 0 && (int)$file['size'] > (int)$maxSize) {
                $err = 'COM_PHOCACART_WARNFILETOOLARGE';

                return false;
            }
        }


        // User (only in ucp) - Check the size of all files by users
        /*if ($frontEnd == 2) {
            $user 				= PhocacartUser::getUser();
            $maxUserUploadSize 	= (int)$paramsC->get( 'user_files_max_size', 20971520 );
            $maxUserUploadCount	= (int)$paramsC->get( 'user_files_max_count', 5 );
            $allFile	= PhocacartUser:: getUserFileInfo($file, $user->id);

            if ($chunkEnabled == 1) {
                $fileSize = $realSize;
            } else {
                $fileSize = $file['size'];
            }

            if ((int)$maxUserUploadSize > 0 && (int) $allFile['size'] > $maxUserUploadSize) {
                $err = Text::_('COM_PHOCACART_WARNUSERFILESTOOLARGE');
                return false;
            }

            if ((int) $allFile['count'] > $maxUserUploadCount) {
                $err = Text::_('COM_PHOCACART_WARNUSERFILESTOOMUCH');
                return false;
            }
        }*/


        // Image check
        $imginfo = null;
        $images = explode(',', $paramsL['image_extensions']);

        if (in_array($format, $images)) { // if its an image run it through getimagesize

            if ($group['i'] == 1) {
                if ($chunkEnabled != 1) {
                    if (($imginfo = getimagesize($file['tmp_name'])) === FALSE) {
                        $err = 'COM_PHOCACART_WARNINVALIDIMG';

                        if (isset($imginfo[0]) && $imginfo[0] != '') {
                            $err = $imginfo[0];
                        }
                        return false;
                    }
                }
            }
        } else if (!in_array($format, $images)) { // if its not an image...and we're not ignoring it
            $allowed_mime = explode(',', $paramsL['upload_mime']);
            //$illegal_mime = explode(',', $paramsL['upload_mime_illegal']);
            if (function_exists('finfo_open')) {// We have fileinfo
                $finfo = finfo_open(FILEINFO_MIME);
                $type = finfo_file($finfo, $file['tmp_name'], FILEINFO_MIME_TYPE );

                if (strlen($type) && !in_array($type, $allowed_mime) /* && in_array($type, $illegal_mime)*/) {
                    $err = 'COM_PHOCACART_WARNINVALIDMIME';
                    return false;
                }

                finfo_close($finfo);
            } else if (function_exists('mime_content_type')) { // we have mime magic
                $type = mime_content_type($file['tmp_name']);
                if (strlen($type) && !in_array($type, $allowed_mime) /*&& in_array($type, $illegal_mime)*/) {
                    $err = 'COM_PHOCACART_WARNINVALIDMIME';
                    return false;
                }
            }
        }

        // XSS Check
        $xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);
        $html_tags = PhocacartUtilsSettings::getHTMLTagsUpload();
        foreach ($html_tags as $tag) { // A tag is '<tagname ', so we need to add < and a space or '<tagname>'
            if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag . '>')) {
                $err = 'COM_PHOCACART_WARNIEXSS';
                return false;
            }
        }

        return true;
    }


    public static function renderFTPaccess()
    {

        $ftpOutput = '<fieldset title="' . Text::_('COM_PHOCACART_FTP_LOGIN_LABEL') . '">'
            . '<legend>' . Text::_('COM_PHOCACART_FTP_LOGIN_LABEL') . '</legend>'
            . Text::_('COM_PHOCACART_FTP_LOGIN_DESC')
            . '<table class="adminform nospace">'
            . '<tr>'
            . '<td width="120"><label for="username">' . Text::_('JGLOBAL_USERNAME') . ':</label></td>'
            . '<td><input type="text" id="username" name="username" class="input_box" size="70" value="" /></td>'
            . '</tr>'
            . '<tr>'
            . '<td width="120"><label for="password">' . Text::_('JGLOBAL_PASSWORD') . ':</label></td>'
            . '<td><input type="password" id="password" name="password" class="input_box" size="70" value="" /></td>'
            . '</tr></table></fieldset>';
        return $ftpOutput;
    }

    public static function renderCreateFolder($sessName, $sessId, $currentFolder, $viewBack, $attribs = '')
    {

        if ($attribs != '') {
            $attribs = '&amp;' . $attribs;
        }

        $folderOutput = '<form action="' . Uri::base()
            . 'index.php?option=com_phocacart&task=phocacartupload.createfolder&amp;' . $sessName . '=' . $sessId . '&amp;'
            . Session::getFormToken() . '=1&amp;viewback=' . $viewBack . '&amp;'
            . 'folder=' . PhocacartText::filterValue($currentFolder, 'folderpath') . $attribs . '" name="folderForm" id="folderForm" method="post" class="form-inline" >' . "\n"

            . '<h4>' . Text::_('COM_PHOCACART_FOLDER') . '</h4>' . "\n"
            . '<div class="path">'
            . '<input class="form-control" type="text" id="foldername" name="foldername"  />'
            . '<input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="' . PhocacartText::filterValue($currentFolder, 'folderpath') . '" />'
            . ' <button type="submit" class="btn btn-success">' . Text::_('COM_PHOCACART_CREATE_FOLDER') . '</button>'
            . '</div>' . "\n"
            . HTMLHelper::_('form.token')
            . '</form>';
        return $folderOutput;
    }


    public static function submitItemUpload($files, $data, &$fileData, $type = 'image')
    {


		$pC                                 = PhocacartUtils::getComponentParameters();
		$submit_item_upload_image_maxsize = $pC->get('submit_item_upload_image_maxsize', 512000);
		$submit_item_upload_image_count = $pC->get('submit_item_upload_image_count', 1);


		$app        = Factory::getApplication();
        $path       = PhocacartPath::getPath('submititem');
        $folder     = $data['upload_folder'];




        if (!empty($files)) {
            $i = 1;
            foreach ($files as $k => $v) {

                if (isset($v['name'])) {



                    $errUploadMsg = '';
                    if (!PhocacartFileUpload::canUpload( $v, $errUploadMsg, 'submitimage', 1 )) {

                        if ($errUploadMsg == 'COM_PHOCACART_WARNFILETOOLARGE') {
                            $errUploadMsg 	= Text::_($errUploadMsg) . ' ('.PhocacartFile::getFileSizeReadable($v['size']).')';
                        } /* else if ($errUploadMsg == 'COM_PHOCACART_WARNING_FILE_TOOLARGE_RESOLUTION') {
                            $imgSize		= phocacartImage::getImageSize($file['tmp_name']);
                            $errUploadMsg 	= Text::_($errUploadMsg) . ' ('.(int)$imgSize[0].' x '.(int)$imgSize[1].' px)';
                        } */ else {
							$errUploadMsg 	= Text::_($errUploadMsg);

							if ($errUploadMsg != '') {
								if (isset($v['name']) && $v['name'] != '') {
									$errUploadMsg = $errUploadMsg . ' ('.$v['name'].')';
								}
							}
						}


						$app->enqueueMessage($errUploadMsg, 'error');
						return false;

					}

                    // Specific check for specific form field types
                    if ((int)$submit_item_upload_image_maxsize > 0 && (int)$v['size'] > (int)$submit_item_upload_image_maxsize ) {
                        $app->enqueueMessage(Text::_('COM_PHOCACART_WARNFILETOOLARGE'), 'error');
                        return false;
                    }


                    if ($i > (int)$submit_item_upload_image_count ) {
                        // Don't allow to upload any other file and return the current data
                        return true;
                    }
                    $i++;

                    $ext                = PhocacartFile::getExtension($v['name']);
                    $fileNameToken	    = PhocacartUtils::getToken('folder');
                    $fileNameTokenExt	= $fileNameToken . '.'.$ext;
                    $folderPath         = Path::clean($path['orig_abs_ds'] . $folder);
                    $filePath           = Path::clean($path['orig_abs_ds'] . $folder . '/'. $fileNameTokenExt);

                    PhocacartFile::createUploadFolder('');// Create Main Upload Folder if not exists
                    PhocacartFile::createUploadFolder($folder);


					if (File::exists($filePath)) {
						$app->enqueueMessage( Text::_('COM_PHOCACART_FILE_ALREADY_EXISTS'), 'error');
						return false;
					}

					if (!File::upload($v['tmp_name'], $filePath, false, true)) {

						PhocacartLog::add(2, 'Error uploading file - JFile upload (Submit Item)', 0, 'File: '.strip_tags(htmlspecialchars($v['name'])).', File Path: '.$filePath );
						$app->enqueueMessage( Text::_('COM_PHOCACART_ERROR_UNABLE_TO_UPLOAD_FILE'), 'error');
						return false;
					} else {

						//$app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_FILE_UPLOADED'));
                        $fileData[$fileNameToken]                 = array();
                        $fileData[$fileNameToken]['id']           = $fileNameToken;
                        $fileData[$fileNameToken]['name']         = $v['name'];
                        $fileData[$fileNameToken]['size']         = $v['size'];
                        $fileData[$fileNameToken]['nametoken']    = $fileNameTokenExt;
                        $fileData[$fileNameToken]['fullpath']     = $filePath;
                        // Success File added go further with foreach and next files

					}
                } else {
					PhocacartLog::add(2, 'Error uploading file - Filename does not exist (Submit Item)', 0, 'File: File does not exist (Submit Item)' );
					$msg = Text::_('COM_PHOCACART_ERROR_UNABLE_TO_UPLOAD_FILE');
					$app->enqueueMessage($msg);
					return false;
				}
            }
        }
        return true;
    }
}
?>
