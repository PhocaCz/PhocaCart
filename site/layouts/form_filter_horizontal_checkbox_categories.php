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
$dParamAttr		= str_replace(array('[',']'), '', $d['param']);

$output = '';
$items = PhocacartCategory::nestedToFlat($d['items']);
foreach ($items as $k => $v) {
  $v = (object)$v;
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


  $output .= '<li>';
  $output .= '<label class="'.$d['s']['c']['dropdown-item'].' ph-checkbox-container">';
  $output .= '<span class="ps-' . $v->nested_level . ' ms-' . $v->nested_level . '">';
  $output .= '<input class="'.$d['s']['c']['inputbox.checkbox'].'" type="checkbox" name="tag" value="'.$value.'" '.$checked.' onchange="'.$jsSet.'" />'.$v->title.$count.'<span class="ph-checkbox-checkmark"></span>';
  $output .= '</span>';
  $output .= '</label>';
  $output .= '</li>';

}

$title = isset($d['titleheader']) && $d['titleheader'] != '' ? $d['titleheader'] : $d['title'];
?>
<span class="<?php echo $d['s']['c']['dropdown'] ?> filter-<?php echo $dParamAttr; ?>">
  <button class="<?php echo $d['s']['c']['btn.btn-outline-secondary'] ?> <?php echo $d['s']['c']['dropdown-toggle'] ?>" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><?php echo $title ?></button>
  <ul <?php echo $d['s']['a']['dropdown'] ?> class="<?php echo $d['s']['c']['dropdown-menu'] ?>">
    <?php echo $output ?>
	</ul>
</span>
