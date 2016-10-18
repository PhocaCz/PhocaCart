<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
?>
<form action="index.php" method="post" name="adminForm">

<div id="j-sidebar-container" class="span2"><?php echo JHtmlSidebar::render(); ?></div>

<div id="j-main-container" class="span10">
	<div class="adminform">
		<div class="ph-cpanel-left">
			<div id="cpanel"><?php
			
			
$s		= new PhocaCartStatistics();
$class	= $this->t['n'] . 'RenderAdmin';
$link	= 'index.php?option='.$this->t['o'].'&view=';
foreach ($this->views as $k => $v) {
	$linkV	= $link . $this->t['c'] . $k;
	//echo $class::quickIconButton( $linkV, 'icon-48-'.$k.'.png', JText::_($v[0]), $this->t['i']);
	echo $class::quickIconButton( $linkV, JText::_($v[0]), $v[1], $v[2]);
}
				?><div style="clear:both">&nbsp;</div>
				<p>&nbsp;</p>
				<div class="alert alert-block alert-info ph-w80">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<?php echo $class::getLinks(); ?>
				</div>
			</div>
		</div>
		
		<?php 
		$cOrdersW	= $s->getNumberOfOrders();
		$cOrdersD	= $s->getNumberOfOrders(1);
		$cUsersW	= $s->getNumberOfUsers();
		$cUsersD	= $s->getNumberOfUsers(1);
		$cAmountW	= $s->getAmountOfOrders();
		$cAmountD	= $s->getAmountOfOrders(1);
		
		?>
	
		<div class="ph-cpanel-right">
		
			<div class="row">
				<div class="span12 ph-cpanel-infobox ph-cpanel-infobox-h3"><h3 class="ph-cpanel-color"><?php echo JText::_('COM_PHOCACART_TODAY') ?></h3></div>
			</div>
			<div class="row">
				<div class="span4 ph-cpanel-infobox ph-cpanel-color"><h4 class="ph-cpanel-color-header"><span class="glyphicon glyphicon-user"></span> <?php echo JText::_('COM_PHOCACART_CUSTOMERS'); ?></h4><div class="ph-cpanel-stat-value"><?php echo $cUsersD; ?></div><div class="ph-cb"></div></div>
				<div class="span4 ph-cpanel-infobox ph-cpanel-color"><h4 class="ph-cpanel-color-header"><span class="glyphicon glyphicon-shopping-cart"></span> <?php echo JText::_('COM_PHOCACART_ORDERS'); ?></h4><div class="ph-cpanel-stat-value"><?php echo $cOrdersD; ?></div><div class="ph-cb"></div></div>
				<div class="span4 ph-cpanel-infobox ph-cpanel-color"><h4 class="ph-cpanel-color-header"><span class="glyphicon glyphicon-stats"></span>  <?php echo JText::_('COM_PHOCACART_SALES'); ?></h4><div class="ph-cpanel-stat-value"><?php echo $cAmountD; ?></div><div class="ph-cb"></div></div>
			</div>
			
			<div class="row">
				<div class="span12 ph-cpanel-infobox ph-cpanel-infobox-h3"><h3 class="ph-cpanel-color"><?php echo JText::_('COM_PHOCACART_THIS_WEEK'); ?></h3></div>
			</div>
			
			<div class="row">
				<div class="span4 ph-cpanel-infobox ph-cpanel-color"><h4 class="ph-cpanel-color-header"><span class="glyphicon glyphicon-user"></span> <?php echo JText::_('COM_PHOCACART_CUSTOMERS'); ?></h4><div class="ph-cpanel-stat-value"><?php echo $cUsersW; ?></div><div class="ph-cb"></div></div>
				<div class="span4 ph-cpanel-infobox ph-cpanel-color"><h4 class="ph-cpanel-color-header"><span class="glyphicon glyphicon-shopping-cart"></span> <?php echo JText::_('COM_PHOCACART_ORDERS'); ?></h4><div class="ph-cpanel-stat-value"><?php echo $cOrdersW; ?></div><div class="ph-cb"></div></div>
				<div class="span4 ph-cpanel-infobox ph-cpanel-color"><h4 class="ph-cpanel-color-header"><span class="glyphicon glyphicon-stats"></span>  <?php echo JText::_('COM_PHOCACART_SALES'); ?></h4><div class="ph-cpanel-stat-value"><?php echo $cAmountW; ?></div><div class="ph-cb"></div></div>
			</div>
			
			<div class="row">
				
				<div class="span12 ph-cpanel-infobox"><h3 class="ph-cpanel-color"><?php echo JText::_('COM_PHOCACART_CHART'); ?> (<?php echo JText::_('COM_PHOCACART_THIS_WEEK'); ?>)</h3>
				<?php 
				
				$dataS = $s->getDataChart(); 
				$s->renderChartJsLine('phChartAreaLine', $dataS['amount'], JText::_('COM_PHOCACART_TOTAL_AMOUNT'), $dataS['orders'], JText::_('COM_PHOCACART_TOTAL_ORDERS'), $dataS['ticks']);
				$s->setFunction('phChartAreaLine', 'Line');
				$s->renderFunctions();
				
				/*	<div class="ph-chart-legend"><span class="ph-orders">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_ORDERS'); ?> &nbsp; <span class="ph-amount">&nbsp;</span> <?php echo JText::_('COM_PHOCACART_TOTAL_AMOUNT'); ?></div> */ ?>
	<div id="ph-canvas-holder2" class="phChartAreaLineholder" style="width: 100%;" >
        <canvas id="phChartAreaLine" class="ph-chart-area"s />
    </div>
				</div>
			</div>
			
			
			<div class="row">
			<div class="ph-cpanel-right-box span12">
				<div style="float:right;margin:10px;"><?php echo JHTML::_('image', $this->t['i'] . 'logo-phoca.png', 'Phoca.cz' );?></div><?php
echo '<h3>'.  JText::_($this->t['l'] . '_VERSION').'</h3>'
.'<p>'.  $this->t['version'] .'</p>';
echo '<h3>'.  JText::_($this->t['l'] . '_COPYRIGHT').'</h3>'
.'<p>© 2007 - '.  date("Y"). ' Jan Pavelka</p>'
.'<p><a href="http://www.phoca.cz/" target="_blank">www.phoca.cz</a></p>';
echo '<h3>'.  JText::_($this->t['l'] . '_LICENSE').'</h3>'
.'<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>';
echo '<h3>'.  JText::_($this->t['l'] . '_TRANSLATION').': '. JText::_($this->t['l'] . '_TRANSLATION_LANGUAGE_TAG').'</h3>'
.'<p>© 2007 - '.  date("Y"). ' '. JText::_($this->t['l'] . '_TRANSLATER'). '</p>'
.'<p>'.JText::_($this->t['l'] . '_TRANSLATION_SUPPORT_URL').'</p>';
		echo '<div style="border-top:1px solid #c2c2c2"></div><p>&nbsp;</p>'
.'<div class="btn-group"><a class="btn btn-large btn-primary" href="http://www.phoca.cz/version/index.php?'.$this->t['c'].'='.  $this->t['version'] .'" target="_blank"><i class="icon-loop icon-white"></i>&nbsp;&nbsp;'.  JText::_($this->t['l'] . '_CHECK_FOR_UPDATE') .'</a></div>';

	
			?></div></div>
		</div>
	</div>
	<input type="hidden" name="option" value="<?php echo $this->t['c'] ?>" />
	<input type="hidden" name="view" value="<?php echo $this->t['c'] ?>cp" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>