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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class PhocacartFileUploadmultiple
{
	public $method 		= 1;
	public $url			= '';
	public $reload		= '';
	public $maxFileSize	= '';
	public $chunkSize	= '';
	public $imageHeight	= '';
	public $imageWidth	= '';
	public $imageQuality= '';
	public $frontEnd	= 0;

	public function __construct() {}

	static public function renderMultipleUploadLibraries() {


		$app			= Factory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$chunkMethod 	= $paramsC->get( 'multiple_upload_chunk', 0 );
		$uploadMethod 	= $paramsC->get( 'multiple_upload_method', 4 );
		$wa  			= $app->getDocument()->getWebAssetManager();

		//JHtml::_('behavior.framework', true);// Load it here to be sure, it is loaded before jquery
		HTMLHelper::_('jquery.framework', false);// Load it here because of own nonConflict method (nonconflict is set below)
		$document			= Factory::getDocument();


		/*if ($uploadMethod == 2) {
			//$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/gears_init.js');
		}
		if ($uploadMethod == 5) {
			//$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/plupload.browserplus.js');
		}*/

		//$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/plupload.js');
		$wa->registerAndUseScript('com_phocacart.plupload.js', 'media/com_phocacart/js/plupload/plupload.js', ['version' => 'auto']);
		/*if ($uploadMethod == 2) {
			$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/plupload.gears.js');
		}
		if ($uploadMethod == 3) {
			$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/plupload.silverlight.js');
		}
		if ($uploadMethod == 1) {
			$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/plupload.flash.js');
		}
		if ($uploadMethod == 5) {
			$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/plupload.browserplus.js');
		}
		if ($uploadMethod == 6) {
			$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/plupload.html4.js');
		}*/
		if ($uploadMethod == 4) {
			//$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/plupload.html5.js');
			$wa->registerAndUseScript('com_phocacart.plupload.html5.js', 'media/com_phocacart/js/plupload/plupload.html5.js', ['version' => 'auto']);
		}
		$wa->registerAndUseStyle('com_phocacart.jquery.plupload.queue', 'media/com_phocacart/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css', array('version' => 'auto'));
		$wa->registerAndUseScript('com_phocacart.jquery.plupload.queue.js', 'media/com_phocacart/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js', ['version' => 'auto']);
		//$document->addScript(Uri::root(true).'/media/com_phocacart/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js');
		//HTMLHelper::stylesheet( 'media/com_phocacart/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css' );
	}

	static public function getMultipleUploadSizeFormat($size) {
		$readableSize = PhocacartFile::getFileSizeReadable($size, '%01.0f %s', 1);
		$readableSize 	= str_replace(' ', '', $readableSize);
		$readableSize 	= strtolower($readableSize);
		return $readableSize;
	}

	public function renderMultipleUploadJS($frontEnd = 0, $chunkMethod = 0) {

		$document			= Factory::getDocument();

		switch ($this->method) {
			/* case 2:
				$name		= 'gears_uploader';
				$runtime	= 'gears';
			break;
			case 3:
				$name		= 'silverlight_uploader';
				$runtime	= 'silverlight';
			break;*/
			case 4:
			default:
				$name		= 'html5_uploader';
				$runtime	= 'html5';
			break;

			/* case 5:
				$name		= 'browserplus_uploader';
				$runtime	= 'browserplus';
			break;

			case 6:
				$name		= 'html4_uploader';
				$runtime	= 'html4';
			break;

			case 1:
			default:
				$name		= 'flash_uploader';
				$runtime	= 'flash';
			break;*/
		}

		$chunkEnabled = 0;
		// Chunk only if is enabled and only if flash is enabled
		if (($chunkMethod == 1 && $this->method == 1) || ($this->frontEnd == 0 && $chunkMethod == 0 && $this->method == 1)) {
			$chunkEnabled = 1;
		}

		$this->url      = PhocacartText::filterValue($this->url, 'text');
		$this->reload 	= PhocacartText::filterValue($this->reload, 'text');
		$this->url 		= str_replace('&amp;', '&', $this->url);
		$this->reload 	= str_replace('&amp;', '&', $this->reload);


		$js = 'var pgJQ = jQuery.noConflict();';

		$js .='pgJQ(function() {'."\n";

		$js.=''."\n";
		$js.='   plupload.addI18n({'."\n";
		$js.='	   \'Select files\' : \''.addslashes(Text::_('COM_PHOCACART_SELECT_FILES')).'\','."\n";
		$js.='	   \'Add files to the upload queue and click the start button.\' : \''.addslashes(Text::_('COM_PHOCACART_ADD_FILES_TO_UPLOAD_QUEUE_AND_CLICK_START_BUTTON')).'\','."\n";
		$js.='	   \'Filename\' : \''.addslashes(Text::_('COM_PHOCACART_FILENAME')).'\','."\n";
		$js.='	   \'Status\' : \''.addslashes(Text::_('COM_PHOCACART_STATUS')).'\','."\n";
		$js.='	   \'Size\' : \''.addslashes(Text::_('COM_PHOCACART_SIZE')).'\','."\n";
		$js.='	   \'Add files\' : \''.addslashes(Text::_('COM_PHOCACART_ADD_FILES')).'\','."\n";
		$js.='	   \'Add Files\' : \''.addslashes(Text::_('COM_PHOCACART_ADD_FILES')).'\','."\n";
		$js.='	   \'Start upload\':\''.addslashes(Text::_('COM_PHOCACART_START_UPLOAD')).'\','."\n";
		$js.='	   \'Start Upload\':\''.addslashes(Text::_('COM_PHOCACART_START_UPLOAD')).'\','."\n";
		$js.='	   \'Stop upload\' : \''.addslashes(Text::_('COM_PHOCACART_STOP_CURRENT_UPLOAD')).'\','."\n";
		$js.='	   \'Stop Upload\' : \''.addslashes(Text::_('COM_PHOCACART_STOP_CURRENT_UPLOAD')).'\','."\n";
		$js.='	   \'Stop current upload\' : \''.addslashes(Text::_('COM_PHOCACART_STOP_CURRENT_UPLOAD')).'\','."\n";
		$js.='	   \'Start uploading queue\' : \''.addslashes(Text::_('COM_PHOCACART_START_UPLOADING_QUEUE')).'\','."\n";
		$js.='	   \'Drag files here.\' : \''.addslashes(Text::_('COM_PHOCACART_DRAG_FILES_HERE')).'\''."\n";
		$js.='   });';
		$js.=''."\n";


		$js.='	pgJQ("#'.$name.'").pluploadQueue({'."\n";
		$js.='		runtimes : \''.$runtime.'\','."\n";
		$js.='		url : \''.$this->url.'\','."\n";

		if ($this->maxFileSize != '0b') {
			$js.='		max_file_size : \''.PhocacartText::filterValue($this->maxFileSize, 'alphanumeric3').'\','."\n";
		}


		if ($chunkEnabled == 1) {
			$js.='		chunk_size : \'1mb\','."\n";
		}
		$js.='      preinit: attachCallbacks,'."\n";
		$js.='		unique_names : false,'."\n";
		$js.='		multipart: true,'."\n";
		$js.='		filters : ['."\n";
		//$js.='			{title : "'.Text::_('com_phocacart_IMAGE_FILES').'", extensions : "jpg,gif,png,webp,avif"}'."\n";
		//$js.='			{title : "Zip files", extensions : "zip"}'."\n";
		$js.='		],'."\n";
		$js.=''."\n";
		/*if ($this->method != 6) {
			if ((int)$this->imageWidth > 0 || (int)$this->imageWidth > 0) {
				$js.='		resize : {width : '.$this->imageWidth.', height : '.$this->imageHeight.', quality : '.$this->imageQuality.'},'."\n";
				$js.=''."\n";
			}
		}*/
		/* if ($this->method == 1) {
			$js.='		flash_swf_url : \''.Uri::root(true).'/media/com_phocacart/js/plupload/plupload.flash.swf\''."\n";
		} else if ($this->method == 3) {
			$js.='		silverlight_xap_url : \''.Uri::root(true).'/media/com_phocacart/js/plupload/plupload.silverlight.xap\''."\n";
		}*/
		$js.='	});'."\n";

		$js.=''."\n";

		$js.='function attachCallbacks(Uploader) {'."\n";
		$js.='	Uploader.bind(\'FileUploaded\', function(Up, File, Response) {'."\n";
		$js.='		var obj = eval(\'(\' + Response.response + \')\');'."\n";
		//if ($this->method == 4 || $this->method == 6) {
		//if ($this->method == 6) {
		//	$js.='		var queueFiles = Uploader.total.failed + Uploader.total.uploaded;'."\n";
		//	$js.='		var uploaded0 = Uploader.total.uploaded;'."\n";
		//} else {
			$js.='		var queueFiles = Uploader.total.failed + Uploader.total.uploaded + 1;'."\n";
			$js.='		var uploaded0 = Uploader.total.uploaded + 1;'."\n";
		//}
		$js.=''."\n";
		$js.='		if ((typeof(obj.result) != \'undefined\') && obj.result == \'error\') {'."\n";
		$js.='			'."\n";
		//if ($this->method == 6) {
			//$js.='		var uploaded0 = Uploader.total.uploaded;'."\n";
		//} else {
			//$js.='		var uploaded0 = Uploader.total.uploaded + 1;'."\n";
		//}
		$js.='			Up.trigger("Error", {message : obj.message, code : obj.code, details : obj.details, file: File});'."\n";
		$js.='				if( queueFiles == Uploader.files.length) {'."\n";
		//if ($this->method == 6) {
		//	$js.='		var uploaded0 = Uploader.total.uploaded;'."\n";
		//} else {
			$js.='		var uploaded0 = Uploader.total.uploaded;'."\n";
		//}
		$js.='					window.location = \''.$this->reload.'\' + \'&muuploaded=\' + uploaded0 + \'&mufailed=\' + Uploader.total.failed;'."\n";
		//$js.='					ale rt(\'Error\' + obj.message)'."\n";
		$js.='				}'."\n";
		$js.='				return false; '."\n";
		$js.=''."\n";
		$js.='		} else {'."\n";
		$js.='			if( queueFiles == Uploader.files.length) {'."\n";
		//$js.='				var uploaded = Uploader.total.uploaded + 1;'."\n";
		//if ($this->method == 6) {
		//	$js.='		var uploaded = Uploader.total.uploaded;'."\n";
		//} else {
			$js.='		var uploaded = Uploader.total.uploaded + 1;'."\n";
		//}
		$js.='				window.location = \''.$this->reload.'\' + \'&muuploaded=\' + uploaded + \'&mufailed=\' + Uploader.total.failed;'."\n";
		//$js.='					ale rt(\'OK\' + obj.message)'."\n";
		$js.='			}'."\n";
		$js.='		}'."\n";
		$js.='	});'."\n";
		$js.='	'."\n";
		$js.='    Uploader.bind(\'Error\', function(Up, ErrorObj) {'."\n";
		$js.=''."\n";
	//	$js.='         if (ErrorObj.code == 100) { '."\n";
	//	$js.='        if (typeof ErrorObj.file.id !== \'undefined\') {';
		$js.='			pgJQ(\'#\' + ErrorObj.file.id).append(\'<div class="alert alert-error">\'+ ErrorObj.message + ErrorObj.details +\'</div>\');'."\n";
	//	$js.='         }'."\n";
		$js.='    });	'."\n";
		$js.='}';

		$js.='});'."\n";// End $(function()

		//$document->addScriptDeclaration($js);
		$app = Factory::getApplication();
		$wa  = $app->getDocument()->getWebAssetManager();
		$wa->addInlineScript($js);
	}

	public function getMultipleUploadHTML($width = '', $height = '330', $mootools = 1) {


		switch ($this->method) {
			/*case 2:
				$name		= 'gears_uploader';
				$msg		= Text::_('COM_PHOCACART_NOT_INSTALLED_GEARS');
			break;
			case 3:
				$name		= 'silverlight_uploader';
				$msg		= Text::_('COM_PHOCACART_NOT_INSTALLED_SILVERLIGHT');
			break;*/
			case 4:
			default:
				$name		= 'html5_uploader';
				$msg		= Text::_('COM_PHOCACART_NOT_SUPPORTED_HTML5');
			break;

			/* case 5:
				$name		= 'browserplus_uploader';
				$msg		= Text::_('COM_PHOCACART_NOT_INSTALLED_BROWSERPLUS');
			break;

			case 6:
				$name		= 'html4_uploader';
				$msg		= Text::_('COM_PHOCACART_NOT_SUPPORTED_HTML4');
			break;

			case 1:
			default:
				$name		= 'flash_uploader';
				$msg		= Text::_('COM_PHOCACART_NOT_INSTALLED_FLASH');
			break;*/
		}

		$style				= '';
		if ($width != '') {
			$style	.= 'width: '.(int)$width.'px;';
		}
		if ($height != '') {
			$style	.= 'height: '.(int)$height.'px;';
		}

		return '<div id="'.$name.'" style="'.$style.'">'.$msg.'</div>';

	}
}
?>
