<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

$layoutPC 	= new FileLayout('form_privacy_checkbox', null, array('component' => 'com_phocacart'));


echo '<div id="ph-pc-question-box" class="pc-view pc-question-view'.$this->p->get( 'pageclass_sfx' ).'">';
echo PhocacartRenderFront::renderHeader(array(Text::_('COM_PHOCACART_ASK_A_QUESTION')));


if ( isset($this->item[0]->title) && $this->item[0]->title != '') {
	echo '<h2>'.$this->item[0]->title.'</h2>';
}


if (isset($this->item[0])) {

	echo '<div class="'.$this->s['c']['row'].'">';
	echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].'">';
	$x = $this->item[0];

	$link = Route::_(PhocacartRoute::getItemRoute($x->id, $x->catid, $x->alias, $x->catalias));
	// IMAGE
	echo '<div class="ph-item-image-full-box ph-item-image-full-left-box">';
	$image 	= PhocacartImage::getThumbnailName($this->t['pathitem'], $x->image, 'medium');

	if (isset($image->rel) && $image->rel != '') {
		echo '<a href="'.$link.'" >';
		echo '<img src="'.Uri::base(true).'/'.$image->rel.'" alt="" class="'.$this->s['c']['img-responsive'].' img-thumbnail ph-image-full ph-img-block"';
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

$hiddenfield =	' 		<div class="'.$this->s['c']['control-group'].' '.$this->p->get('hidden_field_class').'">'.
				'			<div class="'.$this->s['c']['controls'].' input-prepend input-group">'.
				'				'. $this->form->getInput($this->p->get('hidden_field_name')) .
				'			</div>'.
				'		</div>';

if ( isset($this->t['question_description']) && $this->t['question_description'] != '') {
	echo '<div class="ph-desc">'. $this->t['question_description']. '</div>';
}


$name = str_replace('form-control', $this->s['c']['inputbox.form-control'], $this->form->getInput('name'));
$email = str_replace('form-control', $this->s['c']['inputbox.form-control'], $this->form->getInput('email'));
$phone = str_replace('form-control', $this->s['c']['inputbox.form-control'], $this->form->getInput('phone'));
$message = str_replace('form-control', $this->s['c']['inputbox.textarea'], $this->form->getInput('message'));

?>



<div>&nbsp;</div>
<div class="<?php echo $this->s['c']['row'] ?>">
<div class="<?php echo $this->s['c']['col.xs12.sm6.md6'] ?>">

<form action="<?php echo $this->t['action'] ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="<?php echo $this->s['c']['control-group'] ?>">
	<div class="<?php echo $this->s['c']['control-label'] ?>"><?php echo $this->form->getLabel('name'); ?></div>
	<div class="<?php echo $this->s['c']['controls'] ?>"><?php echo $name;
	if($this->p->get('hidden_field_position')==1){echo $hiddenfield;}  ?></div>
</div>
<div class="<?php echo $this->s['c']['control-group'] ?>">
	<div class="<?php echo $this->s['c']['control-label'] ?>"><?php echo $this->form->getLabel('email'); ?></div>
	<div class="<?php echo $this->s['c']['controls'] ?>"><?php echo $email;
	if($this->p->get('hidden_field_position')==2){echo $hiddenfield;}  ?></div>
</div>
<div class="<?php echo $this->s['c']['control-group'] ?>">
	<div class="<?php echo $this->s['c']['control-label'] ?>"><?php echo $this->form->getLabel('phone'); ?></div>
	<div class="<?php echo $this->s['c']['controls'] ?>"><?php echo $phone;
	if($this->p->get('hidden_field_position')==3){echo $hiddenfield;}  ?></div>
</div>
<div class="<?php echo $this->s['c']['control-group'] ?>">
	<div class="<?php echo $this->s['c']['control-label'] ?>"><?php echo $this->form->getLabel('message'); ?></div>
	<div class="<?php echo $this->s['c']['controls'] ?>"><?php echo $message;
	if($this->p->get('hidden_field_position')==4){echo $hiddenfield;}  ?></div>
</div>

<div class="<?php echo $this->s['c']['control-group'] ?>">
	<div class="<?php echo $this->s['c']['control-label'] ?>"><?php echo $this->form->getLabel('phq_captcha'); ?></div>
	<div class="<?php echo $this->s['c']['controls'] ?>"><?php echo $this->form->getInput('phq_captcha'); ?></div>
</div>

<?php
if ($this->t['display_question_privacy_checkbox'] > 0) {
	$d					= array();
	$d['s']			    = $this->s;
	$d['label_text']	= $this->t['question_privacy_checkbox_label_text'];
	$d['id']			= 'phAskQuestionPrivacyCheckbox';
	$d['name']			= 'privacy';
	$d['class']			= $this->s['c']['pull-right'] . ' '. $this->s['c']['inputbox.checkbox'] . ' ph-askquestion-checkbox-confirm';
	$d['display']		= $this->t['display_question_privacy_checkbox'];

	echo '<div class="ph-cb"></div>';
	echo $layoutPC->render($d);
}
?>


<div class="btn-toolbar">
	<div class="btn-group">
		<button type="submit" class="<?php echo $this->s['c']['btn.btn-primary'] ?>">
			<span class="<?php echo $this->s['i']['submit'] ?>"></span> <?php echo Text::_('COM_PHOCACART_SUBMIT');?></button>
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
	<?php echo HTMLHelper::_('form.token');?>

</form>

</div>
</div>
</div>
<?php /*
<script type='text/javascript'>
setTimeout(function () { window.close();}, 1000);
</script>
*/ ?>


