<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/models/fields/imagepreview.php $
// $Id: imagepreview.php 3651 2012-02-19 14:36:46Z mab $
/****************************************************************************************\
**   JoomGallery  2                                                                     **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2012  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Renders a image preview form field
 *
 * @package JoomGallery
 * @since   2.0
 */
class JFormFieldImagepreview extends JFormField
{
  /**
   * The form field type.
   *
   * @access  protected
   * @var     string
   * @since   2.0
   */
  var $type = 'Imagepreview';

  /**
   * Returns the HTML for a thumbnail image form field.
   *
   * @access  protected
   * @return  object    The thumbnail image form field.
   * @since   2.0
   */
  function getInput()
  {
    return '<img class="jg_imgpreview" src="'.$this->value.'" id="'.$this->id.'" name="'.$this->name.'" border="2" alt="'.JText::_('COM_JOOMGALLERY_IMGMAN_IMAGE_PREVIEW').'">';
  }
}