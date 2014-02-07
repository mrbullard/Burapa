<?php
/**
 * @version		$Id: login.php 21710 2011-06-28 21:53:59Z chdemko $
 * @package		Joomla.Administrator
 * @subpackage	Templates.vtem_admin
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
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

JHtml::_('behavior.noframes');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />
<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<link href="templates/<?php echo $this->template ?>/css/login.css" rel="stylesheet" type="text/css" />
<?php  if($this->direction == 'rtl') : ?>
	<link href="templates/<?php echo $this->template ?>/css/login_rtl.css" rel="stylesheet" type="text/css" />
<?php  endif; ?>
<!--[if IE 7]>
<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<script type="text/javascript">
	window.addEvent('domready', function () {
		document.getElementById('form-login').username.select();
		document.getElementById('form-login').username.focus();
	});
</script>
</head>
<body class="vt_login_bg">
			<div class="login">
					<h1><?php echo JText::_('Administration Login') ?></h1>
					<jdoc:include type="message" />
					<jdoc:include type="component" />
					<p><a href="<?php echo JURI::root(); ?>"><?php echo JText::_('Return to site Home Page') ?></a></p>					
			</div>
			<noscript>
				<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
			</noscript>
</body>
</html>
