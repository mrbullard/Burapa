<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/Module/JoomImages/trunk/mod_joomimg.php $
// $Id: mod_joomimg.php 3458 2011-10-21 16:56:14Z aha $
/****************************************************************************************\
**   Module JoomImages for JoomGallery                                                  **
**   By: JoomGallery::ProjectTeam                                                       **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

// Deny direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');
if (!JComponentHelper::isEnabled('com_joomgallery', true))
{
  echo JText::_('JIJGNOTINSTALLED');
  return;
}

// Get the interface
require_once(JPATH_ROOT.DS.'components'.DS.'com_joomgallery'.DS.'interface.php');

// Include the helper class only once
require_once (dirname(__FILE__).DS.'helper.php');

// Get id of current module instance
$moduleid = $module->id;

// Create helper object
$joomimgObj = new modJoomImagesHelper();

if($joomimgObj->getGalleryVersion() < "1.6")
{
  echo JText::sprintf('JIJOOMGALLERY_NOT_UPTODATE', '1.6');
  return;
}
jimport('joomla.filesystem.file');

// Fill the interface object and get the images
$imgobjects = $joomimgObj->fillObject($params,$moduleid);

// Get slideshow or default view
if($joomimgObj->getConfig('slideshowthis') == 1)
{
  $path = JModuleHelper::getLayoutPath('mod_joomimg', 'slideshow');
}
else
{
  $path = JModuleHelper::getLayoutPath('mod_joomimg', 'default');
}
if (JFile::exists($path))
{
  require($path);
}
?>