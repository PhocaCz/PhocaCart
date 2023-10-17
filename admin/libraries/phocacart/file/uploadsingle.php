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
use Joomla\CMS\Language\Text;

class PhocacartFileUploadsingle
{
	public $tab			= '';
	public $returnUrl	= '';

	public function __construct() {}
	
	public function getSingleUploadHTML( $frontEnd = 0) {
		
		if ($frontEnd == 1) {
			
			$html = '<input type="hidden" name="controller" value="category" />'
			.'<input type="hidden" name="tab" value="'.$this->tab.'" />';
		
		} else {
			$html = '<input type="file" id="sfile-upload" name="Filedata" />'
			//.'<input type="submit" id="sfile-upload-submit" value="'.Text::_('COM_PHOCADOWNLOAD_START_UPLOAD').'"/>'
			.'<button class="btn btn-primary" id="upload-submit"><i class="icon-upload icon-white"></i> '.Text::_('COM_PHOCACART_START_UPLOAD').'</button>'
			.'<input type="hidden" name="return-url" value="'. base64_encode($this->returnUrl).'" />'
			.'<input type="hidden" name="tab" value="'.$this->tab.'" />';
		}
		
		return $html;
		
	}
}
?>
