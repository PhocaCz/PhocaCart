<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


echo '<div id="ph-pc-download-box" class="pc-download-view'.$this->p->get( 'pageclass_sfx' ).'">';


echo PhocacartRenderFront::renderHeader(array(JText::_('COM_PHOCACART_DOWNLOAD')));

if ($this->u->id > 0 || ($this->t['token_download'] != '' && $this->t['token_order'] != '')) {
	if (!empty($this->t['files'])) {

		//echo '<div class="ph-download-files">';

		echo '<div class="'.$this->s['c']['row'].' ph-download-header-box-row ph-vertical-align">';
		echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].'">'.JText::_('COM_PHOCACART_TITLE').'</div>';
		echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].'">'.JText::_('COM_PHOCACART_FILENAME').'</div>';
		echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].'">'.JText::_('COM_PHOCACART_STATUS').'</div>';
		echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].' ph-center">'.JText::_('COM_PHOCACART_DOWNLOAD').'</div>';
		echo '<div class="ph-cb"></div>';
		echo '</div>';


		foreach ($this->t['files'] as $k => $v) {


			echo '<div class="'.$this->s['c']['row'].' ph-download-item-box-row ph-vertical-align">';

			echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].'">'.$v->title;

			echo '<br /><span class="'.$this->s['c']['label.label-info'].'">'.PhocacartOrder::getOrderNumber($v->order_id, $v->order_date, $v->order_number).'</span>';
			echo '</div>';

			$fileA = explode('/', $v->download_file);
			$fileACount = count($fileA);
			$fileACount--;
			$file = $fileA[$fileACount];
			echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].'">'.$file.'</div>';


			$downloadPossible = 0;
			$status = '';
			if((int)$this->t['download_count'] > 0 && ((int)$this->t['download_count'] == (int)$v->download_hits || (int)$this->t['download_count'] < (int)$v->download_hits)) {
				$status .= '<span class="'.$this->s['c']['label.label-danger'].'">'.JText::_('COM_PHOCACART_MAXIMUM_DOWNLOADS_REACHED'). '</span><br />';

			}

			$downloadDays = (int)$this->t['download_days'];
			if (isset($v->download_days) && (int)$v->download_days > -1) {
				$downloadDays = (int)$v->download_days;
			}

			if($downloadDays != 0 && !PhocacartDownload::isActive($v->date, $downloadDays)) {
				$status .= '<span class="'.$this->s['c']['label.label-danger'].'">'.JText::_('COM_PHOCACART_DOWNLOAD_DATE_EXPIRED'). '</span><br />';

			}

			if ($status == '') {
				$status = '<span class="'.$this->s['c']['label.label-success'].'">'.JText::_('COM_PHOCACART_ACTIVE'). '</span><br />';

				$rem	= (int)$this->t['download_count'] - (int)$v->download_hits;
				if ((int)$rem > 0) {
					$status .= ' <span class="ph-small"><b>'.$rem. '</b> ';
					if ($rem == 1) {
						$status .= JText::_('COM_PHOCACART_DOWNLOAD'). ' ';
					} else {
						$status .= JText::_('COM_PHOCACART_DOWNLOADS'). ' ';
					}
					$status .= JText::_('COM_PHOCACART_REMAINING'). '</span>';
				}

				$dateValid = PhocacartDownload::validUntil($v->date, $downloadDays);
				if ($dateValid) {
					$status .= '<br /> <span class="ph-small">'.JText::_('COM_PHOCACART_DOWNLOAD_VALID_UNTIL'). ': ';
					$status .= ' '.$dateValid.'<span>';
				}

				$downloadPossible = 1;
			}

			echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].'">'.$status.'</div>';

			if($downloadPossible ==1) {
				echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].' ph-center">';

				echo '<form action="'.$this->t['linkdownload'].'" method="post">';
				echo '<input type="hidden" name="id" value="'.(int)$v->id.'">';
				echo '<input type="hidden" name="task" value="download.download">';
				echo '<input type="hidden" name="tmpl" value="component" />';
				echo '<input type="hidden" name="option" value="com_phocacart" />';
				echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />';
				echo '<input type="hidden" name="d" value="'.$this->t['token_download'].'" />';
				echo '<input type="hidden" name="o" value="'.$this->t['token_order'].'" />';
				echo '<button type="submit" class="btn btn-primary ph-btn"><span class="'.$this->s['i']['download'].'"></span> '.JText::_('COM_PHOCACART_DOWNLOAD').'</button>';
				echo Joomla\CMS\HTML\HTMLHelper::_('form.token');
				echo '</form>';

				echo '</div>';

			} else {
				echo '<div class="'.$this->s['c']['col.xs12.sm3.md3'].' ph-center"><span class="'.$this->s['i']['ban'].' ph-red"></span></div>';
			}

			echo '</div>';// end row
		}
		echo '<div class="ph-cb ph-download-item-box-row-line"></div>';

		//echo '</div>';// end download files
	} else {
		echo '<div class="alert alert-error alert-danger">'.JText::_('COM_PHOCACART_THERE_ARE_NO_FILES_TO_DOWNLOAD').'</div>';
	}

} else {
	echo '<div class="alert alert-error alert-danger">'.JText::_('COM_PHOCACART_NOT_LOGGED_IN_PLEASE_LOGIN').'</div>';
}



echo '</div>';// end comparison box
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>
