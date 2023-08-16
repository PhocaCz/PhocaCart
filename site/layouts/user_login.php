<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

require_once JPATH_SITE.'/components/com_users/src/Service/Router.php';
jimport( 'joomla.application.module.helper' );
$module = ModuleHelper::getModule('mod_login');
$mP 	= new Registry();
$mP->loadString($module->params);

$lang = Factory::getLanguage();
$lang->load('mod_login');

$d  = $displayData;

echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_LOGIN').'</div>'. "\n";

echo '<form action="'.Route::_('index.php', true, $mP->get('usesecure')).'" method="post" id="ph-login-form" class="'.$d['s']['c']['form-inline'].'" role="form">';

echo '<div id="ph-form-login-username" class="'.$d['s']['c']['control-group.form_inline'].'">'. "\n";
echo '<div class="'.$d['s']['c']['form-group'].'">'. "\n";
echo '<label class="sr-only" for="modlgn-username">'.Text::_('MOD_LOGIN_VALUE_USERNAME') .'</label>'. "\n";
echo '<input id="ph-modlgn-username" type="text" name="username" class="'.$d['s']['c']['inputbox.form-control'].'" tabindex="0" placeholder="'.Text::_('MOD_LOGIN_VALUE_USERNAME') .'" />'. "\n";
echo ' </div>'. "\n";
echo ' <div class="'.$d['s']['c']['form-group'].'">'. "\n";
echo '<label class="sr-only" for="modlgn-passwd">'.Text::_('JGLOBAL_PASSWORD') .'</label>'. "\n";
echo '<input id="ph-modlgn-passwd" type="password" name="password" class="'.$d['s']['c']['inputbox.form-control'].'" tabindex="0" size="18" placeholder="'.Text::_('JGLOBAL_PASSWORD') .'" />'. "\n";
echo ' </div>'. "\n";

if (PluginHelper::isEnabled('system', 'remember')) {
    //echo '<div id="ph-form-login-remember" class="checkbox">'. "\n";
    //echo '<label for="modlgn-remember" class="control-label">'. Text::_('MOD_LOGIN_REMEMBER_ME') .'</label> <input id="modlgn-remember" type="checkbox" name="remember" class="form-control" value="yes"/>'. "\n";
    //echo '</div>'. "\n";

    ?>
    <div class="mod-login__remember form-group">
        <div id="form-login-remember-pcc" class="form-check">
            <label class="form-check-label">
                <input type="checkbox" name="remember" class="<?php echo $d['s']['c']['inputbox.checkbox'] ?> form-check-input" value="yes">
                <?php echo Text::_('MOD_LOGIN_REMEMBER_ME'); ?>
            </label>
        </div>
    </div><?php
}

$extraButtons = AuthenticationHelper::getLoginButtons('com-users-login__form');
if (!empty($extraButtons)) {
    foreach ($extraButtons as $button) {
        $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
            return substr($key, 0, 5) == 'data-';
        });
        ?>
        <div class="com-users-login__submit control-group">
            <div class="controls">
                <button type="button"
                        class="btn btn-secondary w-100 <?php echo $button['class'] ?? '' ?>"
                        <?php foreach ($dataAttributeKeys as $key) : ?>
                            <?php echo $key ?>="<?php echo $button[$key] ?>"
                        <?php endforeach; ?>
                        <?php if ($button['onclick']) : ?>
                        onclick="<?php echo $button['onclick'] ?>"
                        <?php endif; ?>
                        title="<?php echo Text::_($button['label']) ?>"
                        id="<?php echo $button['id'] ?>"
                >
                    <?php if (!empty($button['icon'])) : ?>
                        <span class="<?php echo $button['icon'] ?>"></span>
                    <?php elseif (!empty($button['image'])) : ?>
                        <?php echo HTMLHelper::_('image', $button['image'], Text::_($button['tooltip'] ?? ''), [
                            'class' => 'icon',
                        ], true) ?>
                    <?php elseif (!empty($button['svg'])) : ?>
                        <?php echo $button['svg']; ?>
                    <?php endif; ?>
                    <?php echo Text::_($button['label']) ?>
                </button>
            </div>
        </div>
    <?php
    }
}




echo '<button type="submit" tabindex="0" name="Submit" class="'.$d['s']['c']['btn.btn-primary'].' ph-btn">'. Text::_('JLOGIN') .'</button>'. "\n";
echo '</div>'. "\n";// end form inline


echo '<ul class="unstyled ph-li-inline">'. "\n";
echo '<li><a href="'.Route::_('index.php?option=com_users&view=remind').'">'.Text::_('MOD_LOGIN_FORGOT_YOUR_USERNAME').'</a></li>'. "\n";
echo '<li><a href="'.Route::_('index.php?option=com_users&view=reset').'">'.Text::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD').'</a></li>'. "\n";
echo '</ul>'. "\n";

echo '<div class="ph-cb"></div>';

echo '<input type="hidden" name="option" value="com_users" />'. "\n";
echo '<input type="hidden" name="task" value="user.login" />'. "\n";
echo '<input type="hidden" name="return" value="'.$d['t']['actionbase64'].'" />'. "\n";
echo HTMLHelper::_('form.token');
echo '</form>';
?>
