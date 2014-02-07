<?php
/**
 * @version		$Id: cpanel.php 21721 2011-07-01 08:48:47Z chdemko $
 * @package		Joomla.Administrator
 * @subpackage	Templates.vtem_admin
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

$doc->addStyleSheet('templates/system/css/system.css');
$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');

if ($this->direction == 'rtl') {
	$doc->addStyleSheet('templates/'.$this->template.'/css/template_rtl.css');
}

/** Load specific language related css */
$lang = JFactory::getLanguage();
$file = 'language/'.$lang->getTag().'/'.$lang->getTag().'.css';
if (JFile::exists($file)) {
	$doc->addStyleSheet($file);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
	<head>
		<jdoc:include type="head" />

		<!--[if IE 7]>
			<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
		<![endif]-->
        <script src="templates/<?php echo  $this->template ?>/js/menu.js" type="text/javascript"></script>
	</head>
<body id="minwidth-body" class="<?php echo $this->params->get('headerColor','green');?>">
	<div id="header-box">
	<div id="vt_logo">
 <a href="http://vtem.net" target="_blank" title="Go To Vtem "><img src="templates/<?php echo  $this->template ?>/images/logo_<?php echo $this->params->get('headerColor','green');?>.png" /></a>	 
 </div>
	 <div id="vt_main_header">
	 <div id="vt_main_sitename_status">
		<div id="vt_sitename">
			<span class="title">
			<a href="index.php"><?php echo $this->params->get('showSiteName') ? $app->getCfg('sitename') : JText::_('Administration'); ?></a>
			</span>
		</div>
		<div id="module-status">
			<jdoc:include type="modules" name="status"/>
			<?php
				//Display an harcoded logout
				$task = JRequest::getCmd('task');
				if ($task == 'edit' || $task == 'editA' || JRequest::getInt('hidemainmenu')) {
					$logoutLink = '';
				} else {
					$logoutLink = JRoute::_('index.php?option=com_login&task=logout&'. JUtility::getToken() .'=1');
				}
				$hideLinks	= JRequest::getBool('hidemainmenu');
				$output = array();
				// Print the Preview link to Main site.
				$output[] = '<span class="viewsite"><a href="'.JURI::root().'" target="_blank">'.JText::_('JGLOBAL_VIEW_SITE').'</a></span>';
				// Print the logout link.
				$output[] = '<span class="logout">' .($hideLinks ? '' : '<a href="'.$logoutLink.'">').JText::_('JLOGOUT').($hideLinks ? '' : '</a>').'</span>';
				// Reverse rendering order for rtl display.
				if ($this->direction == "rtl") :
					$output = array_reverse($output);
				endif;
				// Output the items.
				foreach ($output as $item) :
				echo $item;
				endforeach;
			?>
		</div>
		<div class="clr"></div>
		</div>
		<div id="module-menu">
			<jdoc:include type="modules" name="menu" />
			<span class="version"><?php echo  JText::_('JVERSION') ?> <?php echo  JVERSION; ?></span>
		</div>
	 </div>
	<div class="clr"></div> 	
	</div>
	<table class="vt_cpanel" cellspacing="10" width="100%">
		<tr>
			<td width="55%" valign="top" align="right">
			  <div id="vt_icon">
				<?php if ($this->countModules('icon')>1):?>
					<?php echo JHtml::_('sliders.start', 'position-icon', array('useCookie' => 1));?>
					<jdoc:include type="modules" name="icon" style="sliders" />
					<?php echo JHtml::_('sliders.end');?>
				<?php else:?>
					<jdoc:include type="modules" name="icon" />
				<?php endif;?>
				<div style="clear:both"></div>
			  </div>
			</td>
			<td width="45%" valign="top" align="left">
			  <div id="vt_main">
				<jdoc:include type="component" />
			  </div>
			</td>
		</tr>
	</table>
		<noscript>
			<?php echo  JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
		</noscript>
    <div class="vt_space"></div>
	<jdoc:include type="modules" name="footer" style="none"  />
	<div id="footer">
		<p class="copyright">
			<?php $joomla= '<a href="http://www.joomla.org">Joomla!&#174;</a>';
				echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla) ?>
		</p>
         <p class="copyright">       
             <a href="http://vtem.net" target="_blank" title="Go To Vtem "><img src="templates/<?php echo  $this->template ?>/images/system/vtem-logo.png" /></a>
		</p>
	</div>
</body>
</html>
