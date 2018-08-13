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


// to hide as default
// class="glyphicon glyphicon-triangle-bottom" ==> class="glyphicon glyphicon-triangle-right"
// class="panel-collapse collapse in" ==> class="panel-collapse collapse"

?><div class="panel panel-default">
	<div class="panel-heading" role="tab" id="heading<?php echo $dParamAttr; ?>">
		<h4 class="panel-title">
			<a data-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo $dParamAttr; ?>" class="panel-collapse"><span class="glyphicon glyphicon-triangle-bottom"></span></a> 
			<a data-toggle="collapse" href="#collapse<?php echo $dParamAttr; ?>" aria-expanded="true" aria-controls="collapse<?php echo$dParamAttr; ?>" class="panel-collapse"><?php echo $d['title'] ?></a>
		</h4>
	</div>
			
	<div id="collapse<?php echo $dParamAttr; ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading<?php echo $dParamAttr; ?>">
		<div class="panel-body">
			<?php
			foreach ($d['items'] as $k => $v) {
			
				$checked 	= '';
				$value		= htmlspecialchars($v->alias);
				if (isset($d['nrinalias']) && $d['nrinalias'] == 1) {
					$value 		= (int)$v->id .'-'. htmlspecialchars($v->alias);
				} 
				
				if (in_array($value, $d['getparams'])) {
					$checked 	= 'checked';
				}
				
				echo '<div class="checkbox">';
				echo '<label><input type="checkbox" name="tag" value="'.$value.'" '.$checked.' onchange="phChangeFilter(\''.$d['param'].'\', \''. $value.'\', this, \''.$d['formtype'].'\',\''.$d['uniquevalue'].'\');" />'.$v->title.'</label>';
				echo '</div>';
				
			}
		?>
		</div>
	</div>
</div>