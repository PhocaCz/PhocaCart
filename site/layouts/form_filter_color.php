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
$iconType		= $d['s']['i']['icon-type'];
?>
<div class="<?php echo $d['s']['c']['panel.panel-default'] ?>">
	<div class="<?php echo $d['s']['c']['panel-heading'] ?>" role="tab" id="heading<?php echo $dParamAttr; ?>">
		<h4 class="<?php echo $d['s']['c']['panel-title'] ?>">
			<a data-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo $dParamAttr; ?>" class="panel-collapse"><span class="<?php echo $d['s']['i']['triangle-bottom'] ?>"></span></a>
			<a data-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo$dParamAttr; ?>" class="panel-collapse"><?php echo $d['title'] ?></a>
		</h4>
	</div>

	<div id="collapse<?php echo $dParamAttr; ?>" class="<?php echo $d['s']['c']['panel-collapse.collapse.in'] ?>" role="tabpanel" aria-labelledby="heading<?php echo $dParamAttr; ?>">
		<div class="<?php echo $d['s']['c']['panel-body'] ?> ph-panel-body-color">
				<div class="ph-mod-color-box">
			<?php


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
				}

				if (isset($v->color) && $v->color != '') {
					echo '<a href="#" class="phSelectBoxButton '.$class.' color-'.str_replace('#', '', $v->color).'" style="background-color:'.$v->color.'" onclick="phChangeFilter(\''.$d['param'].'\', \''. $value.'\', '.(int)$checkedInt.', \''.$d['formtype'].'\', \''.$d['uniquevalue'].'\');return false;" title="'.$v->title.'">&nbsp;</a>';
				}
			}
			echo '</div>';
		?>
		</div>
	</div>
</div>
