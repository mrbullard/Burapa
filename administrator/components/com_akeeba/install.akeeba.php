<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2012 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 3.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Obsolete files and folders to remove from the Core release only
$akeebaRemoveFilesCore = array(
	'files'	=> array(
		'administrator/components/com_akeeba/restore.php',
		'plugins/system/akeebaupdatecheck.php',
		'plugins/system/akeebaupdatecheck.xml',
		'plugins/system/oneclickaction.php',
		'plugins/system/oneclickaction.xml',
		'plugins/system/srp.php',
		'plugins/system/srp.xml'
	),
	'folders' => array(
		'administrator/components/com_akeeba/akeeba/engines/finalization',
		'plugins/system/akeebaupdatecheck',
		'plugins/system/oneclickaction',
		'plugins/system/srp'
	)
);

// Obsolete files and folders to remove from the Core and Pro releases
$akeebaRemoveFilesPro = array(
	'files'	=> array(
		'administrator/components/com_akeeba/akeeba/core/03.filters.ini',
		'administrator/components/com_akeeba/akeeba/engines/archiver/directftp.ini',
		'administrator/components/com_akeeba/akeeba/engines/archiver/directftp.php',
		'administrator/components/com_akeeba/akeeba/engines/archiver/directsftp.ini',
		'administrator/components/com_akeeba/akeeba/engines/archiver/directsftp.php',
		'administrator/components/com_akeeba/akeeba/engines/archiver/zipnative.ini',
		'administrator/components/com_akeeba/akeeba/engines/archiver/zipnative.php',
		'administrator/components/com_akeeba/akeeba/engines/proc/email.ini',
		'administrator/components/com_akeeba/akeeba/engines/proc/email.php',
		'administrator/components/com_akeeba/views/buadmin/restorepoint.php',
		'administrator/components/com_akeeba/controllers/installer.php',
		'administrator/components/com_akeeba/controllers/srprestore.php',
		'administrator/components/com_akeeba/controllers/stw.php',
		'administrator/components/com_akeeba/controllers/upload.php',
		'administrator/components/com_akeeba/models/installer.php',
		'administrator/components/com_akeeba/models/srprestore.php',
		'administrator/components/com_akeeba/models/stw.php'
	),
	'folders' => array(
		'administrator/components/com_akeeba/views/installer',
		'administrator/components/com_akeeba/views/profiles',
		'administrator/components/com_akeeba/views/srprestore',
		'administrator/components/com_akeeba/views/stw',
		'administrator/components/com_akeeba/views/upload',
	)
);

if(!function_exists('rrmdir')) {
	function rrmdir($dir) { 
		$result = true;

		if (@is_dir($dir)) { 
			$objects = @scandir($dir); 
			if($objects !== false) foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") {
						$result &= rrmdir($dir."/".$object);		 
					} else {
						$result &= @unlink($dir."/".$object); 
					}
				} 
			}
			reset($objects); 
			$result = @rmdir($dir); 
		} else {
			$result = false;
		}

		return $result;
	}
}

// Joomla! 1.6 Beta 13+ hack
if( version_compare( JVERSION, '1.6.0', 'ge' ) && !defined('_AKEEBA_HACK') ) {
	return;
} else {
	global $akeeba_installation_has_run;
	if($akeeba_installation_has_run) return;
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

// Schema modification -- BEGIN

$db =& JFactory::getDBO();
$errors = array();

// Version 3.0 to 3.1 updates (performs autodection before running the commands)
$sql = 'SHOW CREATE TABLE `#__ak_stats`';
$db->setQuery($sql);
$ctableAssoc = $db->loadResultArray(1);
$ctable = empty($ctableAssoc) ? '' : $ctableAssoc[0];
if(!strstr($ctable, '`total_size`'))
{
	// Smart schema update - Updated for changes in 3.2.a1

	if($db->hasUTF())
	{
		$charset = 'CHARSET=utf8';
	}
	else
	{
		$charset = '';
	}

	$sql = <<<ENDSQL
DROP TABLE IF EXISTS `#__ak_stats_bak`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
CREATE TABLE `#__ak_stats_bak` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `comment` longtext,
  `backupstart` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `backupend` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('run','fail','complete') NOT NULL DEFAULT 'run',
  `origin` varchar(30) NOT NULL DEFAULT 'backend',
  `type` varchar(30) NOT NULL DEFAULT 'full',
  `profile_id` bigint(20) NOT NULL DEFAULT '1',
  `archivename` longtext,
  `absolute_path` longtext,
  `multipart` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(255) DEFAULT NULL,
  `filesexist` tinyint(1) NOT NULL DEFAULT '0',
  `remote_filename` varchar(1000) DEFAULT NULL,
  `total_size` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_fullstatus` (`filesexist`,`status`),
  KEY `idx_stale` (`status`,`origin`)
) ENGINE=MyISAM DEFAULT $charset;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	if(strstr($ctable, '`tag`')) {
		// Upgrade from 3.1.3 or later (has tag and filesexist columns)
		$sql = <<<ENDSQL
INSERT IGNORE INTO `#__ak_stats_bak`
	(`id`,`description`,`comment`,`backupstart`,`backupend`,`status`,`origin`,`type`,`profile_id`,`archivename`,`absolute_path`,`multipart`,`tag`,`filesexist`)
SELECT
  `id`,`description`,`comment`,`backupstart`,`backupend`,`status`,`origin`,`type`,`profile_id`,`archivename`,`absolute_path`,`multipart`,`tag`,`filesexist`
FROM
  `#__ak_stats`;
ENDSQL;
	} else {
		// Upgrade from 3.1.2 or earlier
		$sql = <<<ENDSQL
INSERT IGNORE INTO `#__ak_stats_bak`
	(`id`,`description`,`comment`,`backupstart`,`backupend`,`status`,`origin`,`type`,`profile_id`,`archivename`,`absolute_path`,`multipart`)
SELECT
  `id`,`description`,`comment`,`backupstart`,`backupend`,`status`,`origin`,`type`,`profile_id`,`archivename`,`absolute_path`,`multipart`
FROM
  `#__ak_stats`;
ENDSQL;
	}
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
DROP TABLE IF EXISTS `#__ak_stats`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
CREATE TABLE `#__ak_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `comment` longtext,
  `backupstart` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `backupend` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('run','fail','complete') NOT NULL DEFAULT 'run',
  `origin` varchar(30) NOT NULL DEFAULT 'backend',
  `type` varchar(30) NOT NULL DEFAULT 'full',
  `profile_id` bigint(20) NOT NULL DEFAULT '1',
  `archivename` longtext,
  `absolute_path` longtext,
  `multipart` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(255) DEFAULT NULL,
  `filesexist` tinyint(1) NOT NULL DEFAULT '0',
  `remote_filename` varchar(1000) DEFAULT NULL,
  `total_size` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_fullstatus` (`filesexist`,`status`),
  KEY `idx_stale` (`status`,`origin`)
) ENGINE=MyISAM DEFAULT $charset;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
INSERT IGNORE INTO `#__ak_stats` SELECT * FROM `#__ak_stats_bak`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
DROP TABLE IF EXISTS `#__ak_stats_bak`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

}

// Schema modification -- END

// Install modules and plugins -- BEGIN

// -- General settings
jimport('joomla.installer.installer');
$db = & JFactory::getDBO();
$status = new JObject();
$status->modules = array();
$status->plugins = array();
if( version_compare( JVERSION, '1.6.0', 'ge' ) ) {
	// Thank you for removing installer features in Joomla! 1.6 Beta 13 and
	// forcing me to write ugly code, Joomla!...
	$src = dirname(__FILE__);
} else {
	$src = $this->parent->getPath('source');
}

// Remove features from the Core edition
$isAkeebaPro = is_dir($src.'/plg_srp');

if($isAkeebaPro) {
	$akeebaRemoveFiles = $akeebaRemoveFilesPro;
} else {
	$akeebaRemoveFiles['files'] = array_merge($akeebaRemoveFilesPro['files'], $akeebaRemoveFilesCore['files']);
	$akeebaRemoveFiles['folders'] = array_merge($akeebaRemoveFilesPro['folders'], $akeebaRemoveFilesCore['folders']);
}

// Remove files
jimport('joomla.filesystem.file');
if(!empty($akeebaRemoveFiles['files'])) foreach($akeebaRemoveFiles['files'] as $file) {
	$f = JPATH_BASE.'/'.$file;
	if(!file_exists($f)) continue;
	if(!@unlink($f)) {
		JFile::delete($f);
	}
}

// Remove folders
jimport('joomla.filesystem.file');
if(!empty($akeebaRemoveFiles['folders'])) foreach($akeebaRemoveFiles['folders'] as $folder) {
	$f = JPATH_BASE.'/'.$folder;
	if(!is_dir($f)) continue;
	if(!rrmdir($f)) {
		JFolder::delete($f);
	}
}

if(!$isAkeebaPro) {	
	// Remove plugins
	# ----- System - System Restore Points
	if(version_compare(JVERSION,'1.6.0','ge')) {
		$db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "srp" AND `folder` = "system"');
	} else {
		$db->setQuery('SELECT `id` FROM #__plugins WHERE `element` = "srp" AND `folder` = "system"');
	}
	$id = $db->loadResult();
	if($id)
	{
		$installer = new JInstaller;
		$result = $installer->uninstall('plugin',$id,1);
	}

	# ----- System - One Click Action
	if(version_compare(JVERSION,'1.6.0','ge')) {
		$db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "oneclickaction" AND `folder` = "system"');
	} else {
		$db->setQuery('SELECT `id` FROM #__plugins WHERE `element` = "oneclickaction" AND `folder` = "system"');
	}
	$id = $db->loadResult();
	if($id)
	{
		$installer = new JInstaller;
		$result = $installer->uninstall('plugin',$id,1);
	}

	# ----- System - Akeeba Update Check
	if(version_compare(JVERSION,'1.6.0','ge')) {
		$db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "akeebaupdatecheck" AND `folder` = "system"');
	} else {
		$db->setQuery('SELECT `id` FROM #__plugins WHERE `element` = "akeebaupdatecheck" AND `folder` = "system"');
	}
	$id = $db->loadResult();
	if($id)
	{
		$installer = new JInstaller;
		$result = $installer->uninstall('plugin',$id,1);
	}	
}

// -- Icon module
$installer = new JInstaller;
$result = $installer->install($src.'/mod_akadmin');
$status->modules[] = array('name'=>'mod_akadmin','client'=>'administrator', 'result'=>$result);

$query = "UPDATE #__modules SET position='icon', ordering=97, published=1 WHERE `module`='mod_akadmin'";
$db->setQuery($query);
$db->query();

$query = "SELECT `id` FROM `#__modules` WHERE `module` = 'mod_akadmin'";
$db->setQuery($query);
$modID = $db->loadResult();

$query = "REPLACE INTO `#__modules_menu` (`moduleid`,`menuid`) VALUES ($modID, 0)";
$db->setQuery($query);
$db->query();

// Plugins are only installed in the Professional release
if($isAkeebaPro)
{

	// -- System Restore Point support
	$installer = new JInstaller;
	$result = $installer->install($src.'/plg_srp');
	$status->plugins[] = array('name'=>'plg_srp','group'=>'system', 'result'=>$result);

	// -- One click action
	$installer = new JInstaller;
	$result = $installer->install($src.'/plg_oneclickaction');
	$status->plugins[] = array('name'=>'plg_oneclickaction','group'=>'system', 'result'=>$result);

	if($result) {
		if( version_compare( JVERSION, '1.6.0', 'ge' ) ) {
			$query = "UPDATE #__extensions SET ordering=-31000 WHERE element='oneclickaction' AND folder='system'";
			$db->setQuery($query);
			$db->query();
		} else {
			$query = "UPDATE #__plugins SET ordering=-31000 WHERE element='oneclickaction' AND folder='system'";
			$db->setQuery($query);
			$db->query();
		}
	}

	// -- Akeeba Backup update check
	$installer = new JInstaller;
	$result = $installer->install($src.'/plg_akeebaupdatecheck');
	$status->plugins[] = array('name'=>'plg_akeebaupdatecheck','group'=>'system', 'result'=>$result);
}

// Install modules and plugins -- END

// Load the translation strings (Joomla! 1.5 and 1.6 compatible)
if( version_compare( JVERSION, '1.6.0', 'lt' ) ) {
	global $j15;
	// Joomla! 1.5 will have to load the translation strings
	$j15 = true;
	$jlang =& JFactory::getLanguage();
	$path = JPATH_ADMINISTRATOR.'/components/com_akeeba';
	$jlang->load('com_akeeba.sys', $path, 'en-GB', true);
	$jlang->load('com_akeeba.sys', $path, $jlang->getDefault(), true);
	$jlang->load('com_akeeba.sys', $path, null, true);
} else {
	$j15 = false;
}

if(!function_exists('pitext'))
{
	function pitext($key)
	{
		global $j15;
		$string = JText::_($key);
		if($j15)
		{
			$string = str_replace('"_QQ_"', '"', $string);
		}
		echo $string;
	}
}

if(!function_exists('pisprint'))
{
	function pisprint($key, $param, $param2 = null)
	{
		global $j15;
		$string = is_null($param2) ? JText::sprintf($key, $param) : JText::sprintf($key, $param, $param2);
		if($j15)
		{
			$string = str_replace('"_QQ_"', '"', $string);
		}
		echo $string;
	}
}

// Finally, show the installation results form
?>
<?php if(!empty($errors)): ?>
<div style="background-color: #900; color: #fff; font-size: large;">
	<h1><?php pitext('COM_AKEEBA_PIMYSQLERR_HEAD'); ?></h1>
	<p><?php pitext('COM_AKEEBA_PIMYSQLERR_BODY1'); ?></p>
	<p><?php pitext('COM_AKEEBA_PIMYSQLERR_BODY2'); ?></p>
	<p style="font-size: normal;">
<?php echo implode("<br/>", $errors); ?>
	</p>
</div>
<?php endif; ?>

<h1><?php pitext('COM_AKEEBA_PIHEADER'); ?></h1>

<?php $rows = 0;?>
<img src="components/com_akeeba/assets/images/logo-48.png" width="48" height="48" alt="Akeeba Backup" align="right" />

<h2><?php pitext('COM_AKEEBA_PIWELCOME') ?></h2>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
			<th width="30%"><?php echo JText::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'Akeeba Backup '.JText::_('Component'); ?></td>
			<td><strong><?php echo JText::_('Installed'); ?></strong></td>
		</tr>
		<?php if (count($status->modules)) : ?>
		<tr>
			<th><?php echo JText::_('Module'); ?></th>
			<th><?php echo JText::_('Client'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong><?php echo ($module['result'])?JText::_('Installed'):JText::_('Not installed'); ?></strong></td>
		</tr>
		<?php endforeach;?>
		<?php endif;?>
		<?php if (count($status->plugins)) : ?>
		<tr>
			<th><?php echo JText::_('Plugin'); ?></th>
			<th><?php echo JText::_('Group'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong><?php echo ($plugin['result'])?JText::_('Installed'):JText::_('Not installed'); ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

<fieldset>
	<p>
		<?php pisprint('COM_AKEEBA_PITEXT1B','https://www.akeebabackup.com/documentation/quick-start-guide.html','https://www.akeebabackup.com/documentation/akeeba-backup-documentation.html') ?>
		<?php pisprint('COM_AKEEBA_PITEXT2B','https://www.akeebabackup.com/documentation/video-tutorials.html') ?>
	</p>
	<p>
		<?php pisprint('COM_AKEEBA_PITEXT3B','index.php?option=com_akeeba') ?>
	</p>
	<p>
		<?php pisprint('COM_AKEEBA_PITEXT4B','https://www.akeebabackup.com/documentation/troubleshooter.html','https://www.akeebabackup.com/support/forum.html') ?>
		<?php pitext('COM_AKEEBA_PITEXT6') ?>
	</p>
</fieldset>
<?php
global $akeeba_installation_has_run;
$akeeba_installation_has_run = 1;
if(!defined('AKEEBA_PRO')) {
	require_once JPATH_ADMINISTRATOR.'/components/com_akeeba/version.php';
}
if(AKEEBA_PRO != 1)
{
	jimport('joomla.filesystem.folder');
	jimport('joomla.filesystem.file');
	if(JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_akeeba/plugins')) {
		JFolder::delete(JPATH_ADMINISTRATOR.'/components/com_akeeba/plugins');
		JFolder::create(JPATH_ADMINISTRATOR.'/components/com_akeeba/plugins');
	}
	if(JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_akeeba/akeeba/plugins')) {
		JFolder::delete(JPATH_ADMINISTRATOR.'/components/com_akeeba/akeeba/plugins');
		JFolder::create(JPATH_ADMINISTRATOR.'/components/com_akeeba/akeeba/plugins');	
	}
}
?>