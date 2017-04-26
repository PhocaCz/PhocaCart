<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div id="'.$this->t['c'].'-multipleupload" class="ph-in">';
echo $this->t['mu_response_msg'] ;
echo '<form action="'. JURI::base().'index.php?option='.$this->t['o'].'" >';
if ($this->t['ftp']) {echo PhocacartFileUpload::renderFTPaccess();}
echo '<div class="ph-head-form-small">' . JText::_( $this->t['l'].'_UPLOAD_FILE' ).' [ '. JText::_( $this->t['l'].'_MAX_SIZE' ).':&nbsp;'.$this->t['uploadmaxsizeread'].''
	.']</div>';
echo '<small>'.JText::_($this->t['l'].'_SELECT_FILES').'. '.JText::_($this->t['l'].'_ADD_FILES_TO_UPLOAD_QUEUE_AND_CLICK_START_BUTTON').'</small>';
echo $this->t['mu_output'];
echo '</form>';
echo '</div>';
?>