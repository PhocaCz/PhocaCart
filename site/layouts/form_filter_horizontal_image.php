<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Uri\Uri;

$d 				= $displayData;
$dParamAttr		= str_replace(array('[',']'), '', $d['param']);
$iconType		= $d['s']['i']['icon-type'];

// This output is outside html to get the useful information from foreach about if the panel is active (this is needed to open or clode the panel)
$output = '';
foreach ($d['items'] as $k => $v) {

    $checked 	= '';
    $checkedInt = 0;
    $value		= htmlspecialchars($v->alias);
    if (isset($d['nrinalias']) && $d['nrinalias'] == 1) {
        $value 		= (int)$v->id .'-'. htmlspecialchars($v->alias);
    }

    if (in_array($value, $d['getparams'])) {
        $checked 	= 'checked';
        $checkedInt	= 0;
    } else {
        $checkedInt	= 1;
    }

    $class = $iconType . ' ';
    if ($checked) {
        $class .= 'on';
        $d['collapse_class'] = $d['s']['c']['panel-collapse.collapse.in'];
    }

    if (isset($v->image_small) && $v->image_small != '') {

        $linkI 		= Uri::base(true).'/'.$d['pathitem']['orig_rel'].'/'.$v->image_small;

        $jsSet = '';
        if (isset($d['forcecategory']['idalias']) && $d['forcecategory']['idalias']  != '') {
            // Category View - force the category parameter if set in parameters
            $jsSet .= 'phChangeFilter(\'c\', \''.$d['forcecategory']['idalias'].'\', 1, \'text\', 0, 1, 1);';
        }
        $jsSet .= 'phChangeFilter(\''.$d['param'].'\', \''. $value.'\', this, \''.$d['formtype'].'\', \''.$d['uniquevalue'].'\', 0, 1);return false;';

        $output .= '<a href="#" class="phSelectBoxImage '.$class.'" onclick="'.$jsSet.'" title="'.htmlspecialchars($v->title).'">'
        .'<img style="'.$d['style'].'" src="'.$linkI.'" alt="'.$v->title.'" />'
        .'</a>';
    }
}

$title = isset($d['titleheader']) && $d['titleheader'] != '' ? $d['titleheader'] : $d['title'];
?><span class="<?php echo $d['s']['c']['dropdown'] ?> filter-<?php echo $dParamAttr; ?>">
  <button class="<?php echo $d['s']['c']['btn.btn-outline-secondary'] ?> <?php echo $d['s']['c']['dropdown-toggle'] ?>" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><?php echo $title ?></button>
  <div <?php echo $d['s']['a']['dropdown'] ?>  class="<?php echo $d['s']['c']['dropdown-menu'] ?> <?php echo $d['s']['c']['px-3'] ?> <?php echo $d['s']['c']['py-3'] ?> ph-mod-color-box">
		<?php echo $output ?>
	</div>
</span>
