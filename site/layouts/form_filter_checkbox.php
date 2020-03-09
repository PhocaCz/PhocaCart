<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d 				= $displayData;
$displayData 	= null;
$dParamAttr		= str_replace(array('[',']'), '', $d['param']);

if ($d['params']['open_filter_panel'] == 0) {
    $d['collapse_class'] = $d['s']['c']['panel-collapse.collapse'];
    $d['triangle_class'] = $d['s']['i']['triangle-right'];
} else if ($d['params']['open_filter_panel'] == 2) {
    $d['collapse_class'] = $d['s']['c']['panel-collapse.collapse'];// closed as default and wait if there is some active item to open it
    $d['triangle_class'] = $d['s']['i']['triangle-right'];
} else {
    $d['collapse_class'] = $d['s']['c']['panel-collapse.collapse.in'];
    $d['triangle_class'] = $d['s']['i']['triangle-bottom'];
}

$output = '';
foreach ($d['items'] as $k => $v) {

    $checked 	= '';
    $value		= htmlspecialchars($v->alias);
    if (isset($d['nrinalias']) && $d['nrinalias'] == 1) {
        $value 		= (int)$v->id .'-'. htmlspecialchars($v->alias);
    }

    if (in_array($value, $d['getparams'])) {
        $checked 	= 'checked';
        $d['collapse_class'] = $d['s']['c']['panel-collapse.collapse.in'];
    }


    $jsSet = '';
    if (isset($d['forcecategory']['idalias']) && $d['forcecategory']['idalias']  != '') {
        // Category View - force the category parameter if set in parameters
        $jsSet .= 'phChangeFilter(\'c\', \''.$d['forcecategory']['idalias'].'\', 1, \'text\', 0, 1, 1);';
    }
    $jsSet .= 'phChangeFilter(\''.$d['param'].'\', \''. $value.'\', this, \''.$d['formtype'].'\',\''.$d['uniquevalue'].'\', 0, 1);';

    $count = '';
    if (isset($v->count_products) && isset($d['params']['display_count']) && $d['params']['display_count'] == 1 ) {
        $count = ' <span class="ph-filter-count">'.(int)$v->count_products.'</span>';
    }


    $output .= '<div class="checkbox">';
    $output .= '<label class="ph-checkbox-container"><input type="checkbox" name="tag" value="'.$value.'" '.$checked.' onchange="'.$jsSet.'" />'.$v->title.$count.'<span class="ph-checkbox-checkmark"></span></label>';
    $output .= '</div>';

}

$title = isset($d['titleheader']) && $d['titleheader'] != '' ? $d['titleheader'] : $d['title'];

?><div class="<?php echo $d['s']['c']['panel.panel-default'] ?> <?php echo $dParamAttr; ?>">
	<div class="<?php echo $d['s']['c']['panel-heading'] ?>" role="tab" id="heading<?php echo $dParamAttr; ?>">
		<h4 class="<?php echo $d['s']['c']['panel-title'] ?>">
			<a data-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo $dParamAttr; ?>" class="panel-collapse" aria-label="<?php echo JText::_('COM_PHOCACART_COLLAPSE') . ' ' . $d['title'] ?>"><span class="<?php echo $d['triangle_class'] ?>"></span></a>
			<a data-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo$dParamAttr; ?>" class="panel-collapse" aria-label="<?php echo $title ?>"><?php echo $title ?></a>
		</h4>
	</div>

	<div id="collapse<?php echo $dParamAttr; ?>" class="<?php echo $d['collapse_class'] ?>" role="tabpanel" aria-labelledby="heading<?php echo $dParamAttr; ?>">
		<div class="<?php echo $d['s']['c']['panel-body'] ?>">
			<?php echo $output ?>
		</div>
	</div>
</div>


