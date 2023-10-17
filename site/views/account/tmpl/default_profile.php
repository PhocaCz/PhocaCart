<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


/*
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
//JHtml::_('formbehavior.chosen', 'select');*/

//load user_profile plugin language
$lang = Factory::getLanguage();
$lang->load('plg_user_profile', JPATH_ADMINISTRATOR);

/*
?>
<div class="profile-edit<?php /* echo $this->pageclass_sfx *//* ?>">
<?php if ($this->p->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
<?php endif; ?>

<script type="text/javascript">
	Joomla.twoFactorMethodChange = function(e)
	{
		var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

		jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function(i, el) {
			if (el.id != selectedPane)
			{
				jQuery('#' + el.id).hide(0);
			}
			else
			{
				jQuery('#' + el.id).show(0);
			}
		});
	}
</script>

<?php
*/
// Header
echo '<div class="'.$this->s['c']['row'].' ph-account-box-row" >';
echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-account-box-header" id="phaccountaddressedit"><h3>'.Text::_('COM_PHOCACART_EDIT_MY_PROFILE').'</h3></div>';
echo '</div>';

//echo '<form action="'.$this->t['linkaccount'].'" method="post" class="form-horizontal form-validate" role="form" id="phcheckoutAddress">';

echo '<div class="'.$this->s['c']['row'].' ph-account-box-action">';
echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-account-billing-row" id="phUserProfile" >';
//echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_USER_PROFILE').'</div>';

echo $this->loadTemplate('edit');
echo '</div>';



/*
?>

<?php foreach ($this->form->getFieldsets() as $group => $fieldset):// Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($group);

	if ($group != 'core') {
		continue;
	}
	?>
	<?php if (count($fields)):
	/* ?>
	<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
		<legend><?php echo Text::_($fieldset->label); ?></legend>
		<?php endif; *//* ?>
		<?php foreach ($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<div class="<?php echo $this->s['c']['control-group'] ?>">
					<div class="<?php echo $this->s['c']['controls'] ?>">
						<?php echo $field->input;?>
					</div>
				</div>
			<?php else:?>
				<div class="<?php echo $this->s['c']['control-group'] ?>">
					<div class="<?php echo $this->s['c']['control-label'] ?>">
						<?php echo $field->label; ?>
						<?php if (!$field->required && $field->type != 'Spacer') : ?>
						<span class="optional"><?php echo Text::_('COM_USERS_OPTIONAL'); ?></span>
						<?php endif; ?>
					</div>
					<div class="<?php echo $this->s['c']['controls'] ?>">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endif;?>
		<?php endforeach;?>
	<?php /* </fieldset> *//* ?>
	<?php endif;?>
<?php endforeach;?>

<?php if (count($this->twofactormethods) > 1): ?>
	<fieldset>
		<legend><?php echo Text::_('COM_USERS_PROFILE_TWO_FACTOR_AUTH') ?></legend>

		<div class="<?php echo $this->s['c']['control-group'] ?>">
			<div class="<?php echo $this->s['c']['control-label'] ?>">
				<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="<?php echo $this->s['c']['hastooltip'] ?>"
					   title="<strong><?php echo Text::_('COM_USERS_PROFILE_TWOFACTOR_LABEL') ?></strong><br/><?php echo Text::_('COM_USERS_PROFILE_TWOFACTOR_DESC') ?>">
					<?php echo Text::_('COM_USERS_PROFILE_TWOFACTOR_LABEL'); ?>
				</label>
			</div>
			<div class="controls">
				<?php echo HTMLHelper::_('select.genericlist', $this->twofactormethods, 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false) ?>
			</div>
		</div>
		<div id="com_users_twofactor_forms_container">
			<?php foreach($this->twofactorform as $form): ?>
			<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
			<div id="com_users_twofactor_<?php echo $form['method'] ?>" style="<?php echo $style; ?>">
				<?php echo $form['form'] ?>
			</div>
			<?php endforeach; ?>
		</div>
	</fieldset>

	<fieldset>
		<legend>
			<?php echo Text::_('COM_USERS_PROFILE_OTEPS') ?>
		</legend>
		<div class="alert alert-info">
			<?php echo Text::_('COM_USERS_PROFILE_OTEPS_DESC') ?>
		</div>
		<?php if (empty($this->otpConfig->otep)): ?>
		<div class="alert alert-warning">
			<?php echo Text::_('COM_USERS_PROFILE_OTEPS_WAIT_DESC') ?>
		</div>
		<?php else: ?>
		<?php foreach ($ths->otpConfig->otep as $otep): ?>
		<span class="<?php echo $this->s['c']['col.xs12.sm3.md3']?>">
			<?php echo substr($otep, 0, 4) ?>-<?php echo substr($otep, 4, 4) ?>-<?php echo substr($otep, 8, 4) ?>-<?php echo substr($otep, 12, 4) ?>
		</span>
		<?php endforeach; ?>
		<div class="ph-cb"></div>
		<?php endif; ?>
	</fieldset>
<?php endif; ?>

<?php
*/





/*
echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' '.$this->s['c']['pull-right'].' ph-right ph-account-address-save">';
echo '<button class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-btn"><span class="'.$this->s['i']['save'].'"></span> '.Text::_('COM_PHOCACART_SAVE').'</button>';
//echo '<input type="submit" value="submit" />';
echo '</div>';
*/
echo '</div>';// end row


//echo '<div class="ph-cb"></div>';




//echo '<div class="ph-cb"></div>';
//echo '</div>'."\n";// end box action

/*
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
echo '<input type="hidden" name="task" value="account.saveprofile" />'. "\n";
echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
echo HTMLHelper::_('form.token');
echo '</form>'. "\n";
*/

/*
?>

<form id="member-profile" action="<?php echo Route::_('index.php?option=com_phocacart&task=account.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
<?php foreach ($this->form->getFieldsets() as $group => $fieldset):// Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($group);?>
	<?php if (count($fields)):
	?>
	<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
		<legend><?php echo Text::_($fieldset->label); ?></legend>
		<?php endif;?>
		<?php foreach ($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<div class="control-group">
					<div class="controls">
						<?php echo $field->input;?>
					</div>
				</div>
			<?php else:?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
						<?php if (!$field->required && $field->type != 'Spacer') : ?>
						<span class="optional"><?php echo Text::_('COM_USERS_OPTIONAL'); ?></span>
						<?php endif; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endif;?>
		<?php endforeach;?>
	</fieldset>
	<?php endif;?>
<?php endforeach;?>

<?php if (count($this->twofactormethods) > 1): ?>
	<fieldset>
		<legend><?php echo Text::_('COM_USERS_PROFILE_TWO_FACTOR_AUTH') ?></legend>

		<div class="control-group">
			<div class="control-label">
				<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="<?php echo $this->s['c']['hastooltip'] ?>"
					   title="<strong><?php echo Text::_('COM_USERS_PROFILE_TWOFACTOR_LABEL') ?></strong><br/><?php echo Text::_('COM_USERS_PROFILE_TWOFACTOR_DESC') ?>">
					<?php echo Text::_('COM_USERS_PROFILE_TWOFACTOR_LABEL'); ?>
				</label>
			</div>
			<div class="controls">
				<?php echo HTMLHelper::_('select.genericlist', $this->twofactormethods, 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false) ?>
			</div>
		</div>
		<div id="com_users_twofactor_forms_container">
			<?php foreach($this->twofactorform as $form): ?>
			<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
			<div id="com_users_twofactor_<?php echo $form['method'] ?>" style="<?php echo $style; ?>">
				<?php echo $form['form'] ?>
			</div>
			<?php endforeach; ?>
		</div>
	</fieldset>

	<fieldset>
		<legend>
			<?php echo Text::_('COM_USERS_PROFILE_OTEPS') ?>
		</legend>
		<div class="alert alert-info">
			<?php echo Text::_('COM_USERS_PROFILE_OTEPS_DESC') ?>
		</div>
		<?php if (empty($this->otpConfig->otep)): ?>
		<div class="alert alert-warning">
			<?php echo Text::_('COM_USERS_PROFILE_OTEPS_WAIT_DESC') ?>
		</div>
		<?php else: ?>
		<?php foreach ($this->otpConfig->otep as $otep): ?>
		<span class="span3">
			<?php echo substr($otep, 0, 4) ?>-<?php echo substr($otep, 4, 4) ?>-<?php echo substr($otep, 8, 4) ?>-<?php echo substr($otep, 12, 4) ?>
		</span>
		<?php endforeach; ?>
		<div class="clearfix"></div>
		<?php endif; ?>
	</fieldset>
<?php endif; ?>

		<div class="form-actions">
			<button type="submit" class="btn btn-primary validate"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
			<a class="btn btn-primary" href="<?php echo Route::_(''); ?>" title="<?php echo Text::_('JCANCEL'); ?>"><?php echo Text::_('JCANCEL'); ?></a>

			<input type="hidden" name="option" value="com_phocacart" />
			<input type="hidden" name="task" value="account.saveprofile" />
			<?php
			echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
			echo HTMLHelper::_('form.token');
			?>
		</div>
	</form>
</div>*/ ?>
