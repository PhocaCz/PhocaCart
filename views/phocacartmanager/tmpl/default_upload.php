<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
echo '<div id="'.$this->t['c'].'-upload" class="ph-in">';
echo '<div id="upload-noflash" class="actions">';
echo '<form action="'. $this->t['su_url'] .'" id="uploadFormU" method="post" enctype="multipart/form-data">';
if ($this->t['ftp']) { echo PhocaCartFileUpload::renderFTPaccess();}  
echo '<div class="ph-head-form">'. JText::_( $this->t['l'].'_UPLOAD_FILE' ).' [ '. JText::_( $this->t['l'].'_MAX_SIZE' ).':&nbsp;'.$this->t['uploadmaxsizeread'].'] </div>';
echo $this->t['su_output'];
echo '</form>';
echo '</div>';
echo '</div>';
?>