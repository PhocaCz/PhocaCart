<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div id="ph-pc-question-box" class="pc-question-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo PhocacartRenderFront::renderHeader(array(JText::_('COM_PHOCACART_ASK_A_QUESTION')));


if ( isset($this->item[0]->title) && $this->item[0]->title != '') {
	echo '<h2>'.$this->item[0]->title.'</h2>';
}


if (isset($this->item[0])) {

	echo '<div class="row">';
	echo '<div class="col-xs-12 col-sm-6 col-md-6">';
	$x = $this->item[0];
	
	$link = JRoute::_(PhocacartRoute::getItemRoute($x->id, $x->catid, $x->alias, $x->catalias));
	// IMAGE
	echo '<div class="ph-item-image-full-box ph-item-image-full-left-box">';
	$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'medium');

	if (isset($image->rel) && $image->rel != '') {
		echo '<a href="'.$link.'" >';
		echo '<img src="'.JURI::base(true).'/'.$image->rel.'" alt="" class="img-responsive img-thumbnail ph-image-full ph-img-block"';
		if (isset($this->t['image_width']) && (int)$this->t['image_width'] > 0 && isset($this->t['image_height']) && (int)$this->t['image_height'] > 0) {
			echo ' style="width:'.$this->t['image_width'].'px;height:'.$this->t['image_height'].'px"';
		}
		echo ' />';
		echo '</a>';
	}
	echo '</div>';
	
	echo '</div>'. "\n";
	echo '</div>'. "\n";

}

$hiddenfield =	' 		<div class="control-group '.$this->p->get('hidden_field_class').'">'.
				'			<div class="controls input-prepend input-group">'.
				'				'. $this->form->getInput($this->p->get('hidden_field_name')) .
				'			</div>'.
				'		</div>';
				
if ( isset($this->t['question_description']) && $this->t['question_description'] != '') {
	echo '<div class="ph-desc">'. JHTML::_('content.prepare', $this->t['question_description']). '</div>';
}
?>
<div>&nbsp;</div>
<div class="row">
<div class="col-xs-12 col-sm-6 col-md-6">

<form action="<?php echo $this->t['action'] ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('name'); 
	if($this->p->get('hidden_field_position')==1){echo $hiddenfield;}  ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('email'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('email'); 
	if($this->p->get('hidden_field_position')==2){echo $hiddenfield;}  ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('phone'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('phone'); 
	if($this->p->get('hidden_field_position')==3){echo $hiddenfield;}  ?></div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('message'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('message'); 
	if($this->p->get('hidden_field_position')==4){echo $hiddenfield;}  ?></div>
</div>

<div class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('phq_captcha'); ?></div>
	<div class="controls"><?php echo $this->form->getInput('phq_captcha'); ?></div>
</div>



<div class="btn-toolbar">
	<div class="btn-group">
		<button type="submit" class="btn btn-primary">
			<i class="glyphicon glyphicon-ok icon-ok"></i> <?php echo JText::_('COM_PHOCACART_SUBMIT');?></button>
	</div>
</div>	

	<?php
	echo $this->form->getInput('product_id');
	echo $this->form->getInput('category_id');
	?>
	<input type="hidden" name="view" value="question" />
	<input type="hidden" name="cid" value="cid" />
	<input type="hidden" name="id" value="id" />
	<input type="hidden" name="option" value="com_phocacart" />
	<input type="hidden" name="task" value="question.submit" />
	<?php echo JHtml::_('form.token');?>

</form>

</div>
</div>
</div>