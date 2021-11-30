<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$r          = $this->r;
$s			= new PhocacartStatistics();
$link		= 'index.php?option='.$this->t['o'].'&view=';

$cOrdersW	= $s->getNumberOfOrders();
$cOrdersD	= $s->getNumberOfOrders(0);
$cUsersW	= $s->getNumberOfUsers();
$cUsersD	= $s->getNumberOfUsers(0);
$cAmountW	= $s->getAmountOfOrders();
$cAmountD	= $s->getAmountOfOrders(0);

//echo '<form action="index.php" method="post" name="adminForm">';

echo $r->startCp();




?>
			<div class="row ph-cpanel-top-stats">

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color1">
						<div class="ph-cpanel-color-left"><span class="fas fa fa-user"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_TODAY'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cUsersD; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::plural( 'COM_PHOCACART_CUSTOMERS', $cUsersD); ?></div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color2">
						<div class="ph-cpanel-color-left"><span class="fas fa fa-shopping-cart"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_TODAY'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cOrdersD; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::plural( 'COM_PHOCACART_ORDERS', $cOrdersD); ?></div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color3">
						<div class="ph-cpanel-color-left"><span class="fas fa fa-chart-bar"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_TODAY'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cAmountD; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_SALES'); ?></div>
						</div>
					</div>
				</div>


				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color4">
						<div class="ph-cpanel-color-left"><span class="fas fa fa-user"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_THIS_WEEK'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cUsersW; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::plural( 'COM_PHOCACART_CUSTOMERS', $cUsersW); ?></div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color5">
						<div class="ph-cpanel-color-left"><span class="fas fa fa-shopping-cart"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_THIS_WEEK'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cOrdersW; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::plural( 'COM_PHOCACART_ORDERS', $cOrdersW); ?></div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color6">
						<div class="ph-cpanel-color-left"><span class="fas fa fa-chart-bar"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_THIS_WEEK'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cAmountW; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_SALES'); ?></div>
						</div>
					</div>
				</div>

			</div><?php









echo '<div class="ph-box-cp">';
echo '<div class="ph-left-cp">';

echo '<div class="ph-cp-item-box">';
$link	= 'index.php?option='.$this->t['o'].'&view=';
foreach ($this->views as $k => $v) {
	$linkV	= $link . $this->t['c'] . $k;
	echo $r->quickIconButton( $linkV, Text::_($v[0]), $v[1], $v[2], $k);
}
echo '</div>';
echo '</div>';

echo '<civ class="ph-right-cp">';

?>
<div class="ph-cpanel-chart-box">
						<h3 class="ph-cpanel-color-header-block"><?php echo Text::_('COM_PHOCACART_CHART'); ?> (<?php echo Text::_('COM_PHOCACART_THIS_WEEK'); ?>)</h3>
			<?php

			$dataS = $s->getDataChart();




			$s->renderChartJsLine('phChartAreaLine', $dataS['amount'], Text::_('COM_PHOCACART_TOTAL_AMOUNT'), $dataS['orders'], Text::_('COM_PHOCACART_TOTAL_ORDERS'), $dataS['ticks']);
			$s->setFunction('phChartAreaLine', 'Line');
			$s->renderFunctions();

				/*	<div class="ph-chart-legend"><span class="ph-orders">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_ORDERS'); ?> &nbsp; <span class="ph-amount">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_AMOUNT'); ?></div> */ ?>
						<div id="ph-canvas-holder2" class="phChartAreaLineholder" style="width: 97%;" >
                            <canvas id="phChartAreaLine" class="ph-chart-area"s></canvas>
						</div>
					</div><?php



echo '<div class="ph-extension-info-box ph-cpanel-info-box">';
echo '<div class="ph-cpanel-logo">'.HTMLHelper::_('image', $this->t['i'] . 'logo-'.str_replace('phoca', 'phoca-', $this->t['c']).'.png', 'Phoca.cz') . '</div>';
echo '<div class="ph-cpanel-logo-seal">'. HTMLHelper::_('image', $this->t['i'] . 'logo-phoca.png', 'Phoca.cz' ).'</div>';

echo '<h3>'.  Text::_($this->t['l'] . '_VERSION').'</h3>'
.'<p>'.  $this->t['version'] .'</p>';

echo '<h3>'.  Text::_($this->t['l'] . '_COPYRIGHT').'</h3>'
.'<p>© 2007 - '.  date("Y"). ' Jan Pavelka</p>'
.'<p><a href="https://www.phoca.cz/" target="_blank">www.phoca.cz</a></p>';

echo '<h3>'.  Text::_($this->t['l'] . '_LICENSE').'</h3>'
.'<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>';

echo '<h3>'.  Text::_($this->t['l'] . '_TRANSLATION').': '. Text::_($this->t['l'] . '_TRANSLATION_LANGUAGE_TAG').'</h3>'
.'<p>© 2007 - '.  date("Y"). ' '. Text::_($this->t['l'] . '_TRANSLATER'). '</p>'
.'<p>'.Text::_($this->t['l'] . '_TRANSLATION_SUPPORT_URL').'</p>';

echo '<div class="ph-cp-hr"></div>'
.'<div class="btn-group"><a class="btn btn-large btn-primary" href="https://www.phoca.cz/version/index.php?'.$this->t['c'].'='.  $this->t['version'] .'" target="_blank"><i class="icon-loop icon-white"></i>&nbsp;&nbsp;'.  JText::_($this->t['l'] . '_CHECK_FOR_UPDATE') .'</a></div>'
.'<div style="float:right; margin: 0 10px"><a href="https://www.phoca.cz/" target="_blank">'.JHtml::_('image', $this->t['i'] . 'logo.png', 'Phoca.cz' ).'</a></div>';

echo '</div>';

echo '<div class="ph-extension-links-box ph-cpanel-info-box">';
echo $r->getLinks();
echo '</div>';

echo '</div>';

echo '</div>';
echo $r->endCp();

//echo '<input type="hidden" name="option" value="'. $this->t['c'] .'" />';
//echo '<input type="hidden" name="view" value="'. $this->t['c'] .'cp" />';
//echo HTMLHelper::_('form.token');
//echo '</form>';

echo $this->t['modalwindowdynamic'];
















/*


$s			= new PhocacartStatistics();
$r 			= new PhocacartRenderAdminviews();
$link		= 'index.php?option='.$this->t['o'].'&view=';

$cOrdersW	= $s->getNumberOfOrders();
$cOrdersD	= $s->getNumberOfOrders(0);
$cUsersW	= $s->getNumberOfUsers();
$cUsersD	= $s->getNumberOfUsers(0);
$cAmountW	= $s->getAmountOfOrders();
$cAmountD	= $s->getAmountOfOrders(0);




?><form action="index.php" method="post" name="adminForm">


	<div id="phAdminBox" class="row ph-admin-box">
		<div class="col-xs-12 col-sm-2 col-md-2 ph-admin-box-menu"><?php echo JHtmlSidebar::render(); ?></div>

		<?php /* <div id="j-main-container" class="col-xs-12 col-sm-10 col-md-10 ph-admin-box-content">*//*
		echo $r->startMainContainer();
		?>
			<div class="row ph-cpanel-top-stats">
				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color1">
						<div class="ph-cpanel-color-left"><span class="glyphicon glyphicon-user"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_TODAY'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cUsersD; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::plural( 'COM_PHOCACART_CUSTOMERS', $cUsersD); ?></div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color2">
						<div class="ph-cpanel-color-left"><span class="glyphicon glyphicon-shopping-cart"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_TODAY'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cOrdersD; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::plural( 'COM_PHOCACART_ORDERS', $cOrdersD); ?></div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color3">
						<div class="ph-cpanel-color-left"><span class="glyphicon glyphicon-stats"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_TODAY'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cAmountD; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_SALES'); ?></div>
						</div>
					</div>
				</div>


				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color4">
						<div class="ph-cpanel-color-left"><span class="glyphicon glyphicon-user"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_THIS_WEEK'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cUsersW; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::plural( 'COM_PHOCACART_CUSTOMERS', $cUsersW); ?></div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color5">
						<div class="ph-cpanel-color-left"><span class="glyphicon glyphicon-shopping-cart"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_THIS_WEEK'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cOrdersW; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::plural( 'COM_PHOCACART_ORDERS', $cOrdersW); ?></div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-2">
					<div class="ph-cpanel-color ph-cpanel-color6">
						<div class="ph-cpanel-color-left"><span class="glyphicon glyphicon-stats"></span></div>
						<div class="ph-cpanel-color-right">
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_THIS_WEEK'); ?></div>
							<div class="ph-cpanel-stat-value"><?php echo $cAmountW; ?></div>
							<div class="ph-cpanel-color-header"><?php echo Text::_('COM_PHOCACART_SALES'); ?></div>
						</div>
					</div>
				</div>
			</div>


			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="ph-cpanel-icon-box">
						<div class="row">
					<?php
					foreach ($this->views as $k => $v) {
						$linkV	= $link . $this->t['c'] . $k;
						//echo PhocacartRenderAdmin::quickIconButton( $linkV, 'icon-48-'.$k.'.png', JText::_($v[0]), $this->t['i']);
						echo '<div class="col-xs-6 col-sm-6 col-md-6 col-lg-3">';
						echo PhocacartRenderAdmin::quickIconButton( $linkV, Text::_($v[0]), $v[1], $v[2]);
						echo '</div>';
					}
					?>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="ph-cpanel-chart-box">
						<h3 class="ph-cpanel-color-header-block"><?php echo Text::_('COM_PHOCACART_CHART'); ?> (<?php echo Text::_('COM_PHOCACART_THIS_WEEK'); ?>)</h3>
			<?php

			$dataS = $s->getDataChart();




			$s->renderChartJsLine('phChartAreaLine', $dataS['amount'], Text::_('COM_PHOCACART_TOTAL_AMOUNT'), $dataS['orders'], Text::_('COM_PHOCACART_TOTAL_ORDERS'), $dataS['ticks']);
			$s->setFunction('phChartAreaLine', 'Line');
			$s->renderFunctions();

				/*	<div class="ph-chart-legend"><span class="ph-orders">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_ORDERS'); ?> &nbsp; <span class="ph-amount">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_AMOUNT'); ?></div> *//* ?>
						<div id="ph-canvas-holder2" class="phChartAreaLineholder" style="width: 97%;" >
                            <canvas id="phChartAreaLine" class="ph-chart-area"s></canvas>
						</div>
					</div>


					<div class="ph-cpanel-info-box"><?php

							echo '<div class="ph-cpanel-logo">'.HTMLHelper::_('image', $this->t['i'] . 'logo-phoca-cart.png', 'Phoca.cz') . '</div>';

							?><div style="float:right;margin:10px;"><?php

			echo HTMLHelper::_('image', $this->t['i'] . 'logo-phoca.png', 'Phoca.cz' );?></div><?php
			echo '<h3>'.  Text::_($this->t['l'] . '_VERSION').'</h3>'
			.'<p>'.  $this->t['version'] .'</p>';
			echo '<h3>'.  Text::_($this->t['l'] . '_COPYRIGHT').'</h3>'
			.'<p>© 2007 - '.  date("Y"). ' Jan Pavelka</p>'
			.'<p><a href="https://www.phoca.cz/" target="_blank">www.phoca.cz</a></p>';
			echo '<h3>'.  Text::_($this->t['l'] . '_LICENSE').'</h3>'
			.'<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>';
			echo '<h3>'.  Text::_($this->t['l'] . '_TRANSLATION').': '. Text::_($this->t['l'] . '_TRANSLATION_LANGUAGE_TAG').'</h3>'
			.'<p>© 2007 - '.  date("Y"). ' '. Text::_($this->t['l'] . '_TRANSLATER'). '</p>'
			.'<p>'.Text::_($this->t['l'] . '_TRANSLATION_SUPPORT_URL').'</p>';
					echo '<div style="border-top:1px solid #c2c2c2"></div><p>&nbsp;</p>'
			.'<div><a class="btn btn-large btn-primary" href="https://www.phoca.cz/version/index.php?'.$this->t['c'].'='.  $this->t['version'] .'" target="_blank"><i class="icon-loop icon-white"></i>&nbsp;&nbsp;'.  JText::_($this->t['l'] . '_CHECK_FOR_UPDATE') .'</a>'.'<div style="float:right; margin: 0 10px"><a href="https://www.phoca.cz/" target="_blank">'.JHtml::_('image', $this->t['i'] . 'logo.png', 'Phoca.cz' ).'</a></div></div>';

					?></div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-6">
					<div class="ph-cpanel-tips-box">
						<?php echo PhocacartRenderAdmin::getLinks(); ?>
					</div>
				</div>

			</div>


			<p>&nbsp;</p>

			<?php echo $r->endMainContainer(); ?>

		</div>
	</div>

	<input type="hidden" name="option" value="<?php echo $this->t['c'] ?>" />
	<input type="hidden" name="view" value="<?php echo $this->t['c'] ?>cp" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
*/
