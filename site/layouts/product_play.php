<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Filesystem\File;
$d = $displayData;

if (isset($d['publicplayfile']) && $d['publicplayfile'] != '') {

    $ext = File::getExt($d['publicplayfile']);

    echo '<div class="ph-item-play-file">';
    if (isset($d['title']) && $d['title'] != '') {
        echo '<div class="ph-item-play-title">'.$d['title'].'</div>';
    }
    switch($ext) {

        case 'mp3':
        case 'm4a':
            echo '<audio controls><source src="'.$d['pathpublicfile']['orig_rel_path_ds'].$d['publicplayfile'].'" type="audio/mpeg"></audio>';
        break;

        case 'ogg':
        case 'oga':
            echo '<audio controls><source src="'.$d['pathpublicfile']['orig_rel_path_ds'].$d['publicplayfile'].'" type="audio/ogg"></audio>';
        break;

        case 'mp4':
        case 'm4v':
            echo '<video controls><source src="'.$d['pathpublicfile']['orig_rel_path_ds'].$d['publicplayfile'].'" type="video/mp4"></video>';
        break;

        case 'ogv':
            echo '<video controls><source src="'.$d['pathpublicfile']['orig_rel_path_ds'].$d['publicplayfile'].'" type="video/ogg"></video>';
        break;

        default:


        break;

    }
    echo '</div>';
}
