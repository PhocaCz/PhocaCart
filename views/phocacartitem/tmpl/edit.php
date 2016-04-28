<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$class		= $this->t['n'] . 'RenderAdminView';
$r 			=  new $class();

?>
<script type="text/javascript">
var phRequestActive = null;

function phCheckRequestStatus(i, task) {
	i++;
	if (i > 30) {
		/* Stop Loop */
		phRequestActive = null;
	}

	if (phRequestActive) {
		setTimeout(function(){
			phCheckRequestStatus(i, task);
		}, 1000);
	} else {
		<?php /*if (task != '<?php echo $this->t['task'] ?>.cancel' && document.id('jform_catid_multiple').value == '') {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . ' - '. $this->escape(JText::_($this->t['l'].'_ERROR_CATEGORY_NOT_SELECTED'));?>');
		} else */ ?> if (task == '<?php echo $this->t['task'] ?>.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			<?php echo $this->form->getField('description_long')->save(); ?>
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else {
			alert('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true);?>');
		}
	}
}
Joomla.submitbutton = function(task) {
	phCheckRequestStatus(0, task);
}
</script><?php
echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="span12 form-horizontal">';
$tabs = array (
'general' 		=> JText::_($this->t['l'].'_GENERAL_OPTIONS'),
'image'			=> JText::_($this->t['l'].'_IMAGE_OPTIONS'),
'attributes'	=> JText::_($this->t['l'].'_ATTRIBUTES'),
'specifications'=> JText::_($this->t['l'].'_SPECIFICATIONS'),
'related'		=> JText::_($this->t['l'].'_RELATED_PRODUCTS'),
'stock' 		=> JText::_($this->t['l'].'_STOCK_OPTIONS'),
'download' 		=> JText::_($this->t['l'].'_DOWNLOAD_OPTIONS'),
'size' 			=> JText::_($this->t['l'].'_SIZE_OPTIONS'),
'publishing' 	=> JText::_($this->t['l'].'_PUBLISHING_OPTIONS'),
'metadata'		=> JText::_($this->t['l'].'_METADATA_OPTIONS')
);
echo $r->navigation($tabs);

echo '<div class="tab-content">'. "\n";

echo '<div class="tab-pane active" id="general">'."\n"; 
// ORDERING cannot be used
$formArray = array ('title', 'alias', 'price', 'price_original', 'tax_id', 'catid_multiple', 'manufacturer_id', 'sku', 'upc', 'ean', 'jan', 'mpn', 'isbn', 'serial_number', 'registration_key', 'external_id', 'external_key', 'external_link', 'external_text', 'access', 'featured', 'video');
echo $r->group($this->form, $formArray);
$formArray = array('description' );
echo $r->group($this->form, $formArray, 1);
$formArray = array('description_long' );
echo $r->group($this->form, $formArray, 1);
//$formArray = array ('upc', 'ean', 'jan', 'mpn', 'isbn');
//echo $r->group($this->form, $formArray);
echo '</div>'. "\n";

// IMAGES
echo '<div class="tab-pane" id="image">'. "\n";
$formArray = array ('image');
echo $r->group($this->form, $formArray);
echo '<h3>'.JText::_($this->t['l'].'_ADDITIONAL_IMAGES').'</h3>';

$i 		= 0;
$url 	= 'index.php?option=com_phocacart&amp;view=phocacartmanager&amp;tmpl=component&amp;manager=productimage&amp;field=jform_image';
$images = PhocaCartImageAdditional::getImagesByProductId($this->item->id);

if (!empty($images)) {
	foreach ($images as $k => $v) {
		echo $r->additionalImagesRow((int)$i, $url, $v->image, 0);
		$i++;
	}
}
$newRow = $r->additionalImagesRow('\' + phRowCountImage +  \'', $url, '', 1);
$newRow = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newRow);
PhocaCartRenderJs::renderJsManageRowImage($i, $newRow, 'a.modal_jform_image', '');
echo $r->addRowButton(JText::_('COM_PHOCACART_ADD_IMAGE'), 'image');
echo '</div>'. "\n";


// ATTRIBUTES, OPTIONS 
echo '<div class="tab-pane" id="attributes">'. "\n";
echo '<h3>'.JText::_($this->t['l'].'_ATTRIBUTES').'</h3>';
$i = 0; // i ... ATTRIBUTES
$j = 0; // j ... OPTIONS
if (!empty($this->attributes)) {
	foreach ($this->attributes as $k => $v) {
		echo $r->additionalAttributesRow((int)$i, $v->title, $v->alias, $v->required, $v->type, 0);
		if((int)$v->id > 0) {
			$options	= PhocaCartAttribute::getOptionsById((int)$v->id);
	
			if (!empty($options)) {
				$m = 0; // m ... NEW BEGINN OF OPTIONS - ADD HEADER (we cannot use $j because it counts and will be not cleared)
				foreach ($options as $k2 => $v2) {
					if ($m == 0) {
						echo $r->headerOption();
					}
					echo $r->additionalOptionsRow((int)$j, (int)$i, $v2->title, $v2->alias, $v2->operator, $v2->amount, $v2->stock, $v2->operator_weight, $v2->weight, $v2->image);
					$j++;
					$m++;
				}
			}
		}
		echo $r->addNewOptionButton((int)$i, 0);//Add new Option Button + ending of additionalAttributesRow BOX
		$i++;
	} 
}


// Attribute	
$newRow = $r->additionalAttributesRow('\' + phRowCountAttribute +  \'', '', '', '', '', 1);
$newRow = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newRow);
PhocaCartRenderJs::renderJsManageRowAttribute($i, $newRow);
echo $r->addRowButton(JText::_('COM_PHOCACART_ADD_ATTRIBUTE'), 'attribute');

// Option
$newRow 	= $r->additionalOptionsRow('\' + phRowCountOption +  \'', '\' + attrid +  \'', '', '', '', '', '', '', '', '');
$newRow 	= preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newRow);
$newHeader	= $r->headerOption();
$newHeader 	= preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newHeader);
PhocaCartRenderJs::renderJsManageRowOption($j, $newRow, $newHeader);

echo '<div>&nbsp;</div>';
//echo '</div>'. "\n";

echo '</div>'. "\n";



// SPECIFICATIONS
echo '<div class="tab-pane" id="specifications">'. "\n";
echo '<h3>'.JText::_($this->t['l'].'_SPECIFICATIONS').'</h3>';
$i = 0; //
if (!empty($this->specifications)) {
	foreach ($this->specifications as $k => $v) {
		if ($i == 0) {
			echo $r->headerSpecification();
		}
		echo $r->additionalSpecificationsRow((int)$i, $v->title, $v->alias, $v->value, $v->alias_value, $v->group_id, 0);
		$i++;
	} 
}
	
$newRow = $r->additionalSpecificationsRow('\' + phRowCountSpecification +  \'', '', '', '', '', '', 1);
$newRow = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newRow);
$newHeader	= $r->headerSpecification();
$newHeader 	= preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newHeader);
PhocaCartRenderJs::renderJsManageRowSpecification($i, $newRow, $newHeader);
echo $r->addRowButton(JText::_('COM_PHOCACART_ADD_PARAMETER'), 'specification');

echo '<div>&nbsp;</div>';
//echo '</div>'. "\n";

echo '</div>'. "\n";



/*
echo '<div class="tab-pane" id="attributes">'. "\n";
//$formArray = array ('attributes');
//echo $r->group($this->form, $formArray);

echo '<h3>'.JText::_($this->t['l'].'_OPTION').'</h3>';
$i = 0;
echo '<div class="ph-row">'."\n"
. '<div class="span3">'. JText::_($this->t['l'].'_TITLE') . '</div>'
. '<div class="span1">&nbsp;</div>'
. '<div class="span2">'. JText::_($this->t['l'].'_VALUE') . '</div>'
. '<div class="span2">&nbsp;</div>'
.'</div><div style="clear:both;"></div>'."\n";
if (!empty($this->attributeoption)) {
	
	//echo '<div class="row-fluid span8">'."\n";
	foreach ($this->attributeoption as $k => $v) {
		echo $r->additionalAttributesRow((int)$i, $v->title, $suffix, $v->operator, $v->amount, $v->stock);
		$i++;
	}
	//echo '</div>';
}
	
$suffix  = 'addAttributes';	
$newRowA = $r->additionalAttributesRow('\' + phRowCount'.$suffix.' +  \'', '', $suffix, '', '');
$newRowA = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $newRowA);
PhocaCartRenderJs::renderJsManageRow($i, $newRowA, '', $suffix);
echo $r->addRowButton(JText::_('COM_PHOCACART_ADD_OPTION'), $suffix);

echo '<div>&nbsp;</div>';
//echo '</div>'. "\n";

echo '</div>'. "\n";
*/






echo '<div class="tab-pane" id="related">'. "\n";
$formArray = array ('related');
echo $r->group($this->form, $formArray);
echo '</div>'. "\n";
echo '<div class="tab-pane" id="stock">'. "\n";
$formArray = array ('stock', 'min_quantity', 'stockstatus_a_id', 'stockstatus_n_id');
echo $r->group($this->form, $formArray);
echo '</div>'. "\n";

echo '<div class="tab-pane" id="download">'. "\n";
$formArray = array ('download_folder', 'download_file', 'download_token', 'download_hits');
echo $r->group($this->form, $formArray);
echo '</div>'. "\n";

echo '<div class="tab-pane" id="size">'. "\n";
$formArray = array ('length', 'width', 'height', 'weight', 'volume', 'unit_amount', 'unit_unit',);
echo $r->group($this->form, $formArray);
echo '</div>'. "\n";

echo '<div class="tab-pane" id="publishing">'."\n"; 
foreach($this->form->getFieldset('publish') as $field) {
	
	echo '<div class="control-group">';
	if (!$field->hidden) {
		echo '<div class="control-label">'.$field->label.'</div>';
	}
	echo '<div class="controls">';
	echo $field->input;
	echo '</div></div>';
}
echo '</div>';
				
echo '<div class="tab-pane" id="metadata">'. "\n";
echo $this->loadTemplate('metadata');
echo '</div>'. "\n";
	
				
echo '</div>';//end tab content
echo '</div>';//end span10
// Second Column
//echo '<div class="span2">';


//echo '</div>';//end span2
echo $r->formInputs();
echo $r->endForm();
echo PhocaCartRenderJs::renderAjaxTopHtml();
?>
	
